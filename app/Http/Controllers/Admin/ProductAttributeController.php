<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductAttributeRequest;
use App\Http\Requests\Admin\UpdateProductAttributeRequest;
use App\Models\ProductAttribute;
use App\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductAttributeController extends BaseAdminController
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    public function index(Request $request): View
    {
        $query = ProductAttribute::query()
            ->withCount('values')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('slug', 'like', '%'.$term.'%');
            });
        }

        $attributes = $query->paginate(15)->withQueryString();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create(): View
    {
        return view('admin.attributes.create', ['attribute' => new ProductAttribute]);
    }

    public function store(StoreProductAttributeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($data['slug'] === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->unique('product_attributes', 'slug', $data['name']);
        }

        ProductAttribute::query()->create($data);

        return $this->success(__('admin.attribute_created'), 'admin.attributes.index');
    }

    public function edit(ProductAttribute $attribute): View
    {
        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(UpdateProductAttributeRequest $request, ProductAttribute $attribute): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($data['slug'] === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->unique('product_attributes', 'slug', $data['name'], $attribute->getKey());
        }

        $attribute->update($data);

        return $this->success(__('admin.attribute_updated'), 'admin.attributes.index');
    }

    public function destroy(ProductAttribute $attribute): RedirectResponse
    {
        if ($attribute->values()->exists() || $attribute->assignments()->exists()) {
            return $this->backWithError(__('admin.attribute_delete_blocked'));
        }

        $attribute->delete();

        return $this->success(__('admin.attribute_deleted'), 'admin.attributes.index');
    }
}
