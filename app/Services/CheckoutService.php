<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use App\Services\CouponService;
use App\Services\NotificationService;
use App\Services\StoreService;
use App\Services\TaxService;
use App\Services\WebhookService;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected CouponService $couponService,
        protected ShippingService $shippingService,
        protected OrderNumberService $orderNumberService,
        protected InventoryService $inventoryService,
        protected TaxService $taxService,
        protected NotificationService $notifications,
        protected WebhookService $webhooks,
    ) {}

    /**
     * @return array{cart: Cart, shipping_rates: \Illuminate\Support\Collection, preview_address: array<string, string|null>}
     */
    public function getCheckoutData(Request $request, Cart $cart): array
    {
        $previewAddress = $this->buildPreviewShippingAddress($request);
        $cart = $this->cartService->recalculate($cart->fresh(['items.product', 'items.variant']), $previewAddress);
        $shippingRates = $this->shippingService->getAvailableRates($previewAddress, $cart);

        return [
            'cart' => $cart,
            'shipping_rates' => $shippingRates,
            'preview_address' => $previewAddress,
        ];
    }

    public function calculateCartWithShipping(Cart $cart, ?int $shippingRateId): Cart
    {
        $cart = $this->cartService->recalculate($cart->fresh(['items.product', 'items.variant']));

        if ($shippingRateId === null) {
            return $cart;
        }

        $rate = ShippingRate::query()
            ->active()
            ->with('method')
            ->find($shippingRateId);

        if ($rate === null || ! $rate->method || $rate->method->status !== 'active') {
            return $cart;
        }

        $amount = $this->shippingService->calculateRate($rate, $cart);

        return $this->cartService->applyShippingTotal($cart, $amount);
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     */
    public function createDraftOrderFromCart(Cart $cart, array $checkoutData): Order
    {
        return DB::transaction(function () use ($cart, $checkoutData): Order {
            /** @var Cart $lockedCart */
            $lockedCart = Cart::query()->lockForUpdate()->whereKey($cart->id)->firstOrFail();

            if ($lockedCart->status !== 'active' || $lockedCart->items()->count() === 0) {
                throw ValidationException::withMessages([
                    'cart' => [__('customer.cart_empty')],
                ]);
            }

            $lockedCart->load(['items.product', 'items.variant']);

            // Recalculate totals + tax using shipping address (default tax basis).
            $shippingAddress = $this->resolveShippingAddressFromCheckoutData($checkoutData);
            $this->cartService->recalculate($lockedCart, $shippingAddress);
            $lockedCart->refresh();

            foreach ($lockedCart->items as $line) {
                $this->assertCartLineStillValid($line->product, $line->variant, (float) $line->quantity);
            }

            $allowedIds = $this->shippingService->getAvailableRates($shippingAddress, $lockedCart)
                ->pluck('rate.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $rateId = (int) $checkoutData['shipping_rate_id'];
            if (! in_array($rateId, $allowedIds, true)) {
                throw ValidationException::withMessages([
                    'shipping_rate_id' => [__('customer.no_shipping_methods')],
                ]);
            }

            $rate = ShippingRate::query()->with(['method', 'zone'])->whereKey($rateId)->firstOrFail();
            $shippingTotal = $this->shippingService->calculateRate($rate, $lockedCart);
            $itemsSubtotal = (float) $lockedCart->items()->sum('subtotal');
            $discountTotal = (float) ($lockedCart->discount_total ?? 0);
            $taxTotal = (float) ($lockedCart->tax_total ?? 0);

            $currency = $lockedCart->currency_code;
            if ($currency === null || $currency === '') {
                $currency = app(StoreService::class)->defaultCurrencyCode();
            }
            $orderNumber = $this->orderNumberService->generate();

            $customerId = Auth::guard('customer')->check() ? Auth::guard('customer')->id() : null;

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'customer_email' => $checkoutData['customer_email'],
                'customer_phone' => $checkoutData['customer_phone'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'fulfillment_status' => 'unfulfilled',
                'currency_code' => $currency,
                'coupon_id' => $lockedCart->coupon_id,
                'coupon_code' => $lockedCart->coupon_code,
                'subtotal' => number_format($itemsSubtotal, 4, '.', ''),
                'discount_total' => number_format($discountTotal, 4, '.', ''),
                'tax_total' => number_format($taxTotal, 4, '.', ''),
                'shipping_total' => number_format($shippingTotal, 4, '.', ''),
                'grand_total' => number_format(max(0.0, $itemsSubtotal - $discountTotal + $taxTotal + $shippingTotal), 4, '.', ''),
                'paid_total' => '0',
                'refunded_total' => '0',
                'customer_note' => $checkoutData['customer_note'] ?? null,
                'admin_note' => null,
                'placed_at' => now(),
                'cancelled_at' => null,
                'metadata' => null,
            ]);

            foreach ($lockedCart->items as $line) {
                $product = $line->product;
                $variant = $line->variant;
                if ($product === null) {
                    throw ValidationException::withMessages(['cart' => [__('customer.invalid_cart_item')]]);
                }

                $unitPrice = (float) $line->unit_price;
                $qty = (float) $line->quantity;
                $sub = (float) $line->subtotal;
                $itemTax = $this->taxService->calculateItemTax($line, $shippingAddress);

                OrderItem::query()->create([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'variant_id' => $variant?->getKey(),
                    'product_name' => $product->name,
                    'sku' => $variant?->sku ?? $product->sku,
                    'quantity' => $qty,
                    'unit_price' => number_format($unitPrice, 4, '.', ''),
                    'subtotal' => number_format($sub, 4, '.', ''),
                    'discount_total' => '0',
                    'tax_total' => number_format($itemTax, 4, '.', ''),
                    'total' => number_format(max(0.0, $sub + $itemTax), 4, '.', ''),
                    'metadata' => null,
                ]);
            }

            $this->createOrderAddresses($order, $checkoutData, $shippingAddress);

            OrderStatusHistory::query()->create([
                'order_id' => $order->getKey(),
                'from_status' => null,
                'to_status' => 'pending',
                'note' => 'Order created from checkout.',
                'changed_by_admin_id' => null,
                'changed_by_customer_id' => Auth::guard('customer')->check() ? Auth::guard('customer')->id() : null,
                'created_at' => now(),
            ]);

            $order->load('items');

            foreach ($order->items as $orderItem) {
                $product = Product::query()->find($orderItem->product_id);
                if ($product === null) {
                    continue;
                }
                $variant = $orderItem->variant_id ? ProductVariant::query()->find($orderItem->variant_id) : null;
                $this->reserveLineInventory($order, $product, $variant, (float) $orderItem->quantity);
            }

            $this->couponService->recordCouponUsage($order);

            $lockedCart->status = 'converted';
            $lockedCart->save();

            $orderId = $order->getKey();
            DB::afterCommit(function () use ($orderId): void {
                try {
                    $fresh = Order::query()->find($orderId);
                    if ($fresh !== null) {
                        $this->notifications->notifyOrderCreated($fresh);
                    }
                } catch (\Throwable $e) {
                    Log::error('notifyOrderCreated hook failed', [
                        'order_id' => $orderId,
                        'exception' => $e->getMessage(),
                    ]);
                }
                try {
                    $this->webhooks->dispatchSafe('order.created', ['order_id' => $orderId]);
                } catch (\Throwable $e) {
                    Log::error('order.created webhook dispatch failed', [
                        'order_id' => $orderId,
                        'exception' => $e->getMessage(),
                    ]);
                }
            });

            return $order->fresh(['items', 'billingAddress', 'shippingAddress']);
        });
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     * @param  array<string, string|null>  $shippingAddress
     */
    protected function createOrderAddresses(Order $order, array $checkoutData, array $shippingAddress): void
    {
        OrderAddress::query()->create([
            'order_id' => $order->getKey(),
            'type' => 'billing',
            'first_name' => $checkoutData['billing_first_name'],
            'last_name' => $checkoutData['billing_last_name'] ?? null,
            'phone' => $checkoutData['billing_phone'] ?? null,
            'email' => $checkoutData['billing_email'] ?? null,
            'address_line_1' => $checkoutData['billing_address_line_1'],
            'address_line_2' => $checkoutData['billing_address_line_2'] ?? null,
            'city' => $checkoutData['billing_city'],
            'district' => $checkoutData['billing_district'] ?? null,
            'province' => $checkoutData['billing_province'] ?? null,
            'postal_code' => $checkoutData['billing_postal_code'] ?? null,
            'country_code' => $checkoutData['billing_country_code'],
        ]);

        OrderAddress::query()->create([
            'order_id' => $order->getKey(),
            'type' => 'shipping',
            'first_name' => $shippingAddress['first_name'],
            'last_name' => $shippingAddress['last_name'] ?? null,
            'phone' => $shippingAddress['phone'] ?? null,
            'email' => $shippingAddress['email'] ?? null,
            'address_line_1' => $shippingAddress['address_line_1'],
            'address_line_2' => $shippingAddress['address_line_2'] ?? null,
            'city' => $shippingAddress['city'],
            'district' => $shippingAddress['district'] ?? null,
            'province' => $shippingAddress['province'] ?? null,
            'postal_code' => $shippingAddress['postal_code'] ?? null,
            'country_code' => $shippingAddress['country_code'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     * @return array<string, string|null>
     */
    protected function resolveShippingAddressFromCheckoutData(array $checkoutData): array
    {
        $different = filter_var($checkoutData['ship_to_different_address'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $different) {
            return [
                'first_name' => $checkoutData['billing_first_name'],
                'last_name' => $checkoutData['billing_last_name'] ?? null,
                'phone' => $checkoutData['customer_phone'],
                'email' => $checkoutData['customer_email'],
                'address_line_1' => $checkoutData['billing_address_line_1'],
                'address_line_2' => $checkoutData['billing_address_line_2'] ?? null,
                'city' => $checkoutData['billing_city'],
                'district' => $checkoutData['billing_district'] ?? null,
                'province' => $checkoutData['billing_province'] ?? null,
                'postal_code' => $checkoutData['billing_postal_code'] ?? null,
                'country_code' => $checkoutData['billing_country_code'],
            ];
        }

        return [
            'first_name' => (string) ($checkoutData['shipping_first_name'] ?? ''),
            'last_name' => $checkoutData['shipping_last_name'] ?? null,
            'phone' => $checkoutData['shipping_phone'] ?? $checkoutData['customer_phone'],
            'email' => $checkoutData['shipping_email'] ?? $checkoutData['customer_email'],
            'address_line_1' => (string) ($checkoutData['shipping_address_line_1'] ?? ''),
            'address_line_2' => $checkoutData['shipping_address_line_2'] ?? null,
            'city' => (string) ($checkoutData['shipping_city'] ?? ''),
            'district' => $checkoutData['shipping_district'] ?? null,
            'province' => $checkoutData['shipping_province'] ?? null,
            'postal_code' => $checkoutData['shipping_postal_code'] ?? null,
            'country_code' => $checkoutData['shipping_country_code'] ?? $checkoutData['billing_country_code'],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    protected function buildPreviewShippingAddress(Request $request): array
    {
        $old = $request->old();

        return [
            'country_code' => $old['shipping_country_code'] ?? $old['billing_country_code'] ?? 'LK',
            'province' => $old['shipping_province'] ?? $old['billing_province'] ?? 'Western',
            'district' => $old['shipping_district'] ?? $old['billing_district'] ?? 'Colombo',
            'city' => $old['shipping_city'] ?? $old['billing_city'] ?? 'Colombo',
        ];
    }

    protected function assertCartLineStillValid(?Product $product, ?ProductVariant $variant, float $quantity): void
    {
        if ($product === null) {
            throw ValidationException::withMessages(['cart' => [__('customer.invalid_cart_item')]]);
        }

        if (! Product::query()->whereKey($product->getKey())->forStorefront()->exists()) {
            throw ValidationException::withMessages(['cart' => [__('customer.product_unavailable')]]);
        }

        $available = $this->cartService->getAvailableStock($product, $variant);

        if ($quantity > $available && ! $product->backorders_allowed) {
            throw ValidationException::withMessages(['cart' => [__('customer.insufficient_stock')]]);
        }
    }

    protected function reserveLineInventory(Order $order, Product $product, ?ProductVariant $variant, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $remaining = $quantity;

        $rows = InventoryStock::query()
            ->where('product_id', $product->getKey())
            ->when($variant !== null,
                fn ($q) => $q->where('variant_id', $variant->getKey()),
                fn ($q) => $q->whereNull('variant_id'))
            ->orderByDesc('available_quantity')
            ->lockForUpdate()
            ->get();

        foreach ($rows as $stockRow) {
            if ($remaining <= 0) {
                break;
            }

            $avail = max(0.0, (float) $stockRow->quantity - (float) $stockRow->reserved_quantity);
            $take = min($remaining, $avail);

            if ($take <= 0) {
                continue;
            }

            $warehouse = Warehouse::query()->findOrFail($stockRow->warehouse_id);

            try {
                $this->inventoryService->reserveStock(
                    $warehouse,
                    $product,
                    $variant,
                    $take,
                    'Order '.$order->order_number,
                    null,
                    'order',
                    $order->getKey()
                );
            } catch (InvalidArgumentException $e) {
                throw ValidationException::withMessages(['cart' => [$e->getMessage()]]);
            }

            $remaining -= $take;
        }

        if ($remaining > 0 && ! $product->backorders_allowed) {
            throw ValidationException::withMessages(['cart' => [__('customer.insufficient_stock')]]);
        }
    }
}
