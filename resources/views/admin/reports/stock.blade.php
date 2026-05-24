@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.stock_report')" />

    @include('admin.reports.partials.filters', ['filters' => $filters ?? [], 'exportType' => 'stock'])

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.product') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.variant') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.warehouse') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.quantity') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.reserved') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.available') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.stock_status') }}</th>
                </tr>
            </x-slot:head>

            @forelse($rows as $s)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $s->product?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $s->variant?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $s->warehouse?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $s->quantity, 0) }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $s->reserved_quantity, 0) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $s->available_quantity, 0) }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $s->product?->stock_status ?? '—' }}</td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="7" class="px-4 py-10">
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

