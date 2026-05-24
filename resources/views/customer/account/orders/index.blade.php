@extends('customer.account.layouts.app')

@section('title', __('customer.order_history'))

@section('account_content')
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('customer.my_orders') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('customer.order_history') }}</p>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('customer.order_number') }}</th>
                        <th class="px-4 py-3">{{ __('customer.date') }}</th>
                        <th class="px-4 py-3">{{ __('customer.status') }}</th>
                        <th class="px-4 py-3">{{ __('customer.payment_status') }}</th>
                        <th class="px-4 py-3">{{ __('customer.total') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ optional($order->created_at)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ ucfirst($order->status) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $order->isPaid() ? __('customer.paid') : __('customer.unpaid') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ number_format((float) $order->grand_total, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('customer.account.orders.show', $order) }}" class="font-semibold text-emerald-700 hover:text-emerald-800">
                                    {{ __('customer.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-600">
                                {{ __('customer.no_orders_yet') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">
            {{ $orders->links() }}
        </div>
    </div>
@endsection

