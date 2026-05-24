<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Concerns\ResolvesProductPricing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductDetailResource extends JsonResource
{
    use ResolvesProductPricing;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = (new ProductResource($this->resource))->toArray($request);

        return array_merge($base, [
            'short_description' => $this->short_description,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($img) => [
                    'url' => media_url($img->path),
                    'alt_text' => $img->alt_text,
                    'sort_order' => $img->sort_order,
                    'is_primary' => $img->is_primary,
                ]);
            }),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants'))->resolve(),
            'attributes' => $this->whenLoaded('attributeAssignments', function () {
                return $this->attributeAssignments->map(fn ($a) => [
                    'name' => $a->relationLoaded('attribute') ? $a->attribute?->name : null,
                    'value' => $a->relationLoaded('value') ? $a->value?->value : null,
                ]);
            }),
            'reviews' => [
                'average_rating' => round($this->averageRating(), 2),
                'count' => $this->reviewsCount(),
            ],
            'related_products' => $this->when(
                $this->relationLoaded('relatedProducts'),
                fn () => ProductResource::collection($this->relatedProducts)->resolve(),
                []
            ),
        ]);
    }
}
