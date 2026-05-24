@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.customers_report')" />

    @include('admin.reports.partials.filters', ['filters' => $filters ?? [], 'exportType' => 'customers'])

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.customer') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.email') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.orders') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.total_spent') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.last_order') }}</th>
                </tr>
            </x-slot:head>

            @forelse($rows as $c)
                @php $name = trim(($c->first_name ?? '').' '.($c->last_name ?? '')); @endphp
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $name !== '' ? $name : '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $c->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-slate-700">{{ (int) $c->orders_count }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $c->total_spent, 2) }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $c->last_order_at ? \Illuminate\Support\Carbon::parse($c->last_order_at)->format('Y-m-d') : '—' }}</td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="5" class="px-4 py-10">
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

