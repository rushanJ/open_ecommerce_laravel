@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.orders_report')" />

    @include('admin.reports.partials.filters', ['filters' => $filters ?? [], 'exportType' => 'orders'])

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.order_number') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.customer') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.payment_status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.total') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.date') }}</th>
                </tr>
            </x-slot:head>

            @forelse($rows as $o)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">
                        <a href="{{ route('admin.orders.show', $o) }}" class="hover:underline">{{ $o->order_number }}</a>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $o->customer?->email ?? $o->customer_email ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $o->status }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $o->payment_status }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $o->grand_total, 2) }} {{ $o->currency_code }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $o->placed_at?->format('Y-m-d') ?? '—' }}</td>
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

