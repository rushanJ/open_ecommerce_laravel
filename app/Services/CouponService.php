<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function findByCode(string $code): ?Coupon
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }

        return Coupon::query()
            ->where('code', $code)
            ->first();
    }

    /**
     * @return array{valid: bool, message: string|null, discount_amount: string, free_shipping: bool}
     */
    public function validateCoupon(Coupon $coupon, Cart $cart, ?Customer $customer = null): array
    {
        $now = now();

        if ($coupon->status !== 'active') {
            return $this->invalid(__('customer.coupon_invalid'));
        }

        if ($coupon->starts_at !== null && $coupon->starts_at->gt($now)) {
            return $this->invalid(__('customer.coupon_not_started'));
        }

        if ($coupon->ends_at !== null && $coupon->ends_at->lt($now)) {
            return $this->invalid(__('customer.coupon_expired'));
        }

        if ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit) {
            return $this->invalid(__('customer.coupon_usage_exceeded'));
        }

        if ($coupon->usage_limit_per_customer !== null) {
            if ($customer === null) {
                return $this->invalid(__('customer.coupon_not_applicable'));
            }

            $usedByCustomer = CouponUsage::query()
                ->where('coupon_id', $coupon->getKey())
                ->where('customer_id', $customer->getKey())
                ->count();

            if ($usedByCustomer >= (int) $coupon->usage_limit_per_customer) {
                return $this->invalid(__('customer.coupon_usage_exceeded'));
            }
        }

        $cart->loadMissing([
            'items.product.categories',
            'items.variant',
        ]);

        $itemsSubtotal = (float) $cart->items()->sum('subtotal');

        if ($coupon->minimum_order_amount !== null && $itemsSubtotal < (float) $coupon->minimum_order_amount) {
            return $this->invalid(__('customer.coupon_minimum_not_met'));
        }

        // Customer restrictions
        if ($coupon->customers()->exists()) {
            if ($customer === null) {
                return $this->invalid(__('customer.coupon_not_applicable'));
            }

            $allowed = $coupon->customers()->whereKey($customer->getKey())->exists();
            if (! $allowed) {
                return $this->invalid(__('customer.coupon_not_applicable'));
            }
        }

        $eligibleSubtotal = $this->eligibleSubtotal($coupon, $cart);
        if ($this->hasEligibilityRestrictions($coupon) && $eligibleSubtotal <= 0) {
            return $this->invalid(__('customer.coupon_not_applicable'));
        }

        $freeShipping = $coupon->type === 'free_shipping';
        $discount = 0.0;

        if ($coupon->type === 'percentage') {
            $discount = $eligibleSubtotal * ((float) $coupon->value / 100.0);
        } elseif ($coupon->type === 'fixed_cart') {
            $discount = min((float) $coupon->value, $itemsSubtotal);
        } elseif ($coupon->type === 'fixed_product') {
            $discount = min((float) $coupon->value, $eligibleSubtotal);
        } elseif ($coupon->type === 'free_shipping') {
            $discount = 0.0;
        } else {
            return $this->invalid(__('customer.coupon_invalid'));
        }

        if ($coupon->maximum_discount_amount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount_amount);
        }

        $discount = max(0.0, $discount);

        return [
            'valid' => true,
            'message' => null,
            'discount_amount' => $this->decimalString($discount),
            'free_shipping' => $freeShipping,
        ];
    }

    public function applyCouponToCart(Cart $cart, string $code, ?Customer $customer = null): Cart
    {
        $coupon = $this->findByCode($code);
        if ($coupon === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'code' => [__('customer.coupon_invalid')],
            ]);
        }

        $result = $this->validateCoupon($coupon, $cart, $customer);
        if (! $result['valid']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'code' => [$result['message'] ?? __('customer.coupon_invalid')],
            ]);
        }

        return DB::transaction(function () use ($cart, $coupon, $result): Cart {
            /** @var Cart $locked */
            $locked = Cart::query()->lockForUpdate()->whereKey($cart->getKey())->firstOrFail();

            $locked->coupon_id = $coupon->getKey();
            $locked->coupon_code = $coupon->code;
            $locked->discount_total = $result['discount_amount'];

            if ($result['free_shipping']) {
                $locked->shipping_total = '0';
            }

            $subtotal = (float) $locked->items()->sum('subtotal');
            $tax = (float) ($locked->tax_total ?? 0);
            $shipping = (float) ($locked->shipping_total ?? 0);
            $discount = (float) $locked->discount_total;

            $locked->subtotal = $this->decimalString($subtotal);
            $locked->grand_total = $this->decimalString(max(0.0, $subtotal - $discount + $tax + $shipping));
            $locked->save();

            return $locked->fresh(['items']);
        });
    }

    public function removeCouponFromCart(Cart $cart, CartService $cartService): Cart
    {
        return DB::transaction(function () use ($cart, $cartService): Cart {
            /** @var Cart $locked */
            $locked = Cart::query()->lockForUpdate()->whereKey($cart->getKey())->firstOrFail();

            $locked->coupon_id = null;
            $locked->coupon_code = null;
            $locked->discount_total = '0';
            $locked->save();

            return $cartService->recalculate($locked->fresh(['items.product', 'items.variant']));
        });
    }

    public function recordCouponUsage(Order $order): ?CouponUsage
    {
        if (! $order->coupon_id) {
            return null;
        }

        return DB::transaction(function () use ($order): ?CouponUsage {
            $order = $order->fresh();
            if (! $order || ! $order->coupon_id) {
                return null;
            }

            $exists = CouponUsage::query()
                ->where('coupon_id', $order->coupon_id)
                ->where('order_id', $order->getKey())
                ->exists();

            if ($exists) {
                return CouponUsage::query()
                    ->where('coupon_id', $order->coupon_id)
                    ->where('order_id', $order->getKey())
                    ->first();
            }

            $usage = CouponUsage::query()->create([
                'coupon_id' => $order->coupon_id,
                'order_id' => $order->getKey(),
                'customer_id' => $order->customer_id,
                'discount_amount' => $order->discount_total ?? '0',
                'used_at' => now(),
            ]);

            Coupon::query()
                ->whereKey($order->coupon_id)
                ->update(['used_count' => DB::raw('used_count + 1')]);

            return $usage;
        });
    }

    public function releaseCouponUsage(Order $order): void
    {
        // Not auto-releasing in this stage by design.
    }

    private function invalid(?string $message): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'discount_amount' => $this->decimalString(0),
            'free_shipping' => false,
        ];
    }

    private function decimalString(float $value): string
    {
        return number_format($value, 4, '.', '');
    }

    private function hasEligibilityRestrictions(Coupon $coupon): bool
    {
        return $coupon->products()->exists() || $coupon->categories()->exists();
    }

    private function eligibleSubtotal(Coupon $coupon, Cart $cart): float
    {
        $restrictedProducts = $coupon->products()->pluck('products.id')->map(fn ($id) => (int) $id)->all();
        $restrictedCategories = $coupon->categories()->pluck('categories.id')->map(fn ($id) => (int) $id)->all();

        $hasProductRestriction = $restrictedProducts !== [];
        $hasCategoryRestriction = $restrictedCategories !== [];

        if (! $hasProductRestriction && ! $hasCategoryRestriction) {
            return (float) $cart->items()->sum('subtotal');
        }

        $sum = 0.0;
        foreach ($cart->items as $line) {
            $product = $line->product;
            if (! $product) {
                continue;
            }

            $eligible = false;
            if ($hasProductRestriction && in_array((int) $product->getKey(), $restrictedProducts, true)) {
                $eligible = true;
            }

            if (! $eligible && $hasCategoryRestriction) {
                $catIds = $product->categories?->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];
                $eligible = count(array_intersect($catIds, $restrictedCategories)) > 0;
            }

            if ($eligible) {
                $sum += (float) $line->subtotal;
            }
        }

        return max(0.0, $sum);
    }
}

