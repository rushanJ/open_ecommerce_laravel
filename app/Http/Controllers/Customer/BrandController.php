<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\FiltersStorefrontProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use FiltersStorefrontProducts;

    public function index(): View
    {
        $brands = Brand::query()
            ->active()
            ->withCount(['products as storefront_products_count' => fn ($q) => $q->forStorefront()])
            ->having('storefront_products_count', '>', 0)
            ->orderBy('name')
            ->get();

        return view('customer.brands.index', compact('brands'));
    }

    public function show(Request $request, Brand $brand): View
    {
        $query = Product::query()
            ->forStorefront()
            ->where('brand_id', $brand->getKey())
            ->with([
                'brand',
                'categories',
                'primaryImage',
                'variants' => fn ($q) => $q->active()->select(['id', 'product_id', 'regular_price', 'sale_price', 'stock_status']),
            ]);

        $this->applyStorefrontProductFilters($query, $request, null, $brand->getKey());
        $this->applyStorefrontSort($query, $request);

        $products = $query->paginate(12)->withQueryString();

        $filterCategories = Category::query()->active()->orderBy('name')->get();
        $filterBrands = Brand::query()->active()->orderBy('name')->get();

        return view('customer.brands.show', compact('brand', 'products', 'filterCategories', 'filterBrands'));
    }
}
