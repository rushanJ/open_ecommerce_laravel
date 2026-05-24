<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'variant_id' => null,
            'path' => 'products/test-'.$this->faker->uuid().'.jpg',
            'alt_text' => $this->faker->optional()->words(3, true),
            'sort_order' => 0,
            'is_primary' => true,
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn () => [
            'product_id' => $product->getKey(),
            'variant_id' => null,
        ]);
    }

    public function forVariant(Product $product, ProductVariant $variant): static
    {
        return $this->state(fn () => [
            'product_id' => $product->getKey(),
            'variant_id' => $variant->getKey(),
        ]);
    }
}
