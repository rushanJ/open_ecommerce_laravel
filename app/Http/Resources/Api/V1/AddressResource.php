<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderAddress|\App\Models\CustomerAddress */
class AddressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type ?? null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'district' => $this->district,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'country_code' => $this->country_code,
            'is_default' => $this->when(
                array_key_exists('is_default', $this->resource->getAttributes()),
                (bool) $this->is_default
            ),
        ];
    }
}
