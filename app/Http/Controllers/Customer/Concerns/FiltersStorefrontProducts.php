<?php

namespace App\Http\Controllers\Customer\Concerns;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersStorefrontProducts
{
    /**
     * @param  Builder<Product>  $query
     * @param  list<int>|null  $categoryIds If set, restrict to products in any of these categories.
     */
    protected function applyStorefrontProductFilters(Builder $query, Request $request, ?array $categoryIds = null, ?int $brandId = null): Builder
    {
        if ($categoryIds !== null && $categoryIds !== []) {
            $ids = array_values(array_unique(array_filter($categoryIds)));
            $query->whereHas('categories', function (Builder $q) use ($ids): void {
                $q->whereIn('categories.id', $ids);
            });
        }

        if ($brandId !== null) {
            $query->where('brand_id', $brandId);
        }

        if ($request->filled('q')) {
            $query->search($request->string('q')->toString());
        }

        if ($request->filled('category') && $categoryIds === null) {
            $query->whereHas('categories', function (Builder $q) use ($request): void {
                $q->where('categories.id', $request->input('category'));
            });
        }

        if ($request->filled('brand') && $brandId === null) {
            $query->where('brand_id', $request->input('brand'));
        }

        if ($request->filled('min_price')) {
            $query->where('regular_price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('regular_price', '<=', $request->input('max_price'));
        }

        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->input('stock_status'));
        }

        return $query;
    }

    /**
     * @param  Builder<Product>  $query
     */
    protected function applyStorefrontSort(Builder $query, Request $request): void
    {
        match ($request->input('sort')) {
            'price_asc' => $query->orderBy('regular_price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('regular_price')->orderByDesc('id'),
            'name' => $query->orderBy('name')->orderBy('id'),
            default => $query->orderByDesc('id'),
        };
    }

    /**
     * @return list<int>
     */
    protected function categoryFamilyIds(\App\Models\Category $category): array
    {
        return array_values(array_unique(array_merge(
            [$category->getKey()],
            \App\Models\Category::descendantIdsFor($category)
        )));
    }
}
