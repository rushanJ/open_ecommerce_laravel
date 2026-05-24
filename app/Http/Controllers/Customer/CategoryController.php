<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\FiltersStorefrontProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use FiltersStorefrontProducts;

    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->whereNull('parent_id')
            ->with(['activeChildren' => fn ($q) => $q->withCount([
                'products as storefront_products_count' => fn ($pq) => $pq->forStorefront(),
            ])])
            ->withCount([
                'products as storefront_products_count' => fn ($pq) => $pq->forStorefront(),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('customer.categories.index', compact('categories'));
    }

    public function show(Request $request, Category $category): View
    {
        $familyIds = $this->categoryFamilyIds($category);

        $query = Product::query()
            ->forStorefront()
            ->with([
                'brand',
                'categories',
                'primaryImage',
                'variants' => fn ($q) => $q->active()->select(['id', 'product_id', 'regular_price', 'sale_price', 'stock_status']),
            ]);

        $this->applyStorefrontProductFilters($query, $request, $familyIds, null);
        $this->applyStorefrontSort($query, $request);

        $products = $query->paginate(12)->withQueryString();

        $filterCategories = Category::query()->active()->orderBy('name')->get();
        $filterBrands = Brand::query()->active()->orderBy('name')->get();

        return view('customer.categories.show', compact('category', 'products', 'filterCategories', 'filterBrands', 'familyIds'));
    }
}
