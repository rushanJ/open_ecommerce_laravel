<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function __construct(
        protected WebhookService $webhooks,
    ) {}

    public function ensureStockRecord(Warehouse $warehouse, Product $product, ?ProductVariant $variant = null): InventoryStock
    {
        if ($variant !== null && (int) $variant->product_id !== (int) $product->getKey()) {
            throw new InvalidArgumentException('Variant does not belong to the given product.');
        }

        $query = InventoryStock::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('product_id', $product->getKey());

        if ($variant !== null) {
            $query->where('variant_id', $variant->getKey());
        } else {
            $query->whereNull('variant_id');
        }

        $existing = $query->first();
        if ($existing !== null) {
            if ($variant === null) {
                $dup = InventoryStock::query()
                    ->where('warehouse_id', $warehouse->getKey())
                    ->where('product_id', $product->getKey())
                    ->whereNull('variant_id')
                    ->where('id', '!=', $existing->getKey())
                    ->exists();
                if ($dup) {
                    throw new InvalidArgumentException('Duplicate simple product stock row for this warehouse.');
                }
            }

            return $existing;
        }

        return InventoryStock::query()->create([
            'warehouse_id' => $warehouse->getKey(),
            'product_id' => $product->getKey(),
            'variant_id' => $variant?->getKey(),
            'quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0,
        ]);
    }

    /**
     * @param  'purchase'|'sale'|'return'|'adjustment'|'reservation'|'release'  $type
     */
    public function adjustStock(
        Warehouse $warehouse,
        Product $product,
        ?ProductVariant $variant,
        float $quantity,
        string $type,
        ?string $note = null,
        ?int $adminUserId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): InventoryStock {
        $allowed = ['purchase', 'sale', 'return', 'adjustment', 'reservation', 'release'];

        if (! in_array($type, $allowed, true)) {
            throw new InvalidArgumentException('Invalid inventory movement type.');
        }

        if ($variant !== null && (int) $variant->product_id !== (int) $product->getKey()) {
            throw new InvalidArgumentException('Variant does not belong to the given product.');
        }

        $product->loadMissing('variants');

        return DB::transaction(function () use (
            $warehouse,
            $product,
            $variant,
            $quantity,
            $type,
            $note,
            $adminUserId,
            $referenceType,
            $referenceId,
        ): InventoryStock {
            $this->ensureStockRecord($warehouse, $product, $variant);
            $stockQuery = InventoryStock::query()
                ->where('warehouse_id', $warehouse->getKey())
                ->where('product_id', $product->getKey());

            if ($variant !== null) {
                $stockQuery->where('variant_id', $variant->getKey());
            } else {
                $stockQuery->whereNull('variant_id');
            }

            /** @var InventoryStock $stock */
            $stock = $stockQuery->lockForUpdate()->firstOrFail();

            $delta = (float) $quantity;
            $oldQty = (float) $stock->quantity;
            $newQty = $oldQty + $delta;
            $reserved = (float) $stock->reserved_quantity;

            if ($type !== 'adjustment' && $newQty < 0) {
                throw new InvalidArgumentException('Insufficient stock for this operation.');
            }

            if ($newQty < $reserved) {
                throw new InvalidArgumentException('On-hand quantity cannot be less than reserved quantity.');
            }

            $stock->quantity = $newQty;
            $stock->available_quantity = $newQty - $reserved;
            $stock->save();

            InventoryMovement::query()->create([
                'warehouse_id' => $warehouse->getKey(),
                'product_id' => $product->getKey(),
                'variant_id' => $variant?->getKey(),
                'type' => $type,
                'quantity' => $delta,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'admin_user_id' => $adminUserId,
                'created_at' => now(),
            ]);

            $this->syncProductStockFromInventory($product->fresh());

            return $stock->fresh();
        });
    }

    public function reserveStock(
        Warehouse $warehouse,
        Product $product,
        ?ProductVariant $variant,
        float $quantity,
        ?string $note = null,
        ?int $adminUserId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): InventoryStock {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Reservation quantity must be positive.');
        }

        if ($variant !== null && (int) $variant->product_id !== (int) $product->getKey()) {
            throw new InvalidArgumentException('Variant does not belong to the given product.');
        }

        return DB::transaction(function () use (
            $warehouse,
            $product,
            $variant,
            $quantity,
            $note,
            $adminUserId,
            $referenceType,
            $referenceId,
        ): InventoryStock {
            $this->ensureStockRecord($warehouse, $product, $variant);
            $stockQuery = InventoryStock::query()
                ->where('warehouse_id', $warehouse->getKey())
                ->where('product_id', $product->getKey());
            if ($variant !== null) {
                $stockQuery->where('variant_id', $variant->getKey());
            } else {
                $stockQuery->whereNull('variant_id');
            }

            /** @var InventoryStock $stock */
            $stock = $stockQuery->lockForUpdate()->firstOrFail();

            $product->refresh();
            $available = (float) $stock->quantity - (float) $stock->reserved_quantity;

            if ($quantity > $available && ! $product->backorders_allowed) {
                throw new InvalidArgumentException('Cannot reserve more than available quantity.');
            }

            $stock->reserved_quantity = (float) $stock->reserved_quantity + $quantity;
            $stock->available_quantity = (float) $stock->quantity - (float) $stock->reserved_quantity;
            $stock->save();

            InventoryMovement::query()->create([
                'warehouse_id' => $warehouse->getKey(),
                'product_id' => $product->getKey(),
                'variant_id' => $variant?->getKey(),
                'type' => 'reservation',
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'admin_user_id' => $adminUserId,
                'created_at' => now(),
            ]);

            $this->syncProductStockFromInventory($product->fresh());

            return $stock->fresh();
        });
    }

    public function releaseStock(
        Warehouse $warehouse,
        Product $product,
        ?ProductVariant $variant,
        float $quantity,
        ?string $note = null,
        ?int $adminUserId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): InventoryStock {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Release quantity must be positive.');
        }

        if ($variant !== null && (int) $variant->product_id !== (int) $product->getKey()) {
            throw new InvalidArgumentException('Variant does not belong to the given product.');
        }

        return DB::transaction(function () use (
            $warehouse,
            $product,
            $variant,
            $quantity,
            $note,
            $adminUserId,
            $referenceType,
            $referenceId,
        ): InventoryStock {
            $this->ensureStockRecord($warehouse, $product, $variant);
            $stockQuery = InventoryStock::query()
                ->where('warehouse_id', $warehouse->getKey())
                ->where('product_id', $product->getKey());
            if ($variant !== null) {
                $stockQuery->where('variant_id', $variant->getKey());
            } else {
                $stockQuery->whereNull('variant_id');
            }

            /** @var InventoryStock $stock */
            $stock = $stockQuery->lockForUpdate()->firstOrFail();

            $reserved = (float) $stock->reserved_quantity;
            if ($quantity > $reserved) {
                throw new InvalidArgumentException('Cannot release more than reserved quantity.');
            }

            $stock->reserved_quantity = $reserved - $quantity;
            $stock->available_quantity = (float) $stock->quantity - (float) $stock->reserved_quantity;
            $stock->save();

            InventoryMovement::query()->create([
                'warehouse_id' => $warehouse->getKey(),
                'product_id' => $product->getKey(),
                'variant_id' => $variant?->getKey(),
                'type' => 'release',
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'admin_user_id' => $adminUserId,
                'created_at' => now(),
            ]);

            $this->syncProductStockFromInventory($product->fresh());

            return $stock->fresh();
        });
    }

    public function syncProductStockFromInventory(Product $product): void
    {
        $product->load(['variants']);

        if ($product->variants->isNotEmpty()) {
            foreach ($product->variants as $variant) {
                $sum = (float) InventoryStock::query()
                    ->where('product_id', $product->getKey())
                    ->where('variant_id', $variant->getKey())
                    ->sum('quantity');

                $variant->stock_quantity = $sum;
                $variant->stock_status = $this->resolveStockStatus($sum, (bool) $product->backorders_allowed);
                $variant->save();
            }

            $total = (float) $product->variants->sum(fn ($v) => (float) $v->stock_quantity);
            $product->stock_quantity = $total;
        } else {
            $sum = (float) InventoryStock::query()
                ->where('product_id', $product->getKey())
                ->whereNull('variant_id')
                ->sum('quantity');

            $product->stock_quantity = $sum;
        }

        $product->stock_status = $this->resolveStockStatus((float) $product->stock_quantity, (bool) $product->backorders_allowed);
        $product->save();

        $this->maybeDispatchLowStockWebhooks($product->fresh(['variants']));
    }

    private function maybeDispatchLowStockWebhooks(Product $product): void
    {
        if (! $product->manage_stock) {
            return;
        }

        $threshold = (float) ($product->low_stock_threshold ?? 0);
        if ($threshold <= 0) {
            return;
        }

        try {
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    $qty = (float) ($variant->stock_quantity ?? 0);
                    if ($qty <= $threshold) {
                        $this->webhooks->dispatchSafe('inventory.low_stock', [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'sku' => $variant->sku,
                            'stock_quantity' => $qty,
                            'low_stock_threshold' => $threshold,
                        ]);
                    }
                }
            } else {
                $qty = (float) ($product->stock_quantity ?? 0);
                if ($qty <= $threshold) {
                    $this->webhooks->dispatchSafe('inventory.low_stock', [
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'sku' => $product->sku,
                        'stock_quantity' => $qty,
                        'low_stock_threshold' => $threshold,
                    ]);
                }
            }
        } catch (\Throwable) {
            // Never break inventory sync
        }
    }

    private function resolveStockStatus(float $qty, bool $backordersAllowed): string
    {
        if ($qty > 0) {
            return 'in_stock';
        }

        if ($backordersAllowed) {
            return 'on_backorder';
        }

        return 'out_of_stock';
    }

    /**
     * Convert reserved inventory into a sale movement for an order.
     * Idempotent: if a sale movement already exists for the order, does nothing.
     */
    public function confirmSaleForOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $already = InventoryMovement::query()
                ->where('reference_type', 'order')
                ->where('reference_id', $order->getKey())
                ->where('type', 'sale')
                ->exists();

            if ($already) {
                return;
            }

            $order->loadMissing(['items']);

            foreach ($order->items as $item) {
                if ($item->product_id === null) {
                    continue;
                }

                $product = Product::query()->find($item->product_id);
                if ($product === null) {
                    continue;
                }

                $variant = $item->variant_id ? ProductVariant::query()->find($item->variant_id) : null;
                $remaining = (float) $item->quantity;

                $stocks = InventoryStock::query()
                    ->where('product_id', $product->getKey())
                    ->when(
                        $variant !== null,
                        fn ($q) => $q->where('variant_id', $variant->getKey()),
                        fn ($q) => $q->whereNull('variant_id'),
                    )
                    ->orderByDesc('reserved_quantity')
                    ->lockForUpdate()
                    ->get();

                foreach ($stocks as $stock) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $reserved = (float) $stock->reserved_quantity;
                    if ($reserved <= 0) {
                        continue;
                    }

                    $take = min($remaining, $reserved);

                    $newReserved = $reserved - $take;
                    $newQty = (float) $stock->quantity - $take;

                    if ($newReserved < 0) {
                        throw new InvalidArgumentException('Reserved quantity cannot be negative.');
                    }
                    if ($newQty < 0 && ! $product->backorders_allowed) {
                        throw new InvalidArgumentException('Insufficient stock to confirm sale.');
                    }

                    $stock->reserved_quantity = $newReserved;
                    $stock->quantity = $newQty;
                    $stock->available_quantity = $newQty - $newReserved;
                    $stock->save();

                    InventoryMovement::query()->create([
                        'warehouse_id' => $stock->warehouse_id,
                        'product_id' => $product->getKey(),
                        'variant_id' => $variant?->getKey(),
                        'type' => 'sale',
                        'quantity' => -$take,
                        'reference_type' => 'order',
                        'reference_id' => $order->getKey(),
                        'note' => 'Sale confirmed for '.$order->order_number,
                        'admin_user_id' => null,
                        'created_at' => now(),
                    ]);

                    $remaining -= $take;
                }

                if ($remaining > 0 && ! $product->backorders_allowed) {
                    throw new InvalidArgumentException('Could not confirm sale for full quantity.');
                }

                $this->syncProductStockFromInventory($product->fresh());
            }
        });
    }

    /**
     * Release inventory reservations for an order.
     * Idempotent: if a release movement already exists for the order, does nothing.
     */
    public function releaseReservationForOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $already = InventoryMovement::query()
                ->where('reference_type', 'order')
                ->where('reference_id', $order->getKey())
                ->where('type', 'release')
                ->exists();

            if ($already) {
                return;
            }

            $order->loadMissing(['items']);

            foreach ($order->items as $item) {
                if ($item->product_id === null) {
                    continue;
                }

                $product = Product::query()->find($item->product_id);
                if ($product === null) {
                    continue;
                }

                $variant = $item->variant_id ? ProductVariant::query()->find($item->variant_id) : null;
                $remaining = (float) $item->quantity;

                $stocks = InventoryStock::query()
                    ->where('product_id', $product->getKey())
                    ->when(
                        $variant !== null,
                        fn ($q) => $q->where('variant_id', $variant->getKey()),
                        fn ($q) => $q->whereNull('variant_id'),
                    )
                    ->orderByDesc('reserved_quantity')
                    ->lockForUpdate()
                    ->get();

                foreach ($stocks as $stock) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $reserved = (float) $stock->reserved_quantity;
                    if ($reserved <= 0) {
                        continue;
                    }

                    $take = min($remaining, $reserved);
                    $newReserved = $reserved - $take;
                    if ($newReserved < 0) {
                        throw new InvalidArgumentException('Reserved quantity cannot be negative.');
                    }

                    $stock->reserved_quantity = $newReserved;
                    $stock->available_quantity = (float) $stock->quantity - $newReserved;
                    $stock->save();

                    InventoryMovement::query()->create([
                        'warehouse_id' => $stock->warehouse_id,
                        'product_id' => $product->getKey(),
                        'variant_id' => $variant?->getKey(),
                        'type' => 'release',
                        'quantity' => $take,
                        'reference_type' => 'order',
                        'reference_id' => $order->getKey(),
                        'note' => 'Reservation released for '.$order->order_number,
                        'admin_user_id' => null,
                        'created_at' => now(),
                    ]);

                    $remaining -= $take;
                }

                $this->syncProductStockFromInventory($product->fresh());
            }
        });
    }
}
