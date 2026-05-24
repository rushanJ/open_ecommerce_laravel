@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.pages')" :subtitle="__('admin.pages_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.pages.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />
            <x-admin.select name="status" :label="__('admin.status')">
                <option value="">{{ __('admin.all') }}</option>
                @foreach(['draft','published','archived'] as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ __('admin.'.$s) }}</option>
                @endforeach
            </x-admin.select>
            <div class="flex items-end gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.pages.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>
            <div class="flex items-end justify-end">
                <a href="{{ route('admin.pages.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.create_page') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.title') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.slug') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.published_at') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($pages as $page)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $page->title }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $page->slug }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="match($page->status){'published'=>'success','draft'=>'warning','archived'=>'muted',default=>'muted'}">
                            {{ __('admin.'.$page->status) }}
                        </x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $page->published_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
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
            {{ $pages->links() }}
        </div>
    </x-admin.card>
@endsection

