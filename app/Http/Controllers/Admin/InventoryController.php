<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdjustInventoryRequest;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryController extends BaseAdminController
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $query = InventoryStock::query()
            ->with(['warehouse', 'product', 'variant'])
            ->orderByDesc('updated_at');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('stock_status')) {
            $query->whereHas('product', function ($q) use ($request): void {
                $q->where('stock_status', $request->input('stock_status'));
            });
        }

        if ($request->boolean('low_stock')) {
            $query->whereExists(function ($sub): void {
                $sub->select(DB::raw(1))
                    ->from('products')
                    ->whereColumn('products.id', 'inventory_stocks.product_id')
                    ->where('products.manage_stock', true)
                    ->whereNotNull('products.low_stock_threshold')
                    ->whereColumn('inventory_stocks.quantity', '<=', 'products.low_stock_threshold');
            });
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term): void {
                $q->whereHas('product', function ($pq) use ($term): void {
                    $pq->where('name', 'like', '%'.$term.'%')
                        ->orWhere('sku', 'like', '%'.$term.'%');
                })
                    ->orWhereHas('variant', function ($vq) use ($term): void {
                        $vq->where('sku', 'like', '%'.$term.'%')
                            ->orWhere('name', 'like', '%'.$term.'%');
                    });
            });
        }

        $stocks = $query->paginate(20)->withQueryString();

        $warehouses = Warehouse::query()->where('status', 'active')->orderBy('name')->get();
        $products = Product::query()->where('status', 'active')->orderBy('name')->get();

        return view('admin.inventory.index', compact('stocks', 'warehouses', 'products'));
    }

    public function movements(Request $request): View
    {
        $query = InventoryMovement::query()
            ->with(['warehouse', 'product', 'variant', 'adminUser'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $movements = $query->paginate(20)->withQueryString();

        $warehouses = Warehouse::query()->where('status', 'active')->orderBy('name')->get();
        $products = Product::query()->where('status', 'active')->orderBy('name')->get();

        return view('admin.inventory.movements', compact('movements', 'warehouses', 'products'));
    }

    public function adjustForm(): View
    {
        $warehouses = Warehouse::query()->where('status', 'active')->orderBy('name')->get();
        $products = Product::query()
            ->where('status', 'active')
            ->with(['variants' => fn ($q) => $q->orderBy('id')])
            ->orderBy('name')
            ->get();

        return view('admin.inventory.adjust', compact('warehouses', 'products'));
    }

    public function adjust(AdjustInventoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $warehouse = Warehouse::query()->findOrFail($data['warehouse_id']);
        $product = Product::query()->findOrFail($data['product_id']);
        $variant = isset($data['variant_id']) && $data['variant_id'] !== ''
            ? ProductVariant::query()->findOrFail((int) $data['variant_id'])
            : null;

        $adminId = $request->user('admin')?->getKey();

        try {
            $quantity = (float) $data['quantity'];
            if (($data['type'] === 'return' || $data['type'] === 'purchase') && $quantity < 0) {
                return $this->backWithError(__('admin.error_generic'));
            }

            $this->inventoryService->adjustStock(
                $warehouse,
                $product,
                $variant,
                $quantity,
                $data['type'],
                $data['note'] ?? null,
                $adminId !== null ? (int) $adminId : null,
                null,
                null,
            );
        } catch (InvalidArgumentException $e) {
            return $this->backWithError($e->getMessage());
        }

        return $this->success(__('admin.stock_adjusted'), 'admin.inventory.index');
    }
}
