<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderNote;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected NotificationService $notifications,
        protected WebhookService $webhooks,
    ) {}

    public function updateStatus(Order $order, string $newStatus, ?string $note, ?int $adminUserId): Order
    {
        $newStatus = trim($newStatus);

        return DB::transaction(function () use ($order, $newStatus, $note, $adminUserId): Order {
            /** @var Order $locked */
            $locked = Order::query()->lockForUpdate()->whereKey($order->getKey())->firstOrFail();

            if (! $locked->canTransitionTo($newStatus)) {
                throw ValidationException::withMessages([
                    'status' => [__('admin.invalid_status_transition')],
                ]);
            }

            $from = $locked->status;
            $locked->status = $newStatus;

            $this->markFulfillmentStatus($locked);

            $locked->save();

            OrderStatusHistory::query()->create([
                'order_id' => $locked->getKey(),
                'from_status' => $from,
                'to_status' => $newStatus,
                'note' => $note,
                'changed_by_admin_id' => $adminUserId,
                'changed_by_customer_id' => null,
                'created_at' => now(),
            ]);

            if ($newStatus === 'cancelled' && in_array($locked->payment_status, ['unpaid', 'pending', 'failed'], true)) {
                $this->inventoryService->releaseReservationForOrder($locked->fresh(['items']));
            }

            $orderId = $locked->getKey();
            $fromStatus = $from;
            $toStatus = $newStatus;
            DB::afterCommit(function () use ($orderId, $fromStatus, $toStatus): void {
                try {
                    $fresh = Order::query()->find($orderId);
                    if ($fresh !== null) {
                        $this->notifications->notifyOrderStatusChanged($fresh, $fromStatus, $toStatus);
                    }
                } catch (\Throwable $e) {
                    Log::error('notifyOrderStatusChanged hook failed', [
                        'order_id' => $orderId,
                        'exception' => $e->getMessage(),
                    ]);
                }
                try {
                    $this->webhooks->dispatchSafe('order.status_changed', [
                        'order_id' => $orderId,
                        'from_status' => $fromStatus,
                        'to_status' => $toStatus,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('order.status_changed webhook dispatch failed', [
                        'order_id' => $orderId,
                        'exception' => $e->getMessage(),
                    ]);
                }
            });

            return $locked->fresh();
        });
    }

    public function addNote(Order $order, string $note, bool $isCustomerVisible, ?int $adminUserId): OrderNote
    {
        return DB::transaction(function () use ($order, $note, $isCustomerVisible, $adminUserId): OrderNote {
            /** @var Order $locked */
            $locked = Order::query()->lockForUpdate()->whereKey($order->getKey())->firstOrFail();

            return OrderNote::query()->create([
                'order_id' => $locked->getKey(),
                'note' => $note,
                'is_customer_visible' => $isCustomerVisible,
                'admin_user_id' => $adminUserId,
            ]);
        });
    }

    public function markFulfillmentStatus(Order $order): void
    {
        if ($order->status === 'delivered') {
            $order->fulfillment_status = 'fulfilled';

            return;
        }

        if ($order->status === 'shipped') {
            $order->fulfillment_status = 'partial';

            return;
        }

        $order->fulfillment_status = 'unfulfilled';
    }
}

