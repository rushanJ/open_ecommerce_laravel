<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = (new OrderResource($this->resource))->toArray($request);

        return array_merge($base, [
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'sku' => $item->sku,
                    'quantity' => (string) $item->quantity,
                    'unit_price' => (string) $item->unit_price,
                    'subtotal' => (string) $item->subtotal,
                    'tax_total' => (string) $item->tax_total,
                    'total' => (string) $item->total,
                ]);
            }),
            'addresses' => $this->when(
                $this->relationLoaded('addresses'),
                fn () => AddressResource::collection($this->addresses)->resolve(),
                []
            ),
        ]);
    }
}
