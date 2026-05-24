<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesProductPricing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    use ResolvesProductPricing;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $prices = $this->variantOrProductPrices($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'price' => $prices['price'],
            'regular_price' => $prices['regular_price'],
            'sale_price' => $prices['sale_price'],
            'image_url' => media_url($this->relationLoaded('primaryImage') ? $this->primaryImage?->path : null),
            'stock_status' => $this->stock_status,
            'product_type' => $this->product_type,
            'brand' => $this->when($this->relationLoaded('brand') && $this->brand, fn () => (new BrandResource($this->brand))->resolve()),
            'categories' => $this->when(
                $this->relationLoaded('categories'),
                fn () => CategoryResource::collection($this->categories)->resolve(),
                []
            ),
        ];
    }
}
