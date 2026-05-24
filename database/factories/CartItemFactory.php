<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'quantity' => '1.0000',
            'unit_price' => '100.0000',
            'subtotal' => '100.0000',
            'metadata' => null,
        ];
    }

    public function forCart(Cart $cart): static
    {
        return $this->state(fn () => [
            'cart_id' => $cart->getKey(),
        ]);
    }

    public function forProduct(Product $product, ?ProductVariant $variant = null): static
    {
        return $this->state(function () use ($product, $variant) {
            $unit = '100.0000';
            $qty = 1.0;
            $sub = number_format(((float) $unit) * $qty, 4, '.', '');

            return [
                'product_id' => $product->getKey(),
                'variant_id' => $variant?->getKey(),
                'unit_price' => $unit,
                'quantity' => number_format($qty, 4, '.', ''),
                'subtotal' => $sub,
            ];
        });
    }
}
