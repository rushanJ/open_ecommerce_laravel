<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesProductPricing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ProductVariant */
class ProductVariantResource extends JsonResource
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
            'product_id' => $this->product_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $prices['price'],
            'regular_price' => $prices['regular_price'],
            'sale_price' => $prices['sale_price'],
            'stock_status' => $this->stock_status,
            'status' => $this->status,
            'image_url' => media_url($this->image_path),
            'values' => $this->whenLoaded('values', function () {
                return $this->values->map(fn ($v) => [
                    'attribute' => $v->relationLoaded('attribute') ? $v->attribute?->name : null,
                    'value' => $v->relationLoaded('value') ? $v->value?->value : null,
                ]);
            }),
        ];
    }
}
