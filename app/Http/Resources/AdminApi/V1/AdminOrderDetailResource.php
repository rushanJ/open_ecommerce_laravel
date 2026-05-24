<?php

namespace App\Http\Resources\AdminApi\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class AdminOrderDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = (new AdminOrderResource($this->resource))->toArray($request);

        return array_merge($base, [
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'refunded_total' => (string) $this->refunded_total,
            'customer_note' => $this->customer_note,
            'admin_note' => $this->admin_note,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product_name,
                    'sku' => $item->sku,
                    'quantity' => (string) $item->quantity,
                    'unit_price' => (string) $item->unit_price,
                    'subtotal' => (string) $item->subtotal,
                    'discount_total' => (string) $item->discount_total,
                    'tax_total' => (string) $item->tax_total,
                    'total' => (string) $item->total,
                ]);
            }),
            'addresses' => $this->when(
                $this->relationLoaded('addresses'),
                fn () => $this->addresses->map(fn ($a) => [
                    'type' => $a->type,
                    'first_name' => $a->first_name,
                    'last_name' => $a->last_name,
                    'phone' => $a->phone,
                    'email' => $a->email,
                    'address_line_1' => $a->address_line_1,
                    'address_line_2' => $a->address_line_2,
                    'city' => $a->city,
                    'district' => $a->district,
                    'province' => $a->province,
                    'postal_code' => $a->postal_code,
                    'country_code' => $a->country_code,
                ])->all(),
                []
            ),
            'coupon' => $this->when($this->relationLoaded('coupon') && $this->coupon, fn () => [
                'id' => $this->coupon->id,
                'code' => $this->coupon->code,
            ]),
        ]);
    }
}
