<?php

namespace Database\Factories;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryStock>
 */
class InventoryStockFactory extends Factory
{
    protected $model = InventoryStock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = '100.0000';

        return [
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'quantity' => $qty,
            'reserved_quantity' => '0.0000',
            'available_quantity' => $qty,
        ];
    }

    public function forWarehouseProduct(Warehouse $warehouse, Product $product, ?ProductVariant $variant = null): static
    {
        return $this->state(function () use ($warehouse, $product, $variant) {
            $qty = '100.0000';

            return [
                'warehouse_id' => $warehouse->getKey(),
                'product_id' => $product->getKey(),
                'variant_id' => $variant?->getKey(),
                'quantity' => $qty,
                'reserved_quantity' => '0.0000',
                'available_quantity' => $qty,
            ];
        });
    }
}
