<?php

namespace App\Http\Resources\AdminApi\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class AdminOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'fulfillment_status' => $this->fulfillment_status,
            'currency_code' => $this->currency_code,
            'coupon_code' => $this->coupon_code,
            'subtotal' => (string) $this->subtotal,
            'discount_total' => (string) $this->discount_total,
            'tax_total' => (string) $this->tax_total,
            'shipping_total' => (string) $this->shipping_total,
            'grand_total' => (string) $this->grand_total,
            'paid_total' => (string) $this->paid_total,
            'placed_at' => $this->placed_at?->toIso8601String(),
            'customer' => $this->when($this->relationLoaded('customer') && $this->customer, fn () => [
                'id' => $this->customer->id,
                'email' => $this->customer->email,
                'first_name' => $this->customer->first_name,
                'last_name' => $this->customer->last_name,
            ]),
        ];
    }
}
