<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Customer\Concerns\FiltersStorefrontProducts;
use App\Http\Resources\Api\V1\ProductDetailResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    use FiltersStorefrontProducts;

    public function index(Request $request): JsonResponse
    {
        $perPage = min(50, max(1, (int) $request->input('per_page', 15)));

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

        $paginator = $query->paginate($perPage)->withQueryString();

        return $this->paginated($paginator, ProductResource::class);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->forStorefront()
            ->where('slug', $slug)
            ->firstOrFail();

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
            ->with(['brand', 'categories', 'primaryImage', 'variants' => fn ($vq) => $vq->active()->select(['id', 'product_id', 'regular_price', 'sale_price', 'stock_status'])])
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $product->setRelation('relatedProducts', $relatedProducts);

        return $this->success((new ProductDetailResource($product))->resolve());
    }
}
