<?php

namespace App\Http\Resources\AdminApi\V1;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class AdminProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $token = $request->attributes->get('open_ecommerce_laravel_api_token');
        $canCost = $token instanceof ApiToken && $token->hasAbility('products.cost');

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'product_type' => $this->product_type,
            'regular_price' => (string) $this->regular_price,
            'sale_price' => $this->sale_price !== null ? (string) $this->sale_price : null,
            'manage_stock' => (bool) $this->manage_stock,
            'stock_quantity' => (string) $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold !== null ? (string) $this->low_stock_threshold : null,
            'stock_status' => $this->stock_status,
            'backorders_allowed' => (bool) $this->backorders_allowed,
            'published_at' => $this->published_at?->toIso8601String(),
            'brand' => $this->when($this->relationLoaded('brand') && $this->brand, fn () => [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ]),
            'categories' => $this->when(
                $this->relationLoaded('categories'),
                fn () => $this->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ])->all(),
                []
            ),
            'image_url' => media_url($this->relationLoaded('primaryImage') ? $this->primaryImage?->path : null),
            'images' => $this->when(
                $this->relationLoaded('images'),
                fn () => $this->images->map(fn ($img) => [
                    'id' => $img->id,
                    'path' => $img->path,
                    'url' => media_url($img->path),
                    'alt_text' => $img->alt_text,
                    'is_primary' => (bool) $img->is_primary,
                    'sort_order' => (int) $img->sort_order,
                ])->all(),
                []
            ),
            'variants' => $this->when(
                $this->relationLoaded('variants'),
                fn () => $this->variants->map(function ($v) use ($canCost) {
                    $row = [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'name' => $v->name,
                        'regular_price' => (string) $v->regular_price,
                        'sale_price' => $v->sale_price !== null ? (string) $v->sale_price : null,
                        'stock_quantity' => $v->stock_quantity !== null ? (string) $v->stock_quantity : null,
                        'stock_status' => $v->stock_status,
                        'status' => $v->status,
                    ];
                    if ($canCost) {
                        $row['cost_price'] = $v->cost_price !== null ? (string) $v->cost_price : null;
                    }

                    return $row;
                })->all(),
                []
            ),
        ];

        if ($canCost) {
            $data['cost_price'] = $this->cost_price !== null ? (string) $this->cost_price : null;
        }

        return $data;
    }
}
