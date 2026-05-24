@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.menus')" :subtitle="__('admin.menus_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.menus.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />
            <x-admin.select name="location" :label="__('admin.location')">
                <option value="">{{ __('admin.all') }}</option>
                @foreach(['header','footer','account','mobile'] as $loc)
                    <option value="{{ $loc }}" @selected(($filters['location'] ?? '') === $loc)>{{ $loc }}</option>
                @endforeach
            </x-admin.select>
            <div class="flex items-end gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.menus.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>
            <div class="flex items-end justify-end">
                <a href="{{ route('admin.menus.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.create_menu') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.location') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($menus as $menu)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">
                        <a href="{{ route('admin.menus.show', $menu) }}" class="hover:underline">{{ $menu->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $menu->location }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$menu->status === 'active' ? 'success' : 'muted'">{{ ucfirst($menu->status) }}</x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.menus.edit', $menu) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="4" class="px-4 py-10">
                        <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4">
            {{ $menus->links() }}
        </div>
    </x-admin.card>
@endsection

