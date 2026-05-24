@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.sales_report')" />

    @include('admin.reports.partials.filters', ['filters' => $filters ?? [], 'exportType' => 'sales'])

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.date') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.orders') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.gross_sales') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.discount') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.tax') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.shipping') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.refunds') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.net_sales') }}</th>
                </tr>
            </x-slot:head>

            @forelse($rows as $r)
                @php
                    $gross = (float) ($r->gross_sales ?? 0);
                    $refunds = (float) ($r->refunds ?? 0);
                    $net = max(0.0, $gross - $refunds);
                @endphp
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $r->day }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ (int) $r->orders_count }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format($gross, 2) }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $r->discounts, 2) }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $r->tax, 2) }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $r->shipping, 2) }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format($refunds, 2) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format($net, 2) }}</td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="8" class="px-4 py-10">
                        <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    </x-admin.card>
@endsection

