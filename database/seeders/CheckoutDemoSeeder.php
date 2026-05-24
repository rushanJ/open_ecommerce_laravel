<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Database\Seeder;

/**
 * Optional dev-only: sanity-check checkout path. Not in DatabaseSeeder.
 *
 * php artisan db:seed --class=CheckoutDemoSeeder
 */
class CheckoutDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::query()->firstOrCreate(
            ['email' => 'checkout-demo@open-ecommerce-laravel.test'],
            [
                'first_name' => 'Checkout',
                'last_name' => 'Demo',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );

        /** @var CartService $cartService */
        $cartService = app(CartService::class);

        $activeCart = Cart::query()->updateOrCreate(
            [
                'customer_id' => $customer->getKey(),
                'status' => 'active',
            ],
            [
                'session_id' => null,
                'currency_code' => config('open_ecommerce_laravel.store.currency_code', 'LKR'),
                'expires_at' => now()->addDays(30),
            ]
        );

        if ($activeCart->items()->count() > 0) {
            return;
        }

        $product = Product::query()->forStorefront()->where('product_type', 'simple')->orderBy('id')->first();
        $variant = null;
        if ($product === null) {
            $product = Product::query()->forStorefront()->where('product_type', 'variable')->orderBy('id')->first();
            if ($product !== null) {
                $product->load(['variants' => fn ($q) => $q->active()->orderBy('id')]);
                $variant = $product->variants->first();
            }
        }

        if ($product === null) {
            return;
        }

        $unit = $cartService->unitPriceFor($product, $variant);
        $qty = 1.0;
        $sub = number_format($qty * (float) $unit, 4, '.', '');

        CartItem::query()->create([
            'cart_id' => $activeCart->getKey(),
            'product_id' => $product->getKey(),
            'variant_id' => $variant?->getKey(),
            'quantity' => $qty,
            'unit_price' => $unit,
            'subtotal' => $sub,
            'metadata' => null,
        ]);

        $cartService->recalculate($activeCart->fresh());
    }
}
