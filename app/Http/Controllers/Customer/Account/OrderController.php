<?php

namespace App\Http\Controllers\Customer\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $customerId = (int) auth('customer')->id();

        $orders = Order::query()
            ->where('customer_id', $customerId)
            ->latest('id')
            ->paginate(15);

        return view('customer.account.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $customerId = (int) auth('customer')->id();
        abort_unless((int) $order->customer_id === $customerId, 404);

        $order->load([
            'items.product',
            'items.variant',
            'addresses',
            'statusHistories',
            'latestPayment.paymentMethod',
        ]);

        return view('customer.account.orders.show', [
            'order' => $order,
        ]);
    }
}

