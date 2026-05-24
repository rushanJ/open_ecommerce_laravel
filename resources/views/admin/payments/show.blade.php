@extends('admin.layouts.app')

@section('title', __('admin.payment'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('admin.payment') }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $payment->payment_reference ?? '—' }}</p>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ __('admin.back') }}</a>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950 lg:col-span-2">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('admin.payment') }}</h2>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500">{{ __('admin.orders') }}</dt>
                        <dd class="font-medium">{{ $payment->order?->order_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.status') }}</dt>
                        <dd class="font-medium">{{ $payment->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.payment_reference') }}</dt>
                        <dd class="font-mono text-xs">{{ $payment->payment_reference ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.provider_transaction_id') }}</dt>
                        <dd class="font-mono text-xs">{{ $payment->provider_transaction_id ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.payments') }}</dt>
                        <dd class="font-medium">{{ $payment->method?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('admin.revenue') }}</dt>
                        <dd class="font-semibold">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency_code }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('admin.gateway_response') }}</h2>
                <pre class="mt-4 max-h-80 overflow-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ json_encode($payment->gateway_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </section>
        </div>

        <section class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('admin.payment_transactions') }}</h2>
            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-4 py-3">{{ __('admin.transaction_type') }}</th>
                            <th class="px-4 py-3">{{ __('admin.status') }}</th>
                            <th class="px-4 py-3">{{ __('admin.signature_valid') }}</th>
                            <th class="px-4 py-3">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-900">
                        @forelse ($payment->transactions as $tx)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $tx->transaction_type }}</td>
                                <td class="px-4 py-3">{{ $tx->status ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($tx->signature_valid === null)
                                        —
                                    @else
                                        {{ $tx->signature_valid ? 'yes' : 'no' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">{{ optional($tx->created_at)->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">{{ __('admin.no_records_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

