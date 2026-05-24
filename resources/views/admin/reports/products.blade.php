@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.products_report')" />

    @include('admin.reports.partials.filters', ['filters' => $filters ?? [], 'exportType' => 'products'])

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.product') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.sku') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.brand') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.sold_quantity') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.revenue') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.stock') }}</th>
                </tr>
            </x-slot:head>

            @forelse($rows as $p)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $p->product_name }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $p->sku ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $p->brand_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $p->sold_quantity, 0) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $p->revenue, 2) }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) ($p->stock_quantity ?? 0), 0) }}</td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="6" class="px-4 py-10">
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

