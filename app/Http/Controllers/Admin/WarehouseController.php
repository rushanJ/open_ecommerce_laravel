<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Http\Requests\Admin\UpdateWarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarehouseController extends BaseAdminController
{
    public function index(Request $request): View
    {
        $query = Warehouse::query()->orderByDesc('id');

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('code', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $warehouses = $query->paginate(15)->withQueryString();

        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('admin.warehouses.create', ['warehouse' => new Warehouse]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = $this->resolveWarehouseCode($data['name'], $data['code'] ?? null, null);

        $warehouse = Warehouse::query()->create($data);
        $this->logAdminActivity('shipping', 'create_warehouse', $warehouse, [], $warehouse->only(['code', 'name', 'status']));

        return $this->success(__('admin.warehouse_created'), 'admin.warehouses.index');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = $this->resolveWarehouseCode($data['name'], $data['code'] ?? null, $warehouse->getKey());

        $before = $warehouse->only(['code', 'name', 'status']);
        $warehouse->update($data);
        $updated = $warehouse->fresh();
        $this->logAdminActivity('shipping', 'update_warehouse', $updated, $before, $updated?->only(['code', 'name', 'status']) ?? []);

        return $this->success(__('admin.warehouse_updated'), 'admin.warehouses.index');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $hasPositive = $warehouse->stocks()->where('quantity', '>', 0)->exists();
        if ($hasPositive) {
            return $this->error(__('admin.warehouse_delete_blocked'), 'admin.warehouses.index');
        }

        $snapshot = $warehouse->only(['id', 'code', 'name']);
        $warehouse->delete();
        $this->logAdminActivity('shipping', 'delete_warehouse', null, $snapshot, []);

        return $this->success(__('admin.warehouse_deleted'), 'admin.warehouses.index');
    }

    private function resolveWarehouseCode(string $name, ?string $code, ?int $ignoreId): string
    {
        if ($code !== null && trim($code) !== '') {
            return strtoupper(trim($code));
        }

        $base = strtoupper((string) preg_replace('/[^a-zA-Z0-9]/', '', Str::slug($name, '')));
        if ($base === '') {
            $base = 'WH';
        }

        $candidate = substr($base, 0, 10);
        $n = 1;

        while (Warehouse::query()
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('code', $candidate)
            ->exists()) {
            $suffix = (string) $n;
            $candidate = substr($base, 0, max(1, 10 - strlen($suffix))).$suffix;
            $n++;
        }

        return $candidate;
    }
}
