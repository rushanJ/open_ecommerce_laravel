@extends('admin.layouts.app')

@section('title', __('admin.dashboard').' — '.config('app.name'))

@section('breadcrumb', __('admin.dashboard'))

@section('content')
    <x-admin.page-header
        :title="__('admin.dashboard')"
        :subtitle="__('admin.dashboard_subtitle')"
    />

    @php
        $s = $stats ?? [];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <x-admin.stat-card :label="__('admin.total_orders')" :value="number_format((int) ($s['total_orders'] ?? 0))" />
        <x-admin.stat-card :label="__('admin.gross_sales')" :value="number_format((float) ($s['gross_sales'] ?? 0), 2)" />
        <x-admin.stat-card :label="__('admin.net_sales')" :value="number_format((float) ($s['net_sales'] ?? 0), 2)" />
        <x-admin.stat-card :label="__('admin.average_order_value')" :value="number_format((float) ($s['average_order_value'] ?? 0), 2)" />
        <x-admin.stat-card :label="__('admin.new_customers')" :value="number_format((int) ($s['new_customers'] ?? 0))" />
        <x-admin.stat-card :label="__('admin.low_stock_products')" :value="number_format((int) ($s['low_stock_products'] ?? 0))" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <x-admin.card :title="__('admin.recent_orders')">
            <x-admin.table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('admin.order_number') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('admin.customer') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.total') }}</th>
                    </tr>
                </x-slot:head>
                @forelse(($s['recent_orders'] ?? collect()) as $o)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            <a href="{{ route('admin.orders.show', $o) }}" class="hover:underline">{{ $o->order_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $o->customer?->email ?? $o->customer_email ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $o->grand_total, 2) }} {{ $o->currency_code }}</td>
                    </tr>
                @empty
                    <tr class="border-t border-slate-100">
                        <td colspan="3" class="px-4 py-8 text-sm text-slate-500">—</td>
                    </tr>
                @endforelse
            </x-admin.table>
        </x-admin.card>

        <x-admin.card :title="__('admin.top_products')">
            <x-admin.table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('admin.product') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.sold_quantity') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.revenue') }}</th>
                    </tr>
                </x-slot:head>
                @forelse(($s['top_products'] ?? collect()) as $p)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $p['product_name'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) ($p['sold_quantity'] ?? 0), 0) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) ($p['revenue'] ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr class="border-t border-slate-100">
                        <td colspan="3" class="px-4 py-8 text-sm text-slate-500">—</td>
                    </tr>
                @endforelse
            </x-admin.table>
        </x-admin.card>

        <x-admin.card :title="__('admin.recent_payments')">
            <x-admin.table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('admin.payment_reference') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('admin.provider') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.amount') }}</th>
                    </tr>
                </x-slot:head>
                @forelse(($s['recent_payments'] ?? collect()) as $pay)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $pay->payment_reference }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $pay->method?->provider ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $pay->amount, 2) }} {{ $pay->currency_code }}</td>
                    </tr>
                @empty
                    <tr class="border-t border-slate-100">
                        <td colspan="3" class="px-4 py-8 text-sm text-slate-500">—</td>
                    </tr>
                @endforelse
            </x-admin.table>
        </x-admin.card>

        <x-admin.card :title="__('admin.setup_checklist')">
            <ul class="space-y-3 text-sm text-gray-700 dark:text-gray-300" role="list">
                <li class="flex gap-2">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[color:var(--mk-admin-primary)]" aria-hidden="true"></span>
                    <span>{{ __('admin.setup_store') }}</span>
                </li>
                <li class="flex gap-2">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[color:var(--mk-admin-primary)]" aria-hidden="true"></span>
                    <span>{{ __('admin.setup_payhere') }}</span>
                </li>
                <li class="flex gap-2">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[color:var(--mk-admin-primary)]" aria-hidden="true"></span>
                    <span>{{ __('admin.setup_categories') }}</span>
                </li>
                <li class="flex gap-2">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[color:var(--mk-admin-primary)]" aria-hidden="true"></span>
                    <span>{{ __('admin.setup_products') }}</span>
                </li>
                <li class="flex gap-2">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[color:var(--mk-admin-primary)]" aria-hidden="true"></span>
                    <span>{{ __('admin.setup_shipping') }}</span>
                </li>
            </ul>
        </x-admin.card>
    </div>
@endsection
