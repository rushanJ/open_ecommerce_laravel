<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('VAR-#####')),
            'barcode' => null,
            'name' => $this->faker->words(2, true),
            'regular_price' => '150.0000',
            'sale_price' => null,
            'cost_price' => null,
            'stock_quantity' => '50.0000',
            'stock_status' => 'in_stock',
            'weight' => null,
            'image_path' => null,
            'status' => 'active',
            'metadata' => null,
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn () => [
            'product_id' => $product->getKey(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => 'inactive',
        ]);
    }
}
