<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Cms\BannerService;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class HomeController extends Controller
{
    public function __construct(
        protected BannerService $bannerService,
    ) {}

    public function index(): View
    {
        $parentCategories = Category::query()
            ->active()
            ->whereNull('parent_id')
            ->withCount([
                'activeChildren',
                'products as storefront_products_count' => fn (Builder $q) => $q->forStorefront(),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $brands = Brand::query()
            ->active()
            ->withCount(['products as storefront_products_count' => fn (Builder $q) => $q->forStorefront()])
            ->having('storefront_products_count', '>', 0)
            ->orderBy('name')
            ->limit(12)
            ->get();

        $featuredProducts = Product::query()
            ->forStorefront()
            ->featured()
            ->with([
                'brand',
                'categories',
                'primaryImage',
                'variants' => fn ($q) => $q->active()->select(['id', 'product_id', 'regular_price', 'sale_price', 'stock_status']),
            ])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $latestProducts = Product::query()
            ->forStorefront()
            ->with([
                'brand',
                'categories',
                'primaryImage',
                'variants' => fn ($q) => $q->active()->select(['id', 'product_id', 'regular_price', 'sale_price', 'stock_status']),
            ])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $heroBanners = $this->bannerService->getActiveBanners('home_hero', 3);

        return view('customer.home.index', compact('parentCategories', 'brands', 'featuredProducts', 'latestProducts', 'heroBanners'));
    }
}
