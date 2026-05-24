<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\FiltersStorefrontProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use FiltersStorefrontProducts;

    public function index(Request $request): View
    {
        $query = Product::query()
            ->forStorefront()
            ->with([
                'brand',
                'categories',
                'primaryImage',
                'variants' => fn ($q) => $q->active()->select(['id', 'product_id', 'regular_price', 'sale_price', 'stock_status']),
            ]);

        $this->applyStorefrontProductFilters($query, $request, null, null);
        $this->applyStorefrontSort($query, $request);

        $products = $query->paginate(12)->withQueryString();

        $filterCategories = Category::query()->active()->orderBy('name')->get();
        $filterBrands = Brand::query()->active()->orderBy('name')->get();

        return view('customer.products.index', compact('products', 'filterCategories', 'filterBrands'));
    }

    public function show(Product $product): View
    {
        $product->load([
            'brand',
            'categories',
            'images' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'primaryImage',
            'variants' => fn ($q) => $q->active()->orderBy('id')->with(['values.attribute', 'values.value']),
            'attributeAssignments' => fn ($q) => $q->with(['attribute', 'value']),
        ]);

        $categoryIds = $product->categories->pluck('id')->all();

        $relatedProducts = Product::query()
            ->forStorefront()
            ->where('id', '!=', $product->getKey())
            ->when($categoryIds !== [], function ($q) use ($categoryIds): void {
                $q->whereHas('categories', function ($cq) use ($categoryIds): void {
                    $cq->whereIn('categories.id', $categoryIds);
                });
            })
            ->with(['brand', 'primaryImage', 'variants' => fn ($vq) => $vq->active()->select(['id', 'product_id', 'regular_price', 'sale_price', 'stock_status'])])
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        return view('customer.products.show', compact('product', 'relatedProducts'));
    }
}
