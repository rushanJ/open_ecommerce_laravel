@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.webhooks')" :subtitle="__('admin.webhooks_subtitle')" />

    <x-admin.card class="mt-6">
        <div class="flex justify-end">
            <a href="{{ route('admin.webhooks.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                {{ __('admin.create_webhook') }}
            </a>
        </div>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.url') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.events') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($endpoints as $endpoint)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $endpoint->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-700 break-all">{{ $endpoint->url }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">{{ implode(', ', $endpoint->events ?? []) }}</td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$endpoint->status === 'active' ? 'success' : 'muted'">{{ ucfirst($endpoint->status) }}</x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.webhooks.deliveries', $endpoint) }}" class="text-sm font-semibold text-slate-700 hover:underline">{{ __('admin.webhook_deliveries') }}</a>
                        <a href="{{ route('admin.webhooks.edit', $endpoint) }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.edit') }}</a>
                        <form method="POST" action="{{ route('admin.webhooks.destroy', $endpoint) }}" class="inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}');">
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
            {{ $endpoints->links() }}
        </div>
    </x-admin.card>
@endsection
