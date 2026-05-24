@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.banners')" :subtitle="__('admin.banners_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.banners.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />
            <x-admin.input name="position" :label="__('admin.position')" :value="$filters['position'] ?? ''" />
            <x-admin.select name="status" :label="__('admin.status')">
                <option value="">{{ __('admin.all') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('admin.active') }}</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('admin.inactive') }}</option>
            </x-admin.select>
            <div class="flex items-end gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.banners.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>
            <div class="flex items-end justify-end">
                <a href="{{ route('admin.banners.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.create_banner') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.title') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.position') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.date_range') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($banners as $banner)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $banner->title }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $banner->position }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$banner->status === 'active' ? 'success' : 'muted'">{{ ucfirst($banner->status) }}</x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $banner->starts_at?->format('Y-m-d') ?? '—' }} → {{ $banner->ends_at?->format('Y-m-d') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="5" class="px-4 py-10">
                        <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4">
            {{ $banners->links() }}
        </div>
    </x-admin.card>
@endsection

