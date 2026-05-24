<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Cart */
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency_code' => $this->currency_code,
            'coupon_id' => $this->coupon_id,
            'coupon_code' => $this->coupon_code,
            'subtotal' => (string) $this->subtotal,
            'discount_total' => (string) $this->discount_total,
            'tax_total' => (string) $this->tax_total,
            'shipping_total' => (string) $this->shipping_total,
            'grand_total' => (string) $this->grand_total,
            'items' => $this->when(
                $this->relationLoaded('items'),
                fn () => CartItemResource::collection($this->items)->resolve(),
                []
            ),
        ];
    }
}
