<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CartItem */
class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'quantity' => (string) $this->quantity,
            'unit_price' => (string) $this->unit_price,
            'subtotal' => (string) $this->subtotal,
            'product' => $this->when(
                $this->relationLoaded('product') && $this->product,
                fn () => (new ProductResource($this->product))->resolve()
            ),
            'variant' => $this->when(
                $this->relationLoaded('variant') && $this->variant,
                fn () => (new ProductVariantResource($this->variant))->resolve()
            ),
        ];
    }
}
