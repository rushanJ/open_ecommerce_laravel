@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.seo_redirects')" :subtitle="__('admin.seo_redirects_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.seo.redirects.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="min-w-0 flex-1">
                <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />
            </div>
            <div class="flex items-center gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.seo.redirects.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>
            <div class="sm:ml-auto">
                <a href="{{ route('admin.seo.redirects.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.create_redirect') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.from_url') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.to_url') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status_code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($redirects as $seoRedirect)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-mono text-sm text-slate-900">{{ $seoRedirect->from_url }}</td>
                    <td class="px-4 py-3 font-mono text-sm text-slate-700">{{ $seoRedirect->to_url }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $seoRedirect->status_code }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$seoRedirect->status === 'active' ? 'success' : 'muted'">{{ ucfirst($seoRedirect->status) }}</x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.seo.redirects.edit', $seoRedirect) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                        <form method="POST" action="{{ route('admin.seo.redirects.destroy', $seoRedirect) }}" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">{{ __('admin.delete') }}</button>
                        </form>
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
            {{ $redirects->links() }}
        </div>
    </x-admin.card>
@endsection
