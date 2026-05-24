<?php

namespace Tests\Feature\Payments;

use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class PayHerePaymentTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_starting_payhere_payment_creates_payment_row(): void
    {
        $order = $this->createPendingOrder();

        $payment = $this->createPayHerePayment($order);

        $this->assertSame((int) $order->id, (int) $payment->order_id);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'order_id' => $order->id,
            'status' => 'initiated',
        ]);
    }

    public function test_checkout_payload_includes_required_payhere_fields(): void
    {
        $order = $this->createPendingOrder();
        $payment = $this->createPayHerePayment($order);

        session(['guest_order_ids' => [(int) $order->id]]);

        $this->get(route('customer.payments.payhere.start', $order))
            ->assertOk()
            ->assertViewHas('payload', function (array $payload) use ($payment): bool {
                foreach (['merchant_id', 'return_url', 'cancel_url', 'notify_url', 'order_id', 'items', 'currency', 'amount', 'first_name', 'email', 'phone', 'address', 'city', 'country', 'hash'] as $key) {
                    if (! array_key_exists($key, $payload) || (string) $payload[$key] === '') {
                        return false;
                    }
                }

                return (string) $payload['order_id'] === (string) $payment->payment_reference;
            });
    }

    public function test_return_url_does_not_mark_payment_paid(): void
    {
        $order = $this->createPendingOrder();
        $payment = $this->createPayHerePayment($order);

        session(['guest_order_ids' => [(int) $order->id]]);

        $this->get(route('customer.payments.payhere.return', $payment))
            ->assertOk();

        $payment->refresh();
        $order->refresh();

        $this->assertNotSame('paid', $payment->status);
        $this->assertNotSame('paid', $order->payment_status);
    }

    public function test_invalid_notify_signature_does_not_mark_order_paid(): void
    {
        $order = $this->createPendingOrder();
        $payment = $this->createPayHerePayment($order);

        $payload = [
            'merchant_id' => '121XXXX',
            'order_id' => $payment->payment_reference,
            'payhere_amount' => number_format((float) $payment->amount, 2, '.', ''),
            'payhere_currency' => (string) $payment->currency_code,
            'status_code' => '2',
            'payment_id' => 'PH-TEST-1',
            'md5sig' => 'INVALID',
        ];

        $this->post(route('payments.payhere.notify'), $payload)->assertOk();

        $payment->refresh();
        $order->refresh();

        $this->assertNotSame('paid', $payment->status);
        $this->assertNotSame('paid', $order->payment_status);
    }

    public function test_valid_success_notify_marks_payment_and_order_paid_and_confirms_inventory(): void
    {
        $order = $this->createPendingOrder();
        $payment = $this->createPayHerePayment($order);

        $item = $order->items()->firstOrFail();
        $stock = InventoryStock::query()
            ->where('product_id', $item->product_id)
            ->whereNull('variant_id')
            ->firstOrFail();

        $reservedBefore = (string) $stock->reserved_quantity;
        $qtyBefore = (string) $stock->quantity;

        $payload = [
            'merchant_id' => '121XXXX',
            'order_id' => $payment->payment_reference,
            'payhere_amount' => number_format((float) $payment->amount, 2, '.', ''),
            'payhere_currency' => (string) $payment->currency_code,
            'status_code' => '2',
            'payment_id' => 'PH-TEST-OK',
        ];
        $payload['md5sig'] = $this->payHereNotifyMd5Sig($payload);

        $this->post(route('payments.payhere.notify'), $payload)->assertOk();

        $payment->refresh();
        $order->refresh();
        $stock->refresh();

        $this->assertSame('paid', $payment->status);
        $this->assertSame('paid', $order->payment_status);

        $this->assertTrue(InventoryMovement::query()
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('type', 'sale')
            ->exists());

        $this->assertNotSame('0.0000', $reservedBefore);
        $this->assertSame('0.0000', (string) $stock->reserved_quantity);
        $this->assertNotSame($qtyBefore, (string) $stock->quantity);
    }

    public function test_duplicate_notify_does_not_double_reduce_stock(): void
    {
        $order = $this->createPendingOrder();
        $payment = $this->createPayHerePayment($order);

        $payload = [
            'merchant_id' => '121XXXX',
            'order_id' => $payment->payment_reference,
            'payhere_amount' => number_format((float) $payment->amount, 2, '.', ''),
            'payhere_currency' => (string) $payment->currency_code,
            'status_code' => '2',
            'payment_id' => 'PH-TEST-DUP',
        ];
        $payload['md5sig'] = $this->payHereNotifyMd5Sig($payload);

        $this->post(route('payments.payhere.notify'), $payload)->assertOk();
        $this->post(route('payments.payhere.notify'), $payload)->assertOk();

        $saleCount = (int) InventoryMovement::query()
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('type', 'sale')
            ->count();

        $this->assertSame(1, $saleCount);
    }

    public function test_failed_notify_releases_reservation(): void
    {
        $order = $this->createPendingOrder();
        $payment = $this->createPayHerePayment($order);

        $item = $order->items()->firstOrFail();
        $stock = InventoryStock::query()
            ->where('product_id', $item->product_id)
            ->whereNull('variant_id')
            ->firstOrFail();

        $this->assertNotSame('0.0000', (string) $stock->reserved_quantity);

        $payload = [
            'merchant_id' => '121XXXX',
            'order_id' => $payment->payment_reference,
            'payhere_amount' => number_format((float) $payment->amount, 2, '.', ''),
            'payhere_currency' => (string) $payment->currency_code,
            'status_code' => '-1',
            'payment_id' => 'PH-TEST-FAIL',
        ];
        $payload['md5sig'] = $this->payHereNotifyMd5Sig($payload);

        $this->post(route('payments.payhere.notify'), $payload)->assertOk();

        $stock->refresh();
        $this->assertSame('0.0000', (string) $stock->reserved_quantity);

        $this->assertTrue(InventoryMovement::query()
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('type', 'release')
            ->exists());
    }
}
