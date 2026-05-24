@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.tax_rates')" :subtitle="__('admin.tax_rates_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.tax.rates.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />

            <x-admin.select name="tax_class_id" :label="__('admin.tax_class')">
                <option value="">{{ __('admin.all') }}</option>
                @foreach($taxClasses as $c)
                    <option value="{{ $c->id }}" @selected((string) ($filters['tax_class_id'] ?? '') === (string) $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="status" :label="__('admin.status')">
                <option value="">{{ __('admin.all') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('admin.active') }}</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('admin.inactive') }}</option>
            </x-admin.select>

            <div class="flex items-end gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.tax.rates.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>

            <div class="flex items-end justify-end lg:col-start-6">
                <a href="{{ route('admin.tax.rates.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.create_tax_rate') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.tax_class') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.country') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.province') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.district') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.rate_percent') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.priority') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.compound') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($rates as $r)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $r->name }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $r->taxClass?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $r->country_code }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $r->province ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $r->district ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ number_format((float) $r->rate, 4) }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ (int) $r->priority }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $r->is_compound ? __('admin.yes') : __('admin.no') }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$r->status === 'active' ? 'success' : 'muted'">{{ ucfirst($r->status) }}</x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.tax.rates.edit', $r) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="10" class="px-4 py-10">
                        <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4">
            {{ $rates->links() }}
        </div>
    </x-admin.card>
@endsection

