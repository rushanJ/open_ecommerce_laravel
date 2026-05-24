<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CouponService;
use App\Services\StoreService;
use App\Services\TaxService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const float UNLIMITED = 999999.0;

    /**
     * When set (API guest cart), overrides session id for guest cart resolution.
     */
    private ?string $apiGuestSessionOverride = null;

    /**
     * @var array<string, float>
     */
    private array $stockCache = [];

    /**
     * @template T
     * @param  callable(): T  $callback
     * @return T
     */
    public function usingGuestSessionId(?string $sessionId, callable $callback): mixed
    {
        $previous = $this->apiGuestSessionOverride;
        $this->apiGuestSessionOverride = $sessionId;

        try {
            return $callback();
        } finally {
            $this->apiGuestSessionOverride = $previous;
        }
    }

    public function authenticatedCustomer(): ?Customer
    {
        /** @var Customer|null $sessionCustomer */
        $sessionCustomer = Auth::guard('customer')->user();
        if ($sessionCustomer instanceof Customer) {
            return $sessionCustomer;
        }

        $sanctumUser = Auth::guard('sanctum')->user();

        return $sanctumUser instanceof Customer ? $sanctumUser : null;
    }

    public function getCurrentCart(): Cart
    {
        $cart = $this->baseCartQuery()->first();

        if ($cart === null) {
            $cart = $this->createNewCart();
        }

        return $cart;
    }

    public function addItem(Product $product, ?ProductVariant $variant, float $quantity): Cart
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => [__('customer.insufficient_stock')],
            ]);
        }

        $this->assertProductSellable($product, $variant);

        $available = $this->getAvailableStock($product, $variant);
        $cart = $this->getCurrentCart();

        return DB::transaction(function () use ($cart, $product, $variant, $quantity, $available): Cart {
            /** @var Cart $lockedCart */
            $lockedCart = Cart::query()->lockForUpdate()->whereKey($cart->id)->firstOrFail();

            $unitPrice = $this->resolveUnitPrice($product, $variant);
            $line = $this->findLineItem($lockedCart, $product->getKey(), $variant?->getKey());

            if ($line !== null) {
                $newQty = (float) $line->quantity + $quantity;
                if ($newQty > $available) {
                    throw ValidationException::withMessages([
                        'quantity' => [__('customer.insufficient_stock')],
                    ]);
                }
                $line->quantity = $newQty;
                $line->unit_price = $unitPrice;
                $line->subtotal = $this->lineSubtotal($newQty, $unitPrice);
                $line->save();
            } else {
                if ($quantity > $available) {
                    throw ValidationException::withMessages([
                        'quantity' => [__('customer.insufficient_stock')],
                    ]);
                }
                $lockedCart->items()->create([
                    'product_id' => $product->getKey(),
                    'variant_id' => $variant?->getKey(),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $this->lineSubtotal($quantity, $unitPrice),
                    'metadata' => null,
                ]);
            }

            return $this->recalculate($lockedCart->fresh());
        });
    }

    public function updateItem(CartItem $item, float $quantity): Cart
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => [__('customer.insufficient_stock')],
            ]);
        }

        $product = $item->product;
        if ($product === null) {
            throw ValidationException::withMessages(['cart' => [__('customer.invalid_cart_item')]]);
        }

        $variant = $item->variant;
        $this->assertProductSellable($product, $variant);

        $available = $this->getAvailableStock($product, $variant);

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => [__('customer.insufficient_stock')],
            ]);
        }

        return DB::transaction(function () use ($item, $quantity, $product, $variant): Cart {
            /** @var CartItem $locked */
            $locked = CartItem::query()->lockForUpdate()->whereKey($item->id)->firstOrFail();

            $unitPrice = $this->resolveUnitPrice($product, $variant);
            $locked->quantity = $quantity;
            $locked->unit_price = $unitPrice;
            $locked->subtotal = $this->lineSubtotal($quantity, $unitPrice);
            $locked->save();

            $cart = $locked->cart;
            if ($cart === null) {
                throw ValidationException::withMessages(['cart' => [__('customer.invalid_cart_item')]]);
            }

            return $this->recalculate($cart->fresh());
        });
    }

    public function removeItem(CartItem $item): Cart
    {
        $cart = $item->cart;
        if ($cart === null) {
            throw ValidationException::withMessages(['cart' => [__('customer.invalid_cart_item')]]);
        }

        return DB::transaction(function () use ($item, $cart): Cart {
            $item->delete();

            return $this->recalculate($cart->fresh());
        });
    }

    public function clearCart(Cart $cart): Cart
    {
        return DB::transaction(function () use ($cart): Cart {
            /** @var Cart $lockedCart */
            $lockedCart = Cart::query()->lockForUpdate()->whereKey($cart->id)->firstOrFail();
            $lockedCart->items()->delete();

            return $this->recalculate($lockedCart->fresh());
        });
    }

    /**
     * @param  array<string, mixed>  $address
     */
    public function recalculate(Cart $cart, array $address = []): Cart
    {
        $subtotal = (float) $cart->items()->sum('subtotal');
        $shippingTotal = (float) ($cart->shipping_total ?? 0);
        /** @var TaxService $taxes */
        $taxes = app(TaxService::class);
        $taxTotal = $taxes->calculateCartTax($cart->fresh(['items.product.taxClass', 'items.variant']), $address);
        $discountTotal = 0.0;

        if ($cart->coupon_id) {
            /** @var CouponService $coupons */
            $coupons = app(CouponService::class);
            $customer = $this->authenticatedCustomer();

            $coupon = \App\Models\Coupon::query()->find($cart->coupon_id);
            if ($coupon) {
                $result = $coupons->validateCoupon($coupon, $cart, $customer);
                if ($result['valid']) {
                    $discountTotal = (float) $result['discount_amount'];
                    if ($result['free_shipping']) {
                        $shippingTotal = 0.0;
                    }
                } else {
                    // Auto-remove invalid coupon
                    $cart->coupon_id = null;
                    $cart->coupon_code = null;
                }
            } else {
                $cart->coupon_id = null;
                $cart->coupon_code = null;
            }
        }

        $cart->subtotal = $this->decimalString($subtotal);
        $cart->discount_total = $this->decimalString(max(0.0, $discountTotal));
        $cart->tax_total = $this->decimalString(max(0.0, $taxTotal));
        $cart->shipping_total = $this->decimalString(max(0.0, $shippingTotal));
        $cart->grand_total = $this->decimalString(max(0.0, $subtotal - $discountTotal + $taxTotal + $shippingTotal));
        $cart->save();

        $this->clearStockCache();

        return $cart->fresh(['items']);
    }

    public function applyShippingTotal(Cart $cart, float $shippingTotal): Cart
    {
        return DB::transaction(function () use ($cart, $shippingTotal): Cart {
            /** @var Cart $locked */
            $locked = Cart::query()->lockForUpdate()->whereKey($cart->id)->firstOrFail();
            $subtotal = (float) $locked->items()->sum('subtotal');
            $locked->subtotal = $this->decimalString($subtotal);
            $locked->discount_total = $this->decimalString((float) ($locked->discount_total ?? 0));
            $locked->tax_total = $this->decimalString((float) ($locked->tax_total ?? 0));
            $locked->shipping_total = $this->decimalString($shippingTotal);
            $locked->grand_total = $this->decimalString(max(0.0, $subtotal - (float) $locked->discount_total + (float) $locked->tax_total + $shippingTotal));
            $locked->save();
            $this->clearStockCache();

            return $locked->fresh(['items']);
        });
    }

    public function getAvailableStock(Product $product, ?ProductVariant $variant = null): float
    {
        $key = $product->getKey().'-'.($variant?->getKey() ?? '0');

        if (isset($this->stockCache[$key])) {
            return $this->stockCache[$key];
        }

        if ($this->allowsUnlimitedStock($product, $variant)) {
            $this->stockCache[$key] = self::UNLIMITED;

            return self::UNLIMITED;
        }

        if ($this->isBlockedOutOfStock($product, $variant)) {
            $this->stockCache[$key] = 0.0;

            return 0.0;
        }

        if ($variant !== null) {
            $hasInventory = InventoryStock::query()
                ->where('product_id', $product->getKey())
                ->where('variant_id', $variant->getKey())
                ->exists();

            if ($hasInventory) {
                $sum = (float) InventoryStock::query()
                    ->where('product_id', $product->getKey())
                    ->where('variant_id', $variant->getKey())
                    ->sum('available_quantity');
                $this->stockCache[$key] = max(0.0, $sum);

                return $this->stockCache[$key];
            }

            $qty = $variant->stock_quantity;
            $value = $qty !== null ? (float) $qty : ($product->manage_stock ? 0.0 : self::UNLIMITED);
            $this->stockCache[$key] = max(0.0, $value);

            return $this->stockCache[$key];
        }

        $hasInventory = InventoryStock::query()
            ->where('product_id', $product->getKey())
            ->whereNull('variant_id')
            ->exists();

        if ($hasInventory) {
            $sum = (float) InventoryStock::query()
                ->where('product_id', $product->getKey())
                ->whereNull('variant_id')
                ->sum('available_quantity');
            $this->stockCache[$key] = max(0.0, $sum);

            return $this->stockCache[$key];
        }

        $qty = $product->stock_quantity;
        $value = $qty !== null ? (float) $qty : ($product->manage_stock ? 0.0 : self::UNLIMITED);
        $this->stockCache[$key] = max(0.0, $value);

        return $this->stockCache[$key];
    }

    /**
     * Merge session guest cart into the customer's active cart (call after login).
     */
    public function mergeGuestCartToCustomerCart(Customer $customer): Cart
    {
        $sessionId = $this->guestSessionIdentifier();

        return DB::transaction(function () use ($customer, $sessionId): Cart {
            $guestCart = Cart::query()
                ->where('session_id', $sessionId)
                ->whereNull('customer_id')
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            /** @var Cart $customerCart */
            $customerCart = Cart::query()
                ->where('customer_id', $customer->getKey())
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if ($customerCart === null) {
                $customerCart = Cart::create([
                    'customer_id' => $customer->getKey(),
                    'session_id' => null,
                    'status' => 'active',
                    'currency_code' => $this->resolveStoreCurrency(),
                    'expires_at' => now()->addDays(30),
                ]);
            }

            if ($guestCart === null) {
                return $this->recalculate($customerCart->fresh());
            }

            $guestCart->load(['items.product', 'items.variant']);

            foreach ($guestCart->items as $guestLine) {
                $customerCart->refresh();

                $product = $guestLine->product;
                if ($product === null) {
                    continue;
                }

                $variant = $guestLine->variant;
                $qty = (float) $guestLine->quantity;

                try {
                    $this->assertProductSellable($product, $variant);
                } catch (ValidationException) {
                    continue;
                }

                $existing = $this->findLineItem($customerCart, $product->getKey(), $variant?->getKey());

                if ($existing !== null) {
                    $combined = (float) $existing->quantity + $qty;
                    try {
                        $customerCart = $this->updateItem($existing, $combined);
                    } catch (ValidationException) {
                        continue;
                    }
                } else {
                    $available = $this->getAvailableStock($product, $variant);
                    if ($qty > $available) {
                        $qty = $available;
                    }
                    if ($qty <= 0) {
                        continue;
                    }
                    $unitPrice = $this->resolveUnitPrice($product, $variant);
                    $customerCart->items()->create([
                        'product_id' => $product->getKey(),
                        'variant_id' => $variant?->getKey(),
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $this->lineSubtotal($qty, $unitPrice),
                        'metadata' => null,
                    ]);
                    $customerCart = $customerCart->fresh();
                }
            }

            $guestCart->items()->delete();
            $guestCart->delete();

            $this->clearStockCache();

            return $this->recalculate($customerCart->fresh());
        });
    }

    private function baseCartQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = Cart::query()->withCount('items');
        $customer = $this->authenticatedCustomer();

        if ($customer !== null) {
            $q->where('customer_id', $customer->getKey())
                ->where('status', 'active');
        } else {
            $q->whereNull('customer_id')
                ->where('session_id', $this->guestSessionIdentifier())
                ->where('status', 'active');
        }

        return $q;
    }

    private function guestSessionIdentifier(): string
    {
        if ($this->apiGuestSessionOverride !== null) {
            return $this->apiGuestSessionOverride;
        }

        return session()->getId();
    }

    private function createNewCart(): Cart
    {
        $customer = $this->authenticatedCustomer();

        $cart = Cart::create([
            'customer_id' => $customer?->getKey(),
            'session_id' => $customer !== null ? null : $this->guestSessionIdentifier(),
            'status' => 'active',
            'currency_code' => $this->resolveStoreCurrency(),
            'expires_at' => now()->addDays(30),
        ]);

        $cart->loadCount('items');

        return $cart;
    }

    private function findLineItem(Cart $cart, int $productId, ?int $variantId): ?CartItem
    {
        $q = $cart->items()->where('product_id', $productId);

        if ($variantId !== null) {
            $q->where('variant_id', $variantId);
        } else {
            $q->whereNull('variant_id');
        }

        return $q->first();
    }

    private function assertProductSellable(Product $product, ?ProductVariant $variant): void
    {
        if (! Product::query()->whereKey($product->getKey())->forStorefront()->exists()) {
            throw ValidationException::withMessages([
                'product_id' => [__('customer.product_unavailable')],
            ]);
        }

        if ($product->product_type === 'variable') {
            if ($variant === null) {
                throw ValidationException::withMessages([
                    'variant_id' => [__('customer.variant_required')],
                ]);
            }

            if ((int) $variant->product_id !== (int) $product->getKey()) {
                throw ValidationException::withMessages([
                    'variant_id' => [__('customer.invalid_cart_item')],
                ]);
            }

            if ($variant->status !== 'active') {
                throw ValidationException::withMessages([
                    'variant_id' => [__('customer.product_unavailable')],
                ]);
            }

            return;
        }

        if ($variant !== null) {
            throw ValidationException::withMessages([
                'variant_id' => [__('customer.invalid_cart_item')],
            ]);
        }
    }

    private function allowsUnlimitedStock(Product $product, ?ProductVariant $variant): bool
    {
        if ($product->backorders_allowed) {
            return true;
        }

        $status = $variant?->stock_status ?? $product->stock_status;

        return $status === 'on_backorder';
    }

    private function isBlockedOutOfStock(Product $product, ?ProductVariant $variant): bool
    {
        $status = $variant?->stock_status ?? $product->stock_status;

        return $status === 'out_of_stock' && ! $product->backorders_allowed;
    }

    private function resolveUnitPrice(Product $product, ?ProductVariant $variant): string
    {
        if ($variant !== null) {
            return $this->pickSaleOrRegular($variant->sale_price, $variant->regular_price);
        }

        return $this->pickSaleOrRegular($product->sale_price, $product->regular_price);
    }

    private function pickSaleOrRegular(mixed $sale, mixed $regular): string
    {
        $reg = (float) $regular;
        if ($sale !== null && (float) $sale > 0 && (float) $sale < $reg) {
            return $this->decimalString((float) $sale);
        }

        return $this->decimalString($reg);
    }

    private function lineSubtotal(float $qty, string $unitPrice): string
    {
        return $this->decimalString($qty * (float) $unitPrice);
    }

    private function decimalString(float $value): string
    {
        return number_format($value, 4, '.', '');
    }

    private function resolveStoreCurrency(): string
    {
        return app(StoreService::class)->defaultCurrencyCode();
    }

    private function clearStockCache(): void
    {
        $this->stockCache = [];
    }

    /**
     * @internal Used by seeders/tests; storefront uses addItem/updateItem.
     */
    public function unitPriceFor(Product $product, ?ProductVariant $variant = null): string
    {
        return $this->resolveUnitPrice($product, $variant);
    }
}
