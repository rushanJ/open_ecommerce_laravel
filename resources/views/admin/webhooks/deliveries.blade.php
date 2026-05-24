@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.webhook_deliveries')" :subtitle="$endpoint->name" />

    <x-admin.card class="mt-6">
        <a href="{{ route('admin.webhooks.index') }}" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.back') }}</a>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.event_type') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.response_status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.attempts') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.delivered_at') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.failed_at') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($deliveries as $delivery)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-mono text-xs text-slate-800">{{ $delivery->event_type }}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">{{ $delivery->response_status ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">{{ $delivery->attempts }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $delivery->delivered_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $delivery->failed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.webhook-deliveries.retry', $delivery) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.retry_delivery') }}</button>
                        </form>
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
            {{ $deliveries->links() }}
        </div>
    </x-admin.card>
@endsection
