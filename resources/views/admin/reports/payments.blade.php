@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.payments_report')" />

    @include('admin.reports.partials.filters', ['filters' => $filters ?? [], 'exportType' => 'payments'])

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.payment_reference') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.order') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.provider') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.amount') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.paid_at') }}</th>
                </tr>
            </x-slot:head>

            @forelse($rows as $p)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $p->payment_reference }}</td>
                    <td class="px-4 py-3 text-slate-700">
                        @if($p->order)
                            <a class="hover:underline" href="{{ route('admin.orders.show', $p->order) }}">{{ $p->order->order_number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $p->method?->provider ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format((float) $p->amount, 2) }} {{ $p->currency_code }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $p->status }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $p->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
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

