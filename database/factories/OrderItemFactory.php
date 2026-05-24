<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'product_name' => 'Test product',
            'sku' => 'SKU-TEST',
            'quantity' => '1.0000',
            'unit_price' => '100.0000',
            'subtotal' => '100.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'total' => '100.0000',
            'metadata' => null,
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn () => [
            'order_id' => $order->getKey(),
        ]);
    }

    public function fromProduct(Order $order, Product $product, ?ProductVariant $variant = null, float $qty = 1.0): static
    {
        return $this->state(function () use ($order, $product, $variant, $qty) {
            $unit = $variant !== null
                ? (float) ($variant->sale_price ?: $variant->regular_price)
                : (float) ($product->sale_price ?: $product->regular_price);
            $unitStr = number_format($unit, 4, '.', '');
            $sub = number_format($unit * $qty, 4, '.', '');
            $tax = '0.0000';
            $total = number_format(((float) $sub) + (float) $tax, 4, '.', '');

            return [
                'order_id' => $order->getKey(),
                'product_id' => $product->getKey(),
                'variant_id' => $variant?->getKey(),
                'product_name' => $product->name,
                'sku' => $variant?->sku ?? ($product->sku ?: null),
                'quantity' => number_format($qty, 4, '.', ''),
                'unit_price' => $unitStr,
                'subtotal' => $sub,
                'discount_total' => '0.0000',
                'tax_total' => $tax,
                'total' => $total,
            ];
        });
    }
}
