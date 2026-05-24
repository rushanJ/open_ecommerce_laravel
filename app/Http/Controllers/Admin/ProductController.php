<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\TaxClass;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends BaseAdminController
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $query = Product::query()
            ->with([
                'brand',
                'categories',
                'primaryImage',
                'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('slug', 'like', '%'.$term.'%')
                    ->orWhere('sku', 'like', '%'.$term.'%')
                    ->orWhere('barcode', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->input('product_type'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request): void {
                $q->where('categories.id', $request->input('category_id'));
            });
        }

        $products = $query->paginate(15)->withQueryString();

        $brands = Brand::query()->active()->orderBy('name')->get();
        $categories = Category::query()->active()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'brands', 'categories'));
    }

    public function create(): View
    {
        $product = new Product;
        $variantSlots = $this->buildVariantSlots($product);

        return view('admin.products.create', array_merge(
            compact('product', 'variantSlots'),
            $this->formOptions()
        ));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->productService->createProduct($request->validated());
        $this->logAdminActivity('products', 'create', $product, [], $product->only(['name', 'slug', 'sku', 'status']));

        return $this->success(__('admin.product_created'), 'admin.products.index');
    }

    public function edit(Product $product): View
    {
        $product->load([
            'categories',
            'images' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'attributeAssignments',
            'variants.values',
        ]);

        $variantSlots = $this->buildVariantSlots($product);

        return view('admin.products.edit', array_merge(
            compact('product', 'variantSlots'),
            $this->formOptions()
        ));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $before = $product->only(['name', 'slug', 'sku', 'status']);
        $updated = $this->productService->updateProduct($product, $request->validated());
        $this->logAdminActivity('products', 'update', $updated, $before, $updated->only(['name', 'slug', 'sku', 'status']));

        return $this->success(__('admin.product_updated'), 'admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $snapshot = $product->only(['id', 'name', 'slug', 'sku']);
        $this->productService->deleteProduct($product);
        $this->logAdminActivity('products', 'delete', null, $snapshot, []);

        return $this->success(__('admin.product_deleted'), 'admin.products.index');
    }

    public function destroyImage(Product $product, ProductImage $productImage): RedirectResponse
    {
        try {
            $this->productService->deleteProductImage($product, $productImage);
        } catch (\RuntimeException $e) {
            return $this->backWithError($e->getMessage());
        }

        $this->logAdminActivity('products', 'delete_image', $product, [
            'image_id' => $productImage->getKey(),
            'path' => $productImage->path,
        ], []);

        return $this->backWithSuccess(__('admin.product_image_deleted'));
    }

    /**
     * @return array{productAttributes: \Illuminate\Database\Eloquent\Collection<int, ProductAttribute>, brands: \Illuminate\Database\Eloquent\Collection, categories: \Illuminate\Database\Eloquent\Collection, taxClasses: \Illuminate\Database\Eloquent\Collection, statusOptions: array<string, string>, visibilityOptions: array<string, string>, stockStatusOptions: array<string, string>, productTypeOptions: array<string, string>}
     */
    private function formOptions(): array
    {
        return [
            'productAttributes' => ProductAttribute::query()->with('values')->orderBy('sort_order')->orderBy('name')->get(),
            'brands' => Brand::query()->active()->orderBy('name')->get(),
            'categories' => Category::query()->active()->orderBy('name')->get(),
            'taxClasses' => TaxClass::query()->orderBy('name')->get(),
            'statusOptions' => [
                'draft' => __('admin.draft'),
                'active' => __('admin.active'),
                'inactive' => __('admin.inactive'),
                'archived' => __('admin.archived'),
            ],
            'visibilityOptions' => [
                'visible' => __('admin.visible'),
                'hidden' => __('admin.hidden'),
                'catalog' => __('admin.catalog'),
                'search' => __('admin.visibility_search'),
            ],
            'stockStatusOptions' => [
                'in_stock' => __('admin.in_stock'),
                'out_of_stock' => __('admin.out_of_stock'),
                'on_backorder' => __('admin.on_backorder'),
            ],
            'productTypeOptions' => [
                'simple' => __('admin.simple'),
                'variable' => __('admin.variable'),
                'digital' => __('admin.digital'),
                'bundle' => __('admin.bundle'),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildVariantSlots(Product $product): array
    {
        $productAttributes = ProductAttribute::query()->orderBy('sort_order')->orderBy('name')->get();

        $rows = [];
        if ($product->exists) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $variants */
            $variants = $product->relationLoaded('variants')
                ? $product->variants->sortBy('id')->values()
                : $product->variants()->orderBy('id')->get();

            foreach ($variants as $variant) {
                $variant->loadMissing('values');
                $row = [
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                    'name' => $variant->name,
                    'regular_price' => $variant->regular_price,
                    'sale_price' => $variant->sale_price,
                    'cost_price' => $variant->cost_price,
                    'stock_quantity' => $variant->stock_quantity,
                    'stock_status' => $variant->stock_status,
                    'weight' => $variant->weight,
                    'image_path' => $variant->image_path,
                    'status' => $variant->status,
                    'attribute_values' => [],
                ];
                $byAttr = $variant->values->keyBy('attribute_id');
                foreach ($productAttributes as $ai => $attr) {
                    $pv = $byAttr->get($attr->id);
                    $row['attribute_values'][$ai] = [
                        'attribute_id' => $attr->id,
                        'attribute_value_id' => $pv?->attribute_value_id,
                    ];
                }
                $rows[] = $row;
            }
        }

        $emptyAttrValues = [];
        foreach ($productAttributes as $ai => $attr) {
            $emptyAttrValues[$ai] = [
                'attribute_id' => $attr->id,
                'attribute_value_id' => null,
            ];
        }

        while (count($rows) < 3) {
            $rows[] = [
                'sku' => null,
                'barcode' => null,
                'name' => null,
                'regular_price' => null,
                'sale_price' => null,
                'cost_price' => null,
                'stock_quantity' => null,
                'stock_status' => 'in_stock',
                'weight' => null,
                'image_path' => null,
                'status' => 'active',
                'attribute_values' => $emptyAttrValues,
            ];
        }

        return $rows;
    }
}
