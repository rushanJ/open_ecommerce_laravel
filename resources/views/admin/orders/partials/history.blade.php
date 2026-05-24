@props([
    'order',
])

@php
    /** @var \App\Models\Order $order */
@endphp

<x-admin.card :title="__('admin.status_history')">
    @if ($order->statusHistories->isEmpty())
        <p class="text-sm text-gray-600 dark:text-gray-400">—</p>
    @else
        <ol class="space-y-3">
            @foreach ($order->statusHistories as $h)
                <li class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ $h->from_status ? __('admin.'.$h->from_status) : '—' }} → {{ __('admin.'.$h->to_status) }}
                        </div>
                        <div class="text-xs text-gray-500">{{ optional($h->created_at)->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        @if ($h->changedByAdmin)
                            Admin: {{ $h->changedByAdmin->name }}
                        @elseif ($h->changed_by_customer_id)
                            Customer #{{ $h->changed_by_customer_id }}
                        @endif
                    </div>
                    @if ($h->note)
                        <p class="mt-2 text-gray-700 dark:text-gray-200">{{ $h->note }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</x-admin.card>

