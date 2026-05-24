<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderNote;
use App\Models\Refund;
use App\Models\RefundItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function requestRefund(Order $order, array $data, ?int $adminUserId): Refund
    {
        return DB::transaction(function () use ($order, $data, $adminUserId): Refund {
            /** @var Order $locked */
            $locked = Order::query()->lockForUpdate()->whereKey($order->getKey())->firstOrFail();

            if (! in_array($locked->payment_status, ['paid', 'partially_paid'], true)) {
                throw ValidationException::withMessages([
                    'amount' => [__('admin.refund_not_allowed')],
                ]);
            }

            $refundable = (float) $locked->paid_total - (float) $locked->refunded_total;
            $amount = (float) ($data['amount'] ?? 0);

            if ($amount <= 0 || $amount > $refundable) {
                throw ValidationException::withMessages([
                    'amount' => [__('admin.refund_amount_invalid')],
                ]);
            }

            $refundNumber = $data['refund_number'] ?? ('RF-'.$locked->order_number.'-'.now()->format('YmdHis'));

            $refund = Refund::query()->create([
                'order_id' => $locked->getKey(),
                'payment_id' => $locked->latestPayment?->getKey(),
                'refund_number' => $refundNumber,
                'amount' => $amount,
                'reason' => $data['reason'] ?? null,
                'status' => 'requested',
                'requested_by_admin_id' => $adminUserId,
                'approved_by_admin_id' => null,
                'processed_at' => null,
            ]);

            $items = $data['items'] ?? null;
            if (is_array($items)) {
                foreach ($items as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    if (empty($row['order_item_id'])) {
                        continue;
                    }
                    RefundItem::query()->create([
                        'refund_id' => $refund->getKey(),
                        'order_item_id' => (int) $row['order_item_id'],
                        'quantity' => (float) ($row['quantity'] ?? 0),
                        'amount' => (float) ($row['amount'] ?? 0),
                    ]);
                }
            }

            OrderNote::query()->create([
                'order_id' => $locked->getKey(),
                'note' => 'Refund requested: '.$refund->refund_number,
                'is_customer_visible' => false,
                'admin_user_id' => $adminUserId,
            ]);

            return $refund->fresh(['items']);
        });
    }

    public function approveRefund(Refund $refund, ?int $adminUserId): Refund
    {
        return DB::transaction(function () use ($refund, $adminUserId): Refund {
            /** @var Refund $locked */
            $locked = Refund::query()->lockForUpdate()->whereKey($refund->getKey())->firstOrFail();
            $locked->status = 'approved';
            $locked->approved_by_admin_id = $adminUserId;
            $locked->save();

            OrderNote::query()->create([
                'order_id' => $locked->order_id,
                'note' => 'Refund approved: '.$locked->refund_number,
                'is_customer_visible' => false,
                'admin_user_id' => $adminUserId,
            ]);

            return $locked->fresh(['items', 'order']);
        });
    }

    public function rejectRefund(Refund $refund, string $reason, ?int $adminUserId): Refund
    {
        return DB::transaction(function () use ($refund, $reason, $adminUserId): Refund {
            /** @var Refund $locked */
            $locked = Refund::query()->lockForUpdate()->whereKey($refund->getKey())->firstOrFail();
            $locked->status = 'rejected';
            $locked->save();

            OrderNote::query()->create([
                'order_id' => $locked->order_id,
                'note' => 'Refund rejected: '.$locked->refund_number.' — '.$reason,
                'is_customer_visible' => false,
                'admin_user_id' => $adminUserId,
            ]);

            return $locked->fresh(['items', 'order']);
        });
    }
}

