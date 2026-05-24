@props([
    'title',
    'address',
])

@php
    /** @var \App\Models\OrderAddress|null $address */
@endphp

<x-admin.card :title="$title">
    @if (! $address)
        <p class="text-sm text-gray-600 dark:text-gray-400">—</p>
    @else
        <div class="space-y-1 text-sm text-gray-700 dark:text-gray-200">
            <div class="font-medium">{{ $address->first_name }} {{ $address->last_name }}</div>
            @if ($address->phone)<div>{{ $address->phone }}</div>@endif
            @if ($address->email)<div>{{ $address->email }}</div>@endif
            <div>{{ $address->address_line_1 }}</div>
            @if ($address->address_line_2)<div>{{ $address->address_line_2 }}</div>@endif
            <div>{{ $address->city }} @if($address->district) · {{ $address->district }} @endif @if($address->province) · {{ $address->province }} @endif</div>
            @if ($address->postal_code)<div>{{ $address->postal_code }}</div>@endif
            <div>{{ $address->country_code }}</div>
        </div>
    @endif
</x-admin.card>

