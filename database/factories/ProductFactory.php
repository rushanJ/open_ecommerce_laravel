<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(4, true);

        return [
            'brand_id' => null,
            'product_type' => 'simple',
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'barcode' => null,
            'short_description' => null,
            'description' => null,
            'status' => 'draft',
            'visibility' => 'visible',
            'is_featured' => false,
            'regular_price' => '100.0000',
            'sale_price' => null,
            'cost_price' => null,
            'tax_class_id' => null,
            'manage_stock' => false,
            'stock_quantity' => null,
            'low_stock_threshold' => null,
            'stock_status' => 'in_stock',
            'backorders_allowed' => false,
            'weight' => null,
            'length' => null,
            'width' => null,
            'height' => null,
            'digital_file_path' => null,
            'meta_title' => null,
            'meta_description' => null,
            'published_at' => null,
            'metadata' => null,
        ];
    }

    /**
     * Visible on the storefront (matches {@see Product::scopeForStorefront()}).
     */
    public function activeStorefront(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'visibility' => 'visible',
            'published_at' => now()->subDay(),
            'stock_status' => 'in_stock',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => 'inactive',
        ]);
    }

    public function variable(): static
    {
        return $this->state(fn () => [
            'product_type' => 'variable',
            'manage_stock' => false,
            'stock_quantity' => null,
        ]);
    }
}
