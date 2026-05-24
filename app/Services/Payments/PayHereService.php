<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Services\SettingService;
use App\Services\StoreService;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayHereService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected SettingService $settings,
        protected StoreService $stores,
        protected NotificationService $notifications,
        protected WebhookService $webhooks,
    ) {}

    public function isEnabled(): bool
    {
        return $this->payHereEnabled();
    }

    public function getCheckoutUrl(): string
    {
        $mode = (string) $this->payHereMode();

        return $mode === 'live'
            ? (string) config('open_ecommerce_laravel.payments.payhere.live_url')
            : (string) config('open_ecommerce_laravel.payments.payhere.sandbox_url');
    }

    public function createPaymentForOrder(Order $order): Payment
    {
        if ((float) $order->grand_total <= 0) {
            throw new RuntimeException('Order total must be greater than 0.');
        }
        if ($order->payment_status === 'paid') {
            throw new RuntimeException('Order already paid.');
        }

        /** @var PaymentMethod $method */
        $method = PaymentMethod::query()
            ->where('code', 'payhere')
            ->where('status', 'active')
            ->firstOrFail();

        return DB::transaction(function () use ($order, $method): Payment {
            $payment = Payment::query()
                ->where('order_id', $order->getKey())
                ->whereIn('status', ['initiated', 'pending', 'authorized'])
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $ref = 'PAY-'.$order->order_number.'-'.now()->format('YmdHis');

            if ($payment === null) {
                $payment = Payment::query()->create([
                    'order_id' => $order->getKey(),
                    'payment_method_id' => $method->getKey(),
                    'payment_reference' => $ref,
                    'provider_transaction_id' => null,
                    'status' => 'initiated',
                    'amount' => $order->grand_total,
                    'currency_code' => $order->currency_code ?? 'LKR',
                    'gateway_response' => null,
                    'paid_at' => null,
                ]);
            } else {
                $payment->payment_method_id = $method->getKey();
                $payment->payment_reference = $payment->payment_reference ?: $ref;
                $payment->status = 'initiated';
                $payment->amount = $order->grand_total;
                $payment->currency_code = $order->currency_code ?? 'LKR';
                $payment->save();
            }

            $payload = $this->buildCheckoutPayload($order->fresh(['billingAddress']), $payment);

            PaymentTransaction::query()->create([
                'payment_id' => $payment->getKey(),
                'order_id' => $order->getKey(),
                'provider' => 'payhere',
                'transaction_type' => 'initiate',
                'provider_reference' => $payment->payment_reference,
                'status' => 'created',
                'request_payload' => collect($payload)->except(['hash'])->all(),
                'response_payload' => null,
                'signature_valid' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $payment->fresh();
        });
    }

    /**
     * @return array<string, string>
     */
    public function buildCheckoutPayload(Order $order, Payment $payment): array
    {
        if (! $this->payHereEnabled()) {
            throw new RuntimeException('PayHere is disabled.');
        }

        $merchantId = $this->payHereMerchantId();
        $merchantSecret = $this->payHereMerchantSecret();
        if ($merchantId === '' || $merchantSecret === '') {
            throw new RuntimeException('PayHere merchant configuration missing.');
        }

        $orderId = (string) ($payment->payment_reference ?: $order->order_number);
        $amount = number_format((float) $payment->amount, 2, '.', '');
        $currency = (string) ($payment->currency_code ?: 'LKR');

        $billing = $order->billingAddress;
        $firstName = $billing?->first_name ?: 'Customer';
        $lastName = $billing?->last_name ?: '';
        $email = $order->customer_email ?: ($billing?->email ?: '');
        $phone = $order->customer_phone ?: ($billing?->phone ?: '');
        $address = $billing?->address_line_1 ?: '';
        $city = $billing?->city ?: '';
        $country = $billing?->country_code ?: 'LK';

        $hash = strtoupper(md5(
            $merchantId.
            $orderId.
            $amount.
            $currency.
            strtoupper(md5($merchantSecret))
        ));

        $storeName = $this->storefrontName();

        return [
            'merchant_id' => $merchantId,
            'return_url' => route('customer.payments.payhere.return', ['payment' => $payment->getKey()]),
            'cancel_url' => route('customer.payments.payhere.cancel', ['payment' => $payment->getKey()]),
            'notify_url' => route('payments.payhere.notify'),
            'order_id' => $orderId,
            'items' => $storeName.' — '.$order->order_number,
            'currency' => $currency,
            'amount' => $amount,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'country' => $country,
            'hash' => $hash,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyNotifySignature(array $payload): bool
    {
        $merchantId = $this->payHereMerchantId();
        $merchantSecret = $this->payHereMerchantSecret();

        $md5sig = (string) ($payload['md5sig'] ?? '');
        $orderId = (string) ($payload['order_id'] ?? '');
        $amount = (string) ($payload['payhere_amount'] ?? '');
        $currency = (string) ($payload['payhere_currency'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $payloadMerchantId = (string) ($payload['merchant_id'] ?? '');

        if ($md5sig === '' || $orderId === '' || $amount === '' || $currency === '' || $statusCode === '') {
            return false;
        }
        if ($payloadMerchantId === '' || $payloadMerchantId !== $merchantId) {
            return false;
        }

        $computed = strtoupper(md5(
            $merchantId.
            $orderId.
            $amount.
            $currency.
            $statusCode.
            strtoupper(md5($merchantSecret))
        ));

        return hash_equals($computed, strtoupper($md5sig));
    }

    public function handleNotify(Request $request): void
    {
        $payload = $request->all();

        $tx = PaymentTransaction::query()->create([
            'payment_id' => null,
            'order_id' => null,
            'provider' => 'payhere',
            'transaction_type' => 'notify',
            'provider_reference' => (string) ($payload['order_id'] ?? null),
            'status' => null,
            'request_payload' => $payload,
            'response_payload' => null,
            'signature_valid' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $payment = null;
        if (! empty($payload['order_id'])) {
            $payment = Payment::query()
                ->where('payment_reference', (string) $payload['order_id'])
                ->orderByDesc('id')
                ->first();
        }

        if ($payment === null) {
            $tx->status = 'payment_not_found';
            $tx->signature_valid = false;
            $tx->save();

            return;
        }

        $order = $payment->order()->first();
        if ($order === null) {
            $tx->payment_id = $payment->getKey();
            $tx->status = 'order_not_found';
            $tx->signature_valid = false;
            $tx->save();

            return;
        }

        $signatureValid = $this->verifyNotifySignature($payload);
        $tx->payment_id = $payment->getKey();
        $tx->order_id = $order->getKey();
        $tx->signature_valid = $signatureValid;

        if (! $signatureValid) {
            $tx->status = 'invalid_signature';
            $tx->save();

            return;
        }

        $statusCode = (int) ($payload['status_code'] ?? 0);

        DB::transaction(function () use ($payment, $order, $payload, $statusCode, $tx): void {
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::query()->lockForUpdate()->whereKey($payment->getKey())->firstOrFail();
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()->lockForUpdate()->whereKey($order->getKey())->firstOrFail();

            $lockedPayment->provider_transaction_id = (string) ($payload['payment_id'] ?? $lockedPayment->provider_transaction_id);
            $lockedPayment->gateway_response = $payload;

            if ($lockedPayment->status === 'paid' && $lockedOrder->payment_status === 'paid') {
                $tx->status = 'duplicate_paid';
                $tx->save();

                return;
            }

            if ($statusCode === 2) {
                $lockedPayment->status = 'paid';
                $lockedPayment->paid_at = now();
                $lockedPayment->save();

                $lockedOrder->payment_status = 'paid';
                $lockedOrder->status = in_array($lockedOrder->status, ['pending', 'failed'], true) ? 'processing' : $lockedOrder->status;
                $lockedOrder->paid_total = $lockedPayment->amount;
                $lockedOrder->save();

                OrderStatusHistory::query()->create([
                    'order_id' => $lockedOrder->getKey(),
                    'from_status' => null,
                    'to_status' => $lockedOrder->status,
                    'note' => 'Payment confirmed by PayHere notify.',
                    'changed_by_admin_id' => null,
                    'changed_by_customer_id' => $lockedOrder->customer_id,
                    'created_at' => now(),
                ]);

                $this->inventoryService->confirmSaleForOrder($lockedOrder->fresh(['items']));
                $tx->status = 'success';
                $tx->save();

                $orderId = $lockedOrder->getKey();
                $paymentId = $lockedPayment->getKey();
                DB::afterCommit(function () use ($orderId, $paymentId): void {
                    try {
                        $o = Order::query()->find($orderId);
                        $p = Payment::query()->find($paymentId);
                        if ($o !== null && $p !== null) {
                            $this->notifications->notifyPaymentSuccess($o, $p);
                        }
                    } catch (\Throwable $e) {
                        Log::error('notifyPaymentSuccess hook failed', [
                            'order_id' => $orderId,
                            'payment_id' => $paymentId,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                    try {
                        $this->webhooks->dispatchSafe('payment.paid', [
                            'order_id' => $orderId,
                            'payment_id' => $paymentId,
                        ]);
                        $this->webhooks->dispatchSafe('order.paid', [
                            'order_id' => $orderId,
                            'payment_id' => $paymentId,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('payhere webhooks success dispatch failed', [
                            'order_id' => $orderId,
                            'payment_id' => $paymentId,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                });

                return;
            }

            if ($statusCode === 0) {
                $lockedPayment->status = 'pending';
                $lockedPayment->save();

                $lockedOrder->payment_status = 'pending';
                $lockedOrder->save();

                $tx->status = 'pending';
                $tx->save();

                return;
            }

            if (in_array($statusCode, [-1, -2], true)) {
                $lockedPayment->status = $statusCode === -1 ? 'cancelled' : 'failed';
                $lockedPayment->save();

                $lockedOrder->payment_status = 'failed';
                $lockedOrder->status = $lockedOrder->status === 'pending' ? 'pending' : $lockedOrder->status;
                $lockedOrder->save();

                $this->inventoryService->releaseReservationForOrder($lockedOrder->fresh(['items']));
                $tx->status = $statusCode === -1 ? 'cancelled' : 'failed';
                $tx->save();

                $failOrderId = $lockedOrder->getKey();
                $failPaymentId = $lockedPayment->getKey();
                DB::afterCommit(function () use ($failOrderId, $failPaymentId): void {
                    try {
                        $this->webhooks->dispatchSafe('payment.failed', [
                            'order_id' => $failOrderId,
                            'payment_id' => $failPaymentId,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('payhere webhooks failure dispatch failed', [
                            'order_id' => $failOrderId,
                            'payment_id' => $failPaymentId,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                });

                return;
            }

            if ($statusCode === -3) {
                $lockedPayment->status = 'failed';
                $lockedPayment->save();
                $lockedOrder->payment_status = 'failed';
                $lockedOrder->save();

                $tx->status = 'charged_back';
                $tx->save();

                $failOrderId = $lockedOrder->getKey();
                $failPaymentId = $lockedPayment->getKey();
                DB::afterCommit(function () use ($failOrderId, $failPaymentId): void {
                    try {
                        $this->webhooks->dispatchSafe('payment.failed', [
                            'order_id' => $failOrderId,
                            'payment_id' => $failPaymentId,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('payhere webhooks failure dispatch failed', [
                            'order_id' => $failOrderId,
                            'payment_id' => $failPaymentId,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                });

                return;
            }

            $tx->status = 'unknown_status';
            $tx->save();
        });
    }

    private function payHereEnabled(): bool
    {
        $v = $this->settings->get('payhere.enabled');
        if ($v !== null) {
            return (bool) $v;
        }

        return (bool) config('open_ecommerce_laravel.payments.payhere.enabled', false);
    }

    private function payHereMode(): string
    {
        $v = $this->settings->get('payhere.mode');
        if (is_string($v) && $v !== '') {
            return $v;
        }

        return (string) config('open_ecommerce_laravel.payments.payhere.mode', 'sandbox');
    }

    private function payHereMerchantId(): string
    {
        $v = $this->settings->get('payhere.merchant_id');
        if (is_string($v) && $v !== '') {
            return $v;
        }

        return (string) config('open_ecommerce_laravel.payments.payhere.merchant_id', '');
    }

    private function payHereMerchantSecret(): string
    {
        $v = $this->settings->get('payhere.merchant_secret');
        if (is_string($v) && $v !== '') {
            return $v;
        }

        return (string) config('open_ecommerce_laravel.payments.payhere.merchant_secret', '');
    }

    private function storefrontName(): string
    {
        try {
            $name = $this->stores->currentStore()->name;
            if (is_string($name) && $name !== '') {
                return $name;
            }
        } catch (\Throwable) {
            // fall through
        }

        $fallback = $this->settings->get('store.name');
        if (is_string($fallback) && $fallback !== '') {
            return $fallback;
        }

        return (string) config('open_ecommerce_laravel.store.name', config('app.name'));
    }
}

