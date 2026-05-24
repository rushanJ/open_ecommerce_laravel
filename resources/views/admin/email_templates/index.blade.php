@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.email_templates')" :subtitle="__('admin.email_templates_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.email-templates.index') }}" class="flex flex-wrap items-end gap-3">
            <x-admin.input name="q" :label="__('admin.search')" :value="request('q')" class="min-w-[200px]" />
            <x-admin.select name="status" :label="__('admin.status')" class="min-w-[140px]">
                <option value="">{{ __('admin.filter_all') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('admin.active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('admin.inactive') }}</option>
            </x-admin.select>
            <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
            <a href="{{ route('admin.email-templates.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            <div class="ml-auto">
                <a href="{{ route('admin.email-templates.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.create_email_template') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.template_code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.subject') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>
            @forelse($templates as $t)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-mono text-sm text-slate-800">{{ $t->code }}</td>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $t->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($t->subject, 60) }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$t->status === 'active' ? 'success' : 'neutral'">{{ $t->status }}</x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.email-templates.edit', $t) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
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
        <div class="mt-4">{{ $templates->links() }}</div>
    </x-admin.card>
@endsection
