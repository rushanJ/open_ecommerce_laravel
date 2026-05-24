<?php

namespace App\Http\Controllers\Customer\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $customer = auth('customer')->user();

        $latestOrders = Order::query()
            ->where('customer_id', $customer?->getKey())
            ->latest('id')
            ->limit(5)
            ->get();

        $orderCount = Order::query()
            ->where('customer_id', $customer?->getKey())
            ->count();

        $totalSpent = (float) Order::query()
            ->where('customer_id', $customer?->getKey())
            ->where('paid_total', '>', 0)
            ->sum('paid_total');

        $defaultShipping = $customer?->addresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->latest('id')
            ->first();

        $defaultBilling = $customer?->addresses()
            ->where('type', 'billing')
            ->where('is_default', true)
            ->latest('id')
            ->first();

        return view('customer.account.dashboard', [
            'customer' => $customer,
            'latestOrders' => $latestOrders,
            'orderCount' => $orderCount,
            'totalSpent' => $totalSpent,
            'defaultShipping' => $defaultShipping,
            'defaultBilling' => $defaultBilling,
        ]);
    }
}

