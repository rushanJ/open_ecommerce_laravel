@props([
    'order',
])

@php
    /** @var \App\Models\Order $order */
    $latest = $order->latestPayment;
@endphp

<x-admin.card :title="__('admin.payments')" :subtitle="$latest ? ($latest->status.' · '.number_format((float) $latest->amount, 2).' '.$latest->currency_code) : null">
    @if ($order->payments->isEmpty())
        <p class="text-sm text-gray-600 dark:text-gray-400">—</p>
    @else
        <div class="space-y-4">
            @foreach ($order->payments as $p)
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $p->method?->name ?? $p->method?->provider ?? '—' }}
                        </div>
                        <x-admin.badge :variant="$p->status === 'paid' ? 'success' : ($p->status === 'failed' ? 'danger' : 'neutral')">
                            {{ $p->status }}
                        </x-admin.badge>
                    </div>
                    <div class="mt-2 grid gap-2 text-xs text-gray-600 dark:text-gray-400 sm:grid-cols-2">
                        <div><span class="font-medium">{{ __('admin.payment_reference') }}:</span> <span class="font-mono">{{ $p->payment_reference ?? '—' }}</span></div>
                        <div><span class="font-medium">{{ __('admin.provider_transaction_id') }}:</span> <span class="font-mono">{{ $p->provider_transaction_id ?? '—' }}</span></div>
                    </div>
                    @if ($p->transactions->isNotEmpty())
                        <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                            <div class="font-medium">{{ __('admin.payment_transactions') }}</div>
                            <ul class="mt-1 space-y-1">
                                @foreach ($p->transactions->take(5) as $tx)
                                    <li class="flex justify-between gap-3">
                                        <span class="font-mono">{{ $tx->transaction_type }}</span>
                                        <span>{{ $tx->status ?? '—' }}</span>
                                        <span>
                                            @if ($tx->signature_valid === null)
                                                —
                                            @else
                                                {{ $tx->signature_valid ? 'sig:ok' : 'sig:bad' }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-admin.card>

