@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.menu_items')" :subtitle="$menu->name" />

    <div class="mt-6 flex items-center justify-between gap-4">
        <a href="{{ route('admin.menus.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
        <a href="{{ route('admin.menus.items.create', $menu) }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
            {{ __('admin.create_menu_item') }}
        </a>
    </div>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.title') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.type') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.reference') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.custom_url') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.target') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.sort_order') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($menu->activeItems as $item)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->title }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $item->type }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $item->reference_id ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $item->url ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $item->target }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $item->sort_order }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.menu-items.edit', $item) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                        <span class="mx-2 text-slate-300">|</span>
                        <form method="POST" action="{{ route('admin.menu-items.destroy', $item) }}" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-700 hover:underline">{{ __('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @foreach($item->children as $child)
                    <tr class="border-t border-slate-50 bg-slate-50/40">
                        <td class="px-4 py-3 text-slate-900">↳ {{ $child->title }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $child->type }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $child->reference_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $child->url ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $child->target }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $child->sort_order }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.menu-items.edit', $child) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                            <span class="mx-2 text-slate-300">|</span>
                            <form method="POST" action="{{ route('admin.menu-items.destroy', $child) }}" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-rose-700 hover:underline">{{ __('admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr class="border-t border-slate-100">
                    <td colspan="7" class="px-4 py-10">
                        <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                    </td>
                </tr>
            @endforelse
        </x-admin.table>
    </x-admin.card>
@endsection

