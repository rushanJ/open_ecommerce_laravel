<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreOrderNoteRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends BaseAdminController
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): View
    {
        $query = Order::query()
            ->with(['customer', 'latestPayment.method'])
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $q = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($q): void {
                $w->where('order_number', 'like', $q)
                    ->orWhere('customer_email', 'like', $q)
                    ->orWhere('customer_phone', 'like', $q);
            });
        }

        foreach (['status', 'payment_status', 'fulfillment_status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load([
            'customer',
            'items.product',
            'items.variant',
            'billingAddress',
            'shippingAddress',
            'statusHistories.changedByAdmin',
            'notes.adminUser',
            'payments.method',
            'payments.transactions',
            'refunds.items',
            'shipments',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $before = $order->only(['status', 'fulfillment_status', 'payment_status']);
        $this->orderService->updateStatus(
            $order,
            (string) $request->input('status'),
            $request->input('note'),
            auth('admin')->id(),
        );
        $order->refresh();
        $this->logAdminActivity('orders', 'status_update', $order, $before, $order->only(['status', 'fulfillment_status', 'payment_status']));

        return back()->with('success', __('admin.status_updated'));
    }

    public function addNote(StoreOrderNoteRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->addNote(
            $order,
            (string) $request->input('note'),
            (bool) $request->boolean('is_customer_visible'),
            auth('admin')->id(),
        );

        return back()->with('success', __('admin.order_note_added'));
    }
}

