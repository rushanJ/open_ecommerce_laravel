<?php

namespace App\Services\ImportExport;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportService
{
    /** @var list<string> */
    public const HEADERS = [
        'sku',
        'product_type',
        'name',
        'slug',
        'brand',
        'categories',
        'short_description',
        'description',
        'status',
        'visibility',
        'regular_price',
        'sale_price',
        'cost_price',
        'stock_quantity',
        'stock_status',
        'manage_stock',
        'weight',
        'length',
        'width',
        'height',
        'tax_class',
        'meta_title',
        'meta_description',
        'variant_sku',
        'variant_name',
        'variant_regular_price',
        'variant_sale_price',
        'variant_stock_quantity',
        'variant_stock_status',
    ];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportProducts(array $filters = []): StreamedResponse
    {
        $filename = 'products-export-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::HEADERS);

            $this->queryWithFilters($filters)
                ->with(['brand', 'categories', 'taxClass', 'variants'])
                ->orderBy('id')
                ->chunkById(200, function ($products) use ($out): void {
                    foreach ($products as $product) {
                        if ($product->product_type === 'simple') {
                            fputcsv($out, $this->simpleRow($product));
                        } else {
                            $variants = $product->variants;
                            if ($variants->isEmpty()) {
                                continue;
                            }
                            foreach ($variants as $variant) {
                                fputcsv($out, $this->variableRow($product, $variant));
                            }
                        }
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Product>
     */
    protected function queryWithFilters(array $filters): Builder
    {
        $query = Product::query();

        if (! empty($filters['q'])) {
            $term = (string) $filters['q'];
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('slug', 'like', '%'.$term.'%')
                    ->orWhere('sku', 'like', '%'.$term.'%')
                    ->orWhere('barcode', 'like', '%'.$term.'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['product_type'])) {
            $query->where('product_type', $filters['product_type']);
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters): void {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        return $query;
    }

    /**
     * @return list<string|int|float|null>
     */
    protected function simpleRow(Product $product): array
    {
        return [
            $product->sku ?? '',
            $product->product_type,
            $product->name,
            $product->slug,
            $product->brand?->name ?? '',
            $product->relationLoaded('categories')
                ? $product->categories->pluck('name')->implode('|')
                : '',
            $product->short_description ?? '',
            $product->description ?? '',
            $product->status,
            $product->visibility,
            (string) $product->regular_price,
            $product->sale_price !== null ? (string) $product->sale_price : '',
            $product->cost_price !== null ? (string) $product->cost_price : '',
            $product->stock_quantity !== null ? (string) $product->stock_quantity : '',
            $product->stock_status,
            $product->manage_stock ? '1' : '0',
            $product->weight !== null ? (string) $product->weight : '',
            $product->length !== null ? (string) $product->length : '',
            $product->width !== null ? (string) $product->width : '',
            $product->height !== null ? (string) $product->height : '',
            $product->taxClass?->name ?? '',
            $product->meta_title ?? '',
            $product->meta_description ?? '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
    }

    /**
     * @return list<string|int|float|null>
     */
    protected function variableRow(Product $product, ProductVariant $variant): array
    {
        return [
            $product->sku ?? '',
            $product->product_type,
            $product->name,
            $product->slug,
            $product->brand?->name ?? '',
            $product->relationLoaded('categories')
                ? $product->categories->pluck('name')->implode('|')
                : '',
            $product->short_description ?? '',
            $product->description ?? '',
            $product->status,
            $product->visibility,
            (string) $product->regular_price,
            $product->sale_price !== null ? (string) $product->sale_price : '',
            $product->cost_price !== null ? (string) $product->cost_price : '',
            $product->stock_quantity !== null ? (string) $product->stock_quantity : '',
            $product->stock_status,
            $product->manage_stock ? '1' : '0',
            $product->weight !== null ? (string) $product->weight : '',
            $product->length !== null ? (string) $product->length : '',
            $product->width !== null ? (string) $product->width : '',
            $product->height !== null ? (string) $product->height : '',
            $product->taxClass?->name ?? '',
            $product->meta_title ?? '',
            $product->meta_description ?? '',
            $variant->sku ?? '',
            $variant->name ?? '',
            (string) $variant->regular_price,
            $variant->sale_price !== null ? (string) $variant->sale_price : '',
            $variant->stock_quantity !== null ? (string) $variant->stock_quantity : '',
            $variant->stock_status,
        ];
    }
}
