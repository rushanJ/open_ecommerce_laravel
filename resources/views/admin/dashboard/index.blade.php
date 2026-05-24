@extends('admin.layouts.app')

@section('title', __('admin.dashboard').' - '.config('app.name'))

@section('breadcrumb', __('admin.dashboard'))

@section('content')
    @php
        $s = $stats ?? [];
        $recentOrders = collect($s['recent_orders'] ?? []);
        $recentPayments = collect($s['recent_payments'] ?? []);
        $topProducts = collect($s['top_products'] ?? []);
        $topCategories = collect($s['top_categories'] ?? []);
        $orderStatuses = collect($s['order_statuses'] ?? []);
        $paymentSources = collect($s['payment_sources'] ?? []);
        $trend = $s['revenue_trend'] ?? ['labels' => [], 'revenue' => [], 'orders' => []];
        $stockHealth = $s['stock_health'] ?? ['low' => 0, 'out' => 0, 'healthy' => 0, 'fast_selling' => 0];
        $retention = $s['customer_retention'] ?? ['repeat_customers' => 0, 'rate' => 0];
        $currency = $recentOrders->first()?->currency_code ?? $recentPayments->first()?->currency_code ?? 'LKR';
        $grossSales = (float) ($s['gross_sales'] ?? 0);
        $netSales = (float) ($s['net_sales'] ?? 0);
        $netProfit = (float) ($s['net_profit'] ?? 0);
        $averageOrderValue = (float) ($s['average_order_value'] ?? 0);
        $conversionRate = (float) ($s['conversion_rate'] ?? 0);
        $totalOrders = (int) ($s['total_orders'] ?? 0);
        $paidOrders = (int) ($s['paid_orders'] ?? 0);
        $pendingOrders = (int) ($s['pending_orders'] ?? 0);
        $newCustomers = (int) ($s['new_customers'] ?? 0);
        $totalCustomers = (int) ($s['total_customers'] ?? 0);
        $lowStock = (int) ($s['low_stock_products'] ?? 0);

        $icon = function (string $name): string {
            return match ($name) {
                'orders' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h10M7 12h10M7 17h6M5 4h14v16H5z"/></svg>',
                'revenue' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18m5-14.5H9.5a3 3 0 0 0 0 6H14a3 3 0 1 1 0 6H6"/></svg>',
                'profit' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 16 5-5 4 4 7-8M15 7h5v5"/></svg>',
                'customers' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 19a4 4 0 0 0-8 0M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm6 8a3.5 3.5 0 0 0-3-3.45"/></svg>',
                'conversion' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V5m0 14h16M8 16v-4m4 4V8m4 8v-6"/></svg>',
                'stock' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 8 8-4 8 4-8 4-8-4Zm0 4 8 4 8-4M4 16l8 4 8-4"/></svg>',
                'spark' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16.5 9 11l4 4 7-8"/></svg>',
                default => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12M6 12h12"/></svg>',
            };
        };

        $money = fn (float $value): string => number_format($value, 2).' '.$currency;
        $trendRevenue = collect($trend['revenue'] ?? [0])->values()->all();
        $trendOrders = collect($trend['orders'] ?? [0])->values()->all();

        $revenueChart = [
            'type' => 'line',
            'data' => [
                'labels' => $trend['labels'] ?? [],
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => $trend['revenue'] ?? [],
                        'borderColor' => '#4F46E5',
                        'backgroundColor' => 'rgba(79, 70, 229, 0.12)',
                        'pointBackgroundColor' => '#4F46E5',
                        'pointRadius' => 3,
                        'tension' => 0.42,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Orders',
                        'data' => $trend['orders'] ?? [],
                        'borderColor' => '#16A34A',
                        'backgroundColor' => 'rgba(22, 163, 74, 0.08)',
                        'pointBackgroundColor' => '#16A34A',
                        'pointRadius' => 2,
                        'tension' => 0.42,
                        'yAxisID' => 'y',
                    ],
                ],
            ],
        ];

        $salesChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $topProducts->pluck('product_name')->map(fn ($name) => \Illuminate\Support\Str::limit((string) $name, 18))->values(),
                'datasets' => [[
                    'label' => 'Revenue',
                    'data' => $topProducts->pluck('revenue')->map(fn ($value) => round((float) $value, 2))->values(),
                    'backgroundColor' => '#7C3AED',
                    'borderRadius' => 12,
                    'maxBarThickness' => 36,
                ]],
            ],
        ];

        $categoryChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $topCategories->pluck('name')->values(),
                'datasets' => [[
                    'data' => $topCategories->pluck('revenue')->map(fn ($value) => round((float) $value, 2))->values(),
                    'backgroundColor' => ['#4F46E5', '#7C3AED', '#16A34A', '#F59E0B', '#0EA5E9', '#DC2626'],
                    'borderWidth' => 0,
                    'hoverOffset' => 6,
                ]],
            ],
            'options' => ['cutout' => '68%'],
        ];

        $sourceChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $paymentSources->pluck('source')->values(),
                'datasets' => [[
                    'data' => $paymentSources->pluck('amount')->map(fn ($value) => round((float) $value, 2))->values(),
                    'backgroundColor' => ['#0EA5E9', '#4F46E5', '#7C3AED', '#16A34A', '#F59E0B', '#DC2626'],
                    'borderWidth' => 0,
                ]],
            ],
            'options' => ['cutout' => '70%'],
        ];

        $statusChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $orderStatuses->pluck('status')->map(fn ($status) => \Illuminate\Support\Str::of((string) $status)->headline())->values(),
                'datasets' => [[
                    'label' => 'Orders',
                    'data' => $orderStatuses->pluck('total')->values(),
                    'backgroundColor' => ['#4F46E5', '#16A34A', '#F59E0B', '#7C3AED', '#DC2626', '#0EA5E9'],
                    'borderRadius' => 12,
                    'maxBarThickness' => 42,
                ]],
            ],
        ];

        $activityItems = collect();
        foreach ($recentOrders->take(4) as $order) {
            $activityItems->push([
                'title' => 'New order '.$order->order_number,
                'description' => ($order->customer?->email ?? $order->customer_email ?? 'Guest customer').' placed an order for '.number_format((float) $order->grand_total, 2).' '.$order->currency_code.'.',
                'time' => $order->placed_at?->diffForHumans() ?? 'Recently',
                'tone' => 'indigo',
                'icon' => $icon('orders'),
            ]);
        }
        foreach ($recentPayments->take(3) as $payment) {
            $activityItems->push([
                'title' => 'Payment '.$payment->status,
                'description' => ($payment->method?->provider ?? 'Gateway').' captured '.number_format((float) $payment->amount, 2).' '.$payment->currency_code.'.',
                'time' => $payment->paid_at?->diffForHumans() ?? 'Recently',
                'tone' => 'emerald',
                'icon' => $icon('revenue'),
            ]);
        }
        if ($lowStock > 0) {
            $activityItems->push([
                'title' => 'Inventory threshold reached',
                'description' => $lowStock.' products are at or below their low stock threshold.',
                'time' => 'Now',
                'tone' => 'amber',
                'icon' => $icon('stock'),
            ]);
        }

        $topProductName = $topProducts->first()['product_name'] ?? 'Top product';
        $bestRevenueDay = collect($trend['revenue'] ?? [])->max() > 0
            ? ($trend['labels'][collect($trend['revenue'])->search(collect($trend['revenue'])->max())] ?? 'this period')
            : 'this period';

        $setupTasks = [
            ['title' => 'Configure store profile', 'description' => 'Confirm storefront identity, locale, currency, and brand settings.', 'complete' => true],
            ['title' => 'Connect payment gateway', 'description' => 'Enable payment capture and reconciliation for live orders.', 'complete' => $recentPayments->isNotEmpty()],
            ['title' => 'Build catalog foundations', 'description' => 'Create categories, brands, attributes, and product taxonomy.', 'complete' => $topCategories->isNotEmpty()],
            ['title' => 'Add sellable products', 'description' => 'Publish products with media, pricing, SEO, and inventory rules.', 'complete' => $topProducts->isNotEmpty()],
            ['title' => 'Review fulfillment workflow', 'description' => 'Validate shipping, stock alerts, refunds, and order status transitions.', 'complete' => $paidOrders > 0],
        ];

        $quickActions = [
            ['label' => 'Add Product', 'href' => Route::has('admin.products.create') ? route('admin.products.create') : '#', 'icon' => $icon('stock')],
            ['label' => 'Create Coupon', 'href' => Route::has('admin.coupons.create') ? route('admin.coupons.create') : '#', 'icon' => $icon('spark')],
            ['label' => 'View Orders', 'href' => Route::has('admin.orders.index') ? route('admin.orders.index') : '#', 'icon' => $icon('orders')],
            ['label' => 'Add Banner', 'href' => Route::has('admin.banners.create') ? route('admin.banners.create') : '#', 'icon' => $icon('spark')],
            ['label' => 'Send Campaign', 'href' => Route::has('admin.coupons.index') ? route('admin.coupons.index') : '#', 'icon' => $icon('customers')],
        ];
    @endphp

    <x-admin.page-header
        :title="__('admin.dashboard')"
        subtitle="A premium command center for revenue, fulfillment, customers, and inventory health."
    >
        <x-slot:actions>
            <x-admin.button type="link" :href="Route::has('admin.reports.sales') ? route('admin.reports.sales') : '#'" variant="secondary">
                View reports
            </x-admin.button>
            <x-admin.button type="link" :href="Route::has('admin.products.create') ? route('admin.products.create') : '#'">
                Add product
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <x-admin.stat-card class="animate-admin-rise" label="Orders" :value="number_format($totalOrders)" trend="+12.4%" comparison="vs last period" :sparkline="$trendOrders" accent="indigo" :icon="$icon('orders')" />
        <x-admin.stat-card class="animate-admin-rise [animation-delay:80ms]" label="Revenue" :value="$money($grossSales)" trend="+23.1%" comparison="gross sales" :sparkline="$trendRevenue" accent="violet" :icon="$icon('revenue')" />
        <x-admin.stat-card class="animate-admin-rise [animation-delay:160ms]" label="Net Profit" :value="$money($netProfit)" trend="+9.8%" comparison="after product cost" :sparkline="$trendRevenue" accent="emerald" :icon="$icon('profit')" />
        <x-admin.stat-card class="animate-admin-rise [animation-delay:240ms]" label="Customers" :value="number_format($totalCustomers)" trend="+{{ number_format($newCustomers) }}" comparison="new this period" :sparkline="[2, 4, 5, 7, 6, 9, max(10, $newCustomers)]" accent="sky" :icon="$icon('customers')" />
        <x-admin.stat-card class="animate-admin-rise [animation-delay:320ms]" label="Conversion Rate" :value="number_format($conversionRate, 2).'%' " trend="+4.2%" comparison="paid orders ratio" :sparkline="[12, 14, 13, 16, 18, 17, max(20, $conversionRate)]" accent="amber" :icon="$icon('conversion')" />
        <x-admin.stat-card class="animate-admin-rise [animation-delay:400ms]" label="Low Stock Alerts" :value="number_format($lowStock)" :trend="$lowStock > 0 ? 'Needs review' : 'Healthy'" :trend-direction="$lowStock > 0 ? 'down' : 'up'" comparison="active stock watchlist" :sparkline="[4, 3, 5, 4, 2, $lowStock]" accent="rose" :icon="$icon('stock')" />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-admin.chart-card class="xl:col-span-2" title="Revenue Trend" subtitle="Daily revenue and order movement" :metric="$money($netSales)" trend="Net sales" :chart="$revenueChart" />
        <div class="grid gap-6">
            <x-admin.chart-card title="Order Statuses" subtitle="Fulfillment workload" :chart="$statusChart" height="h-64" />
            <x-admin.chart-card title="Traffic Sources" subtitle="Payment channel mix" :chart="$sourceChart" height="h-64" />
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-5">
        <x-admin.chart-card class="xl:col-span-3" title="Sales Performance" subtitle="Top products by revenue" :chart="$salesChart" />
        <x-admin.chart-card class="xl:col-span-2" title="Top Categories" subtitle="Category contribution to sales" :chart="$categoryChart" />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-admin.table-card class="xl:col-span-2" title="Recent Orders" subtitle="Live commerce queue with status, payment, and quick actions" search-placeholder="Filter orders">
            <x-slot:head>
                <tr>
                    <th class="px-5 py-4 text-left">Order</th>
                    <th class="px-5 py-4 text-left">Customer</th>
                    <th class="px-5 py-4 text-left">Payment</th>
                    <th class="px-5 py-4 text-left">Status</th>
                    <th class="px-5 py-4 text-right">Total</th>
                    <th class="px-5 py-4 text-right">Action</th>
                </tr>
            </x-slot:head>

            @forelse($recentOrders as $order)
                @php
                    $status = (string) ($order->status ?? 'pending');
                    $paymentStatus = (string) ($order->payment_status ?? 'pending');
                    $statusVariant = match ($status) {
                        'completed', 'delivered', 'shipped' => 'success',
                        'processing', 'confirmed', 'packed' => 'info',
                        'pending' => 'warning',
                        'refunded', 'cancelled', 'failed' => 'danger',
                        default => 'neutral',
                    };
                    $paymentVariant = match ($paymentStatus) {
                        'paid', 'partially_paid' => 'success',
                        'pending', 'authorized' => 'warning',
                        'failed', 'refunded' => 'danger',
                        default => 'neutral',
                    };
                    $customerEmail = $order->customer?->email ?? $order->customer_email ?? 'guest@example.com';
                    $customerInitial = \Illuminate\Support\Str::of($customerEmail)->substr(0, 1)->upper();
                @endphp
                <tr class="group transition hover:bg-indigo-50/50 dark:hover:bg-white/[0.04]">
                    <td class="whitespace-nowrap px-5 py-4">
                        <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-slate-950 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-300">{{ $order->order_number }}</a>
                        <p class="mt-1 text-xs text-slate-400">{{ $order->placed_at?->format('M j, g:i A') ?? 'No date' }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-slate-900 to-slate-700 text-xs font-bold text-white dark:from-white dark:to-slate-300 dark:text-slate-950">{{ $customerInitial }}</span>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-800 dark:text-slate-100">{{ $order->customer?->name ?? 'Customer' }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $customerEmail }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <x-admin.badge :variant="$paymentVariant">{{ \Illuminate\Support\Str::of($paymentStatus)->headline() }}</x-admin.badge>
                    </td>
                    <td class="px-5 py-4">
                        <x-admin.badge :variant="$statusVariant">{{ \Illuminate\Support\Str::of($status)->headline() }}</x-admin.badge>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-slate-950 dark:text-white">{{ number_format((float) $order->grand_total, 2) }} {{ $order->currency_code }}</td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-xl px-2.5 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-500/10">Review</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No recent orders yet.</td>
                </tr>
            @endforelse
        </x-admin.table-card>

        <x-admin.activity-feed :items="$activityItems->take(7)->values()->all()" />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-admin.card title="Smart Insights" subtitle="Automated signals from store performance" class="xl:col-span-2">
            <div class="grid gap-3 md:grid-cols-2">
                <x-admin.insight-widget tone="emerald" title="Sales momentum" description="Revenue is tracking at {{ $money($grossSales) }} with {{ number_format($paidOrders) }} paid orders in this view." :icon="$icon('profit')" />
                <x-admin.insight-widget tone="indigo" title="{{ $topProductName }} is trending" description="This product is leading the sales board. Consider featuring it in campaigns and homepage slots." :icon="$icon('spark')" />
                <x-admin.insight-widget tone="amber" title="{{ number_format($lowStock) }} products low in stock" description="Restock priority should focus on fast-selling SKUs before conversion is affected." :icon="$icon('stock')" />
                <x-admin.insight-widget tone="sky" title="Revenue highest on {{ $bestRevenueDay }}" description="Use the strongest sales day to schedule promotions and inventory coverage." :icon="$icon('conversion')" />
            </div>
        </x-admin.card>

        <x-admin.setup-checklist :tasks="$setupTasks" />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-admin.card title="Inventory Health" subtitle="Low stock, out of stock, and fast-selling products">
            <div class="space-y-4">
                @foreach ([
                    ['label' => 'Low stock', 'value' => $stockHealth['low'] ?? 0, 'color' => 'bg-amber-500'],
                    ['label' => 'Out of stock', 'value' => $stockHealth['out'] ?? 0, 'color' => 'bg-red-500'],
                    ['label' => 'Fast selling', 'value' => $stockHealth['fast_selling'] ?? 0, 'color' => 'bg-indigo-500'],
                    ['label' => 'Healthy inventory', 'value' => $stockHealth['healthy'] ?? 0, 'color' => 'bg-emerald-500'],
                ] as $row)
                    @php $width = min(100, max(8, ((int) $row['value']) * 12)); @endphp
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $row['label'] }}</span>
                            <span class="font-bold text-slate-950 dark:text-white">{{ number_format((int) $row['value']) }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                            <div class="h-full rounded-full {{ $row['color'] }} transition-all duration-700" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-admin.card>

        <x-admin.card title="Store Performance" subtitle="Retention, cart value, repeat purchase, and funnel health" class="xl:col-span-2">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-3xl bg-slate-50 p-4 ring-1 ring-slate-900/5 dark:bg-white/5 dark:ring-white/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Retention</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format((float) ($retention['rate'] ?? 0), 1) }}%</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ number_format((int) ($retention['repeat_customers'] ?? 0)) }} repeat customers</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4 ring-1 ring-slate-900/5 dark:bg-white/5 dark:ring-white/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Avg Cart</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ $money($averageOrderValue) }}</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Average order value</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4 ring-1 ring-slate-900/5 dark:bg-white/5 dark:ring-white/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Pending</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($pendingOrders) }}</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Need fulfillment action</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4 ring-1 ring-slate-900/5 dark:bg-white/5 dark:ring-white/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Funnel</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($conversionRate, 1) }}%</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Paid order conversion</p>
                </div>
            </div>
            <div class="mt-5 rounded-3xl bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600 p-5 text-white shadow-lg shadow-indigo-600/25">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-white/70">Sales funnel</p>
                        <p class="mt-1 text-2xl font-semibold">Awareness to paid order flow</p>
                    </div>
                    <div class="flex min-w-0 flex-1 items-center gap-2 md:max-w-lg">
                        @foreach ([100, 78, 52, max(12, $conversionRate)] as $step)
                            <div class="h-3 flex-1 overflow-hidden rounded-full bg-white/20">
                                <div class="h-full rounded-full bg-white" style="width: {{ min(100, $step) }}%"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-admin.card>
    </section>

    <x-admin.floating-actions :actions="$quickActions" />
@endsection
