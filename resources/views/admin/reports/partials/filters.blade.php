@props([
    'filters' => [],
    'exportType' => null,
])

@php
    $filters = is_array($filters) ? $filters : [];
    $exportType = $exportType ?: null;
@endphp

<x-admin.card class="mt-6">
    <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
        <x-admin.input name="date_from" type="date" :label="__('admin.date_from')" :value="$filters['date_from'] ?? ''" />
        <x-admin.input name="date_to" type="date" :label="__('admin.date_to')" :value="$filters['date_to'] ?? ''" />

        <div class="flex items-end gap-2 lg:col-span-2">
            <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
            <a href="{{ request()->url() }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
        </div>

        <div class="flex items-end justify-end lg:col-span-2">
            @if($exportType && \Illuminate\Support\Facades\Route::has('admin.reports.export'))
                <a href="{{ route('admin.reports.export', ['type' => $exportType] + request()->query()) }}"
                   class="inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    {{ __('admin.export_csv') }}
                </a>
            @endif
        </div>
    </form>
</x-admin.card>

