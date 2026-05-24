<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Database\Seeder;

/**
 * Dev-only cart sanity check. Not registered in DatabaseSeeder.
 *
 * php artisan db:seed --class=CartDemoSeeder
 */
class CartDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::query()->firstOrCreate(
            ['email' => 'demo@open-ecommerce-laravel.test'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Customer',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );

        /** @var CartService $cartService */
        $cartService = app(CartService::class);

        $cart = Cart::query()->updateOrCreate(
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

        $cart->items()->delete();

        $lines = [];

        $simple = Product::query()->forStorefront()->where('product_type', 'simple')->orderBy('id')->first();
        if ($simple !== null) {
            $lines[] = ['product' => $simple, 'variant' => null, 'qty' => 1.0];
        }

        $variable = Product::query()->forStorefront()->where('product_type', 'variable')->orderBy('id')->first();
        if ($variable !== null) {
            $variable->load(['variants' => fn ($q) => $q->active()->orderBy('id')]);
            $v1 = $variable->variants->first();
            if ($v1 !== null) {
                $lines[] = ['product' => $variable, 'variant' => $v1, 'qty' => 1.0];
            }
            $v2 = $variable->variants->skip(1)->first();
            if ($v2 !== null) {
                $lines[] = ['product' => $variable, 'variant' => $v2, 'qty' => 2.0];
            }
        }

        foreach ($lines as $line) {
            $product = $line['product'];
            $variant = $line['variant'];
            $qty = (float) $line['qty'];
            $unit = $cartService->unitPriceFor($product, $variant);
            $subtotal = number_format($qty * (float) $unit, 4, '.', '');

            CartItem::query()->create([
                'cart_id' => $cart->getKey(),
                'product_id' => $product->getKey(),
                'variant_id' => $variant?->getKey(),
                'quantity' => $qty,
                'unit_price' => $unit,
                'subtotal' => $subtotal,
                'metadata' => null,
            ]);
        }

        $cartService->recalculate($cart->fresh());
    }
}
