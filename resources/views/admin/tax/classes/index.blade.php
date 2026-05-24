@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.tax_classes')" :subtitle="__('admin.tax_classes_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.tax.classes.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />

            <div class="flex items-end gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.tax.classes.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>

            <div class="flex items-end justify-end lg:col-start-4">
                <a href="{{ route('admin.tax.classes.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.create_tax_class') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.slug') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.products_count') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.rates_count') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.created') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($classes as $c)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $c->name }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $c->slug }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $c->products_count }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $c->rates_count }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $c->created_at?->format('Y-m-d') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.tax.classes.edit', $c) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="6" class="px-4 py-10">
                        <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4">
            {{ $classes->links() }}
        </div>
    </x-admin.card>
@endsection

