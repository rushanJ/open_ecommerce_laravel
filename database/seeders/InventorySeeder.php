<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $main = Warehouse::query()->updateOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Warehouse',
                'address' => 'Main stock location',
                'status' => 'active',
            ]
        );

        $colombo = Warehouse::query()->updateOrCreate(
            ['code' => 'CMB'],
            [
                'name' => 'Colombo Store',
                'address' => 'Colombo retail stock location',
                'status' => 'active',
            ]
        );

        /** @var InventoryService $syncService */
        $syncService = app(InventoryService::class);

        Product::query()
            ->where('status', 'active')
            ->with(['categories', 'variants' => fn ($q) => $q->orderBy('id')])
            ->orderBy('id')
            ->each(function (Product $product) use ($main, $colombo, $syncService): void {
                if ($product->variants->isNotEmpty()) {
                    foreach ($product->variants as $variant) {
                        $this->seedStockAndMovement($main, $product, $variant, $this->qtyMain($product, $variant, 'main'));
                        $this->seedStockAndMovement($colombo, $product, $variant, $this->qtySecondary($product, $variant, 'cmb'));
                    }
                } else {
                    $this->seedStockAndMovement($main, $product, null, $this->qtyMain($product, null, 'main'));
                    $this->seedStockAndMovement($colombo, $product, null, $this->qtySecondary($product, null, 'cmb'));
                }

                $syncService->syncProductStockFromInventory($product->fresh(['variants']));
            });
    }

    private function seedStockAndMovement(Warehouse $warehouse, Product $product, $variant, int $targetQty): void
    {
        if ($targetQty < 1) {
            $targetQty = 1;
        }

        $variantId = $variant?->getKey();

        InventoryStock::query()->updateOrCreate(
            [
                'warehouse_id' => $warehouse->getKey(),
                'product_id' => $product->getKey(),
                'variant_id' => $variantId,
            ],
            [
                'quantity' => $targetQty,
                'reserved_quantity' => 0,
                'available_quantity' => $targetQty,
            ]
        );

        $movementExists = InventoryMovement::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('product_id', $product->getKey())
            ->when(
                $variantId !== null,
                fn ($q) => $q->where('variant_id', $variantId),
                fn ($q) => $q->whereNull('variant_id')
            )
            ->where('type', 'purchase')
            ->where('reference_type', 'seed')
            ->exists();

        if ($movementExists) {
            return;
        }

        $referenceId = $variantId !== null ? (int) $variantId : (int) $product->getKey();

        InventoryMovement::query()->create([
            'warehouse_id' => $warehouse->getKey(),
            'product_id' => $product->getKey(),
            'variant_id' => $variantId,
            'type' => 'purchase',
            'quantity' => $targetQty,
            'reference_type' => 'seed',
            'reference_id' => $referenceId,
            'note' => 'Initial seed stock',
            'admin_user_id' => null,
            'created_at' => now(),
        ]);
    }

    private function qtyMain(Product $product, $variant, string $suffix): int
    {
        [$min, $max] = $this->rangeForProduct($product);

        $key = $product->slug.($variant ? (string) $variant->sku : 'simple').$suffix;

        return $this->deterministicBetween($key, $min, $max);
    }

    private function qtySecondary(Product $product, $variant, string $suffix): int
    {
        $main = $this->qtyMain($product, $variant, 'main');

        return max(1, (int) floor($main * 0.45));
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function rangeForProduct(Product $product): array
    {
        $slugs = $product->categories->pluck('slug')->all();

        if (array_intersect($slugs, ['smartphones', 'laptops'])) {
            return [10, 25];
        }

        if (array_intersect($slugs, ['shoes', 't-shirts'])) {
            return [20, 60];
        }

        if (in_array('audio', $slugs, true)) {
            return [10, 30];
        }

        return [15, 35];
    }

    private function deterministicBetween(string $key, int $min, int $max): int
    {
        if ($min >= $max) {
            return $min;
        }

        $span = $max - $min + 1;
        $n = (int) (crc32($key) % $span + $span) % $span;

        return $min + $n;
    }
}
