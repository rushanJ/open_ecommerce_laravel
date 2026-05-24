<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function dashboardStats(array $filters = []): array
    {
        $orders = $this->ordersBaseQuery($filters)->with(['customer'])->latest('placed_at');

        $totalOrders = (clone $orders)->count();
        $paidOrders = (clone $orders)->whereIn('payment_status', ['paid', 'partially_paid'])->count();
        $pendingOrders = (clone $orders)->where('status', 'pending')->count();
        $cancelledOrders = (clone $orders)->where('status', 'cancelled')->count();

        $salesOrders = $this->salesOrdersQuery($filters);
        $grossSales = (float) (clone $salesOrders)->sum('grand_total');
        $refundedTotal = (float) (clone $salesOrders)->sum('refunded_total');
        $netSales = max(0.0, $grossSales - $refundedTotal);
        $averageOrderValue = $paidOrders > 0 ? ($grossSales / $paidOrders) : 0.0;

        $newCustomers = $this->applyDateRange(Customer::query(), 'created_at', $filters)->count();

        $lowStockProducts = Product::query()
            ->where('manage_stock', true)
            ->whereNotNull('low_stock_threshold')
            ->whereNotNull('stock_quantity')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        $recentOrders = (clone $orders)
            ->with(['customer'])
            ->limit(10)
            ->get();

        $topProducts = $this->topProducts($filters, 10);

        $recentPayments = $this->applyDateRange(Payment::query()->with(['order', 'method']), 'paid_at', $filters)
            ->latest('paid_at')
            ->limit(10)
            ->get();

        return [
            'total_orders' => $totalOrders,
            'paid_orders' => $paidOrders,
            'pending_orders' => $pendingOrders,
            'cancelled_orders' => $cancelledOrders,
            'gross_sales' => round($grossSales, 4),
            'net_sales' => round($netSales, 4),
            'refunded_total' => round($refundedTotal, 4),
            'average_order_value' => round($averageOrderValue, 4),
            'new_customers' => $newCustomers,
            'low_stock_products' => $lowStockProducts,
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
            'recent_payments' => $recentPayments,
        ];
    }

    /**
     * Sales report grouped by date.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator
     */
    public function salesReport(array $filters = []): LengthAwarePaginator
    {
        $query = $this->salesOrdersQuery($filters);

        $rows = $query
            ->selectRaw('DATE(placed_at) as day')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(grand_total) as gross_sales')
            ->selectRaw('SUM(discount_total) as discounts')
            ->selectRaw('SUM(tax_total) as tax')
            ->selectRaw('SUM(shipping_total) as shipping')
            ->selectRaw('SUM(refunded_total) as refunds')
            ->groupBy('day')
            ->orderByDesc('day');

        return $rows->paginate(31)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function orderReport(array $filters = []): LengthAwarePaginator
    {
        $query = $this->ordersBaseQuery($filters)
            ->with(['customer'])
            ->latest('placed_at');

        return $query->paginate(20)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function productReport(array $filters = []): LengthAwarePaginator
    {
        $orders = $this->salesOrdersQuery($filters)->select('id');

        $query = OrderItem::query()
            ->joinSub($orders, 'paid_orders', fn ($j) => $j->on('order_items.order_id', '=', 'paid_orders.id'))
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->selectRaw('order_items.product_id as product_id')
            ->selectRaw('COALESCE(products.name, order_items.product_name) as product_name')
            ->selectRaw('COALESCE(products.sku, order_items.sku) as sku')
            ->selectRaw('brands.name as brand_name')
            ->selectRaw('SUM(order_items.quantity) as sold_quantity')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->selectRaw('COALESCE(products.stock_quantity, 0) as stock_quantity')
            ->groupBy('order_items.product_id', 'products.name', 'order_items.product_name', 'products.sku', 'order_items.sku', 'brands.name', 'products.stock_quantity')
            ->orderByDesc('revenue');

        return $query->paginate(20)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function customerReport(array $filters = []): LengthAwarePaginator
    {
        $orders = $this->salesOrdersQuery($filters)
            ->select('id', 'customer_id', 'customer_email', 'placed_at', 'grand_total');

        $query = DB::query()
            ->fromSub($orders, 'o')
            ->leftJoin('customers', 'customers.id', '=', 'o.customer_id')
            ->selectRaw('o.customer_id as customer_id')
            ->selectRaw('COALESCE(customers.first_name, "") as first_name')
            ->selectRaw('COALESCE(customers.last_name, "") as last_name')
            ->selectRaw('COALESCE(customers.email, o.customer_email) as email')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(o.grand_total) as total_spent')
            ->selectRaw('MAX(o.placed_at) as last_order_at')
            ->groupBy('o.customer_id', 'customers.first_name', 'customers.last_name', 'customers.email', 'o.customer_email')
            ->orderByDesc('total_spent');

        return $this->paginateQuery($query, 20);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function stockReport(array $filters = []): LengthAwarePaginator
    {
        $query = InventoryStock::query()
            ->with(['warehouse', 'product', 'variant'])
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', (int) $id))
            ->orderByDesc('available_quantity');

        return $query->paginate(25)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paymentReport(array $filters = []): LengthAwarePaginator
    {
        $query = Payment::query()
            ->with(['order', 'method'])
            ->when(($filters['status'] ?? '') !== '', fn ($q) => $q->where('status', (string) $filters['status']))
            ->when(($filters['payment_status'] ?? '') !== '', fn ($q) => $q->where('status', (string) $filters['payment_status']))
            ->tap(fn (Builder $q) => $this->applyDateRange($q, 'paid_at', $filters))
            ->latest('paid_at');

        return $query->paginate(20)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function couponReport(array $filters = []): LengthAwarePaginator
    {
        $usages = CouponUsage::query()
            ->when(($filters['date_from'] ?? null) || ($filters['date_to'] ?? null), function ($q) use ($filters) {
                return $this->applyDateRange($q, 'used_at', $filters);
            });

        $query = Coupon::query()
            ->leftJoinSub(
                $usages->selectRaw('coupon_id, COUNT(*) as used_count_calc, SUM(discount_amount) as discount_total')->groupBy('coupon_id'),
                'u',
                fn ($j) => $j->on('coupons.id', '=', 'u.coupon_id')
            )
            ->select('coupons.*')
            ->selectRaw('COALESCE(u.used_count_calc, coupons.used_count, 0) as used_count_calc')
            ->selectRaw('COALESCE(u.discount_total, 0) as discount_total')
            ->when(($filters['status'] ?? '') !== '', fn ($q) => $q->where('coupons.status', (string) $filters['status']))
            ->orderByDesc('used_count_calc');

        return $query->paginate(20)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function topProducts(array $filters, int $limit): Collection
    {
        $orders = $this->salesOrdersQuery($filters)->select('id');

        return OrderItem::query()
            ->joinSub($orders, 'paid_orders', fn ($j) => $j->on('order_items.order_id', '=', 'paid_orders.id'))
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('order_items.product_id as product_id')
            ->selectRaw('COALESCE(products.name, order_items.product_name) as product_name')
            ->selectRaw('SUM(order_items.quantity) as sold_quantity')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('order_items.product_id', 'products.name', 'order_items.product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'product_id' => (int) ($r->product_id ?? 0),
                'product_name' => (string) ($r->product_name ?? '—'),
                'sold_quantity' => (float) ($r->sold_quantity ?? 0),
                'revenue' => (float) ($r->revenue ?? 0),
            ]);
    }

    /**
     * Base orders query used for counts/listing; includes date range.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Order>
     */
    private function ordersBaseQuery(array $filters): Builder
    {
        $query = Order::query();
        $this->applyDateRange($query, 'placed_at', $filters);

        if (($filters['status'] ?? '') !== '') {
            $query->where('status', (string) $filters['status']);
        }
        if (($filters['payment_status'] ?? '') !== '') {
            $query->where('payment_status', (string) $filters['payment_status']);
        }
        if (($filters['customer_id'] ?? null) !== null && $filters['customer_id'] !== '') {
            $query->where('customer_id', (int) $filters['customer_id']);
        }

        return $query;
    }

    /**
     * Sales orders query (paid by default).
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Order>
     */
    private function salesOrdersQuery(array $filters): Builder
    {
        $query = $this->ordersBaseQuery($filters);

        if (($filters['payment_status'] ?? '') === '') {
            $query->whereIn('payment_status', ['paid', 'partially_paid']);
        }

        return $query;
    }

    /**
     * @param  Builder  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyDateRange(Builder $query, string $column, array $filters): Builder
    {
        $from = $this->parseDate($filters['date_from'] ?? null);
        $to = $this->parseDate($filters['date_to'] ?? null);

        if ($from) {
            $query->where($column, '>=', $from->startOfDay());
        }
        if ($to) {
            $query->where($column, '<=', $to->endOfDay());
        }

        return $query;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function paginateQuery(\Illuminate\Database\Query\Builder $query, int $perPage): LengthAwarePaginator
    {
        $page = (int) request()->get('page', 1);
        $total = (int) (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}

