<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    public function generate(): string
    {
        $prefix = 'MEK-'.now()->format('Ymd').'-';

        return DB::transaction(function () use ($prefix): string {
            $last = Order::withTrashed()
                ->where('order_number', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('order_number')
                ->value('order_number');

            $seq = 1;
            if ($last !== null && strlen($last) >= strlen($prefix) + 6) {
                $seq = (int) substr($last, -6) + 1;
            }

            if ($seq > 999999) {
                $seq = random_int(1, 999999);
            }

            $candidate = $prefix.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
            $guard = 0;
            while (Order::withTrashed()->where('order_number', $candidate)->exists() && $guard < 50) {
                $seq++;
                $candidate = $prefix.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
                $guard++;
            }

            if (Order::withTrashed()->where('order_number', $candidate)->exists()) {
                $candidate = $prefix.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            }

            return $candidate;
        });
    }
}
