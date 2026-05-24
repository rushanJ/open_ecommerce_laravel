<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductAttributeValueRequest;
use App\Http\Requests\Admin\UpdateProductAttributeValueRequest;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeAssignment;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariantValue;
use App\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductAttributeValueController extends BaseAdminController
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    public function index(Request $request, ProductAttribute $attribute): View
    {
        $query = $attribute->values()->orderBy('sort_order')->orderBy('value');

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term): void {
                $q->where('value', 'like', '%'.$term.'%')
                    ->orWhere('slug', 'like', '%'.$term.'%');
            });
        }

        $values = $query->paginate(15)->withQueryString();

        return view('admin.attributes.values.index', compact('attribute', 'values'));
    }

    public function create(ProductAttribute $attribute): View
    {
        return view('admin.attributes.values.create', [
            'attribute' => $attribute,
            'value' => new ProductAttributeValue,
        ]);
    }

    public function store(StoreProductAttributeValueRequest $request, ProductAttribute $attribute): RedirectResponse
    {
        $data = $request->validated();
        $data['attribute_id'] = $attribute->getKey();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($data['slug'] === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->uniqueScoped(
                'product_attribute_values',
                'slug',
                $data['value'],
                ['attribute_id' => $attribute->getKey()]
            );
        }

        ProductAttributeValue::query()->create($data);

        return redirect()->route('admin.attributes.values.index', $attribute)->with('success', __('admin.attribute_value_created'));
    }

    public function edit(ProductAttribute $attribute, ProductAttributeValue $value): View
    {
        return view('admin.attributes.values.edit', compact('attribute', 'value'));
    }

    public function update(UpdateProductAttributeValueRequest $request, ProductAttribute $attribute, ProductAttributeValue $value): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($data['slug'] === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->uniqueScoped(
                'product_attribute_values',
                'slug',
                $data['value'],
                ['attribute_id' => $attribute->getKey()],
                $value->getKey()
            );
        }

        $value->update($data);

        return redirect()->route('admin.attributes.values.index', $attribute)->with('success', __('admin.attribute_value_updated'));
    }

    public function destroy(ProductAttribute $attribute, ProductAttributeValue $value): RedirectResponse
    {
        if (
            ProductVariantValue::query()->where('attribute_value_id', $value->getKey())->exists()
            || ProductAttributeAssignment::query()->where('attribute_value_id', $value->getKey())->exists()
        ) {
            return $this->backWithError(__('admin.attribute_value_delete_blocked'));
        }

        $value->delete();

        return redirect()->route('admin.attributes.values.index', $attribute)->with('success', __('admin.attribute_value_deleted'));
    }
}
