@props([
    'status',
])

@php
    $label = match ($status) {
        'in_stock' => __('customer.in_stock'),
        'out_of_stock' => __('customer.out_of_stock'),
        'on_backorder' => __('customer.on_backorder'),
        default => is_string($status) ? $status : '',
    };
    $cls = match ($status) {
        'in_stock' => 'border-green-200 bg-[#DCFCE7] text-green-800',
        'out_of_stock' => 'border-slate-200 bg-slate-100 text-slate-600',
        'on_backorder' => 'border-amber-200 bg-amber-50 text-amber-900',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
    };
@endphp

<span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $cls }}">{{ $label }}</span>
