<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerAccountDemoSeeder extends Seeder
{
    public function run(): void
    {
        /** @var InventoryService $inventory */
        $inventory = app(InventoryService::class);
        /** @var CartService $cart */
        $cart = app(CartService::class);

        $customer = Customer::query()->updateOrCreate(
            ['email' => 'customer@open-ecommerce-laravel.test'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Customer',
                'phone' => '0770000000',
                'password' => bcrypt('password'),
                'accepts_marketing' => true,
                'status' => 'active',
            ]
        );

        $shipping = CustomerAddress::query()->updateOrCreate(
            ['customer_id' => $customer->getKey(), 'type' => 'shipping', 'address_line_1' => 'No. 25, Galle Road'],
            [
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'city' => 'Colombo',
                'district' => 'Colombo',
                'province' => 'Western',
                'postal_code' => '00300',
                'country_code' => 'LK',
                'is_default' => true,
            ]
        );

        $billing = CustomerAddress::query()->updateOrCreate(
            ['customer_id' => $customer->getKey(), 'type' => 'billing', 'address_line_1' => 'No. 25, Galle Road'],
            [
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'city' => 'Colombo',
                'district' => 'Colombo',
                'province' => 'Western',
                'postal_code' => '00300',
                'country_code' => 'LK',
                'is_default' => true,
            ]
        );

        $payhere = PaymentMethod::query()->firstOrCreate(
            ['code' => 'payhere'],
            ['name' => 'PayHere', 'provider' => 'payhere', 'status' => 'active', 'config' => ['enabled' => false, 'mode' => 'sandbox']]
        );

        $products = Product::query()
            ->forStorefront()
            ->with(['variants' => fn ($q) => $q->active()->orderBy('id')])
            ->orderBy('id')
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($customer, $shipping, $billing, $payhere, $products, $inventory, $cart): void {
            $this->seedOrder(
                orderNumber: 'DEMO-CUSTOMER-ORDER-001',
                customer: $customer,
                billing: $billing,
                shipping: $shipping,
                status: 'processing',
                paymentStatus: 'paid',
                withPayment: true,
                confirmSale: true,
                payhere: $payhere,
                products: $products,
                inventory: $inventory,
                cart: $cart,
            );

            $this->seedOrder(
                orderNumber: 'DEMO-CUSTOMER-ORDER-002',
                customer: $customer,
                billing: $billing,
                shipping: $shipping,
                status: 'pending',
                paymentStatus: 'unpaid',
                withPayment: false,
                confirmSale: false,
                payhere: $payhere,
                products: $products,
                inventory: $inventory,
                cart: $cart,
            );
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     */
    private function seedOrder(
        string $orderNumber,
        Customer $customer,
        CustomerAddress $billing,
        CustomerAddress $shipping,
        string $status,
        string $paymentStatus,
        bool $withPayment,
        bool $confirmSale,
        PaymentMethod $payhere,
        $products,
        InventoryService $inventory,
        CartService $cart,
    ): void {
        $order = Order::query()->updateOrCreate(
            ['order_number' => $orderNumber],
            [
                'customer_id' => $customer->getKey(),
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'fulfillment_status' => 'unfulfilled',
                'currency_code' => config('open_ecommerce_laravel.store.currency_code', 'LKR'),
                'subtotal' => '0',
                'discount_total' => '0',
                'tax_total' => '0',
                'shipping_total' => '500.0000',
                'grand_total' => '0',
                'paid_total' => '0',
                'refunded_total' => '0',
                'customer_note' => null,
                'admin_note' => null,
                'placed_at' => now(),
                'cancelled_at' => null,
                'metadata' => null,
            ]
        );

        $order->items()->delete();
        $order->addresses()->delete();
        $order->statusHistories()->delete();
        $order->notes()->delete();
        $order->payments()->delete();

        $pick = $products->first();
        $variant = $pick->product_type === 'variable' ? $pick->variants->first() : null;
        $unit = (float) $cart->unitPriceFor($pick, $variant);
        $qty = 1.0;
        $sub = $unit * $qty;

        OrderItem::query()->create([
            'order_id' => $order->getKey(),
            'product_id' => $pick->getKey(),
            'variant_id' => $variant?->getKey(),
            'product_name' => $pick->name,
            'sku' => $variant?->sku ?? $pick->sku,
            'quantity' => $qty,
            'unit_price' => number_format($unit, 4, '.', ''),
            'subtotal' => number_format($sub, 4, '.', ''),
            'discount_total' => '0',
            'tax_total' => '0',
            'total' => number_format($sub, 4, '.', ''),
            'metadata' => null,
        ]);

        $order->subtotal = number_format($sub, 4, '.', '');
        $order->grand_total = number_format($sub + 500.0, 4, '.', '');
        if ($paymentStatus === 'paid') {
            $order->paid_total = $order->grand_total;
        }
        $order->save();

        OrderAddress::query()->create([
            'order_id' => $order->getKey(),
            'type' => 'billing',
            'first_name' => $billing->first_name,
            'last_name' => $billing->last_name,
            'phone' => $billing->phone,
            'email' => $billing->email,
            'address_line_1' => $billing->address_line_1,
            'address_line_2' => $billing->address_line_2,
            'city' => $billing->city,
            'district' => $billing->district,
            'province' => $billing->province,
            'postal_code' => $billing->postal_code,
            'country_code' => $billing->country_code,
        ]);

        OrderAddress::query()->create([
            'order_id' => $order->getKey(),
            'type' => 'shipping',
            'first_name' => $shipping->first_name,
            'last_name' => $shipping->last_name,
            'phone' => $shipping->phone,
            'email' => $shipping->email,
            'address_line_1' => $shipping->address_line_1,
            'address_line_2' => $shipping->address_line_2,
            'city' => $shipping->city,
            'district' => $shipping->district,
            'province' => $shipping->province,
            'postal_code' => $shipping->postal_code,
            'country_code' => $shipping->country_code,
        ]);

        OrderStatusHistory::query()->create([
            'order_id' => $order->getKey(),
            'from_status' => null,
            'to_status' => $status,
            'note' => 'Demo customer order seeded.',
            'changed_by_admin_id' => null,
            'changed_by_customer_id' => $customer->getKey(),
            'created_at' => now(),
        ]);

        if ($withPayment) {
            $payment = Payment::query()->updateOrCreate(
                ['order_id' => $order->getKey(), 'payment_method_id' => $payhere->getKey()],
                [
                    'payment_reference' => 'PAY-'.$order->order_number.'-DEMO',
                    'provider_transaction_id' => 'PH-DEMO-'.$order->order_number,
                    'status' => 'paid',
                    'amount' => $order->grand_total,
                    'currency_code' => $order->currency_code,
                    'gateway_response' => ['status_code' => 2, 'order_id' => 'PAY-'.$order->order_number.'-DEMO'],
                    'paid_at' => now(),
                ]
            );

            PaymentTransaction::query()->create([
                'payment_id' => $payment->getKey(),
                'order_id' => $order->getKey(),
                'provider' => 'payhere',
                'transaction_type' => 'notify',
                'provider_reference' => $payment->payment_reference,
                'status' => 'success',
                'request_payload' => ['status_code' => 2, 'order_id' => $payment->payment_reference],
                'response_payload' => null,
                'signature_valid' => true,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'created_at' => now(),
            ]);
        }

        // Inventory: ensure reservation exists; confirm sale for paid.
        $order->load('items');

        $reservationExists = InventoryMovement::query()
            ->where('reference_type', 'order')
            ->where('reference_id', $order->getKey())
            ->where('type', 'reservation')
            ->exists();

        if (! $reservationExists) {
            foreach ($order->items as $oi) {
                if (! $oi->product_id) {
                    continue;
                }

                $product = Product::query()->find($oi->product_id);
                if (! $product) {
                    continue;
                }

                $variantObj = $oi->variant_id ? ProductVariant::query()->find($oi->variant_id) : null;
                $stockRows = InventoryStock::query()
                    ->where('product_id', $product->getKey())
                    ->when($variantObj, fn ($q) => $q->where('variant_id', $variantObj->getKey()), fn ($q) => $q->whereNull('variant_id'))
                    ->orderByDesc('available_quantity')
                    ->get();

                foreach ($stockRows as $sr) {
                    $warehouse = Warehouse::query()->find($sr->warehouse_id);
                    if ($warehouse) {
                        $inventory->reserveStock($warehouse, $product, $variantObj, (float) $oi->quantity, 'Demo reservation', null, 'order', $order->getKey());
                        break;
                    }
                }
            }
        }

        if ($confirmSale) {
            $inventory->confirmSaleForOrder($order->fresh(['items']));
        }
    }
}

