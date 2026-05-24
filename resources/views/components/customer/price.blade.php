@props([
    'regular',
    'sale' => null,
    'showFrom' => false,
])

@php
    $regular = (float) $regular;
    $sale = $sale !== null ? (float) $sale : null;
    $hasSale = $sale !== null && $sale > 0 && $sale < $regular;
@endphp

<div class="text-sm">
    @if ($showFrom)
        <span class="mr-1 text-xs font-bold text-slate-500">{{ \Illuminate\Support\Str::lower(__('customer.from')) }}</span>
    @endif
    @if ($hasSale)
        <span class="font-black text-green-700">LKR {{ number_format($sale, 2) }}</span>
        <span class="ml-2 text-xs text-slate-400 line-through">LKR {{ number_format($regular, 2) }}</span>
    @else
        <span class="font-black text-slate-950">LKR {{ number_format($regular, 2) }}</span>
    @endif
</div>
