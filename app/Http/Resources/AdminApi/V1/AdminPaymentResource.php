<?php

namespace App\Http\Resources\AdminApi\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payment */
class AdminPaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_method_id' => $this->payment_method_id,
            'payment_reference' => $this->payment_reference,
            'provider_transaction_id' => $this->provider_transaction_id,
            'status' => $this->status,
            'amount' => (string) $this->amount,
            'currency_code' => $this->currency_code,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'order' => $this->when($this->relationLoaded('order') && $this->order, fn () => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
                'payment_status' => $this->order->payment_status,
            ]),
            'method' => $this->when($this->relationLoaded('method') && $this->method, fn () => [
                'id' => $this->method->id,
                'name' => $this->method->name,
                'code' => $this->method->code,
            ]),
            'transactions' => $this->when(
                $this->relationLoaded('transactions'),
                fn () => $this->transactions->map(fn ($tx) => [
                    'id' => $tx->id,
                    'provider' => $tx->provider,
                    'transaction_type' => $tx->transaction_type,
                    'provider_reference' => $tx->provider_reference,
                    'status' => $tx->status,
                    'signature_valid' => $tx->signature_valid,
                    'created_at' => $tx->created_at?->toIso8601String(),
                ])->all(),
                []
            ),
        ];
    }
}
