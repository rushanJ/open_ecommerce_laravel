@props([
    'rating' => 0,
    'max' => 5,
])

@php
    $rating = (float) $rating;
    $max = (int) $max;
    $filled = (int) floor($rating);
@endphp

<div class="flex items-center gap-0.5" aria-label="{{ __('customer.rating') }}: {{ number_format($rating, 1) }}/{{ $max }}">
    @for($i = 1; $i <= $max; $i++)
        @php $on = $i <= $filled; @endphp
        <svg class="h-4 w-4 {{ $on ? 'text-amber-500' : 'text-slate-300' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.955a1 1 0 00.95.69h4.157c.969 0 1.371 1.24.588 1.81l-3.363 2.444a1 1 0 00-.364 1.118l1.286 3.955c.3.921-.755 1.688-1.539 1.118l-3.363-2.444a1 1 0 00-1.176 0l-3.363 2.444c-.784.57-1.838-.197-1.539-1.118l1.286-3.955a1 1 0 00-.364-1.118L2.07 9.382c-.783-.57-.38-1.81.588-1.81h4.157a1 1 0 00.95-.69l1.286-3.955z"/>
        </svg>
    @endfor
</div>

