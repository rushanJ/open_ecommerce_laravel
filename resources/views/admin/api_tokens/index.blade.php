@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.api_tokens')" :subtitle="__('admin.api_tokens_subtitle')" />

    <x-admin.card class="mt-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-600">{{ __('admin.api_tokens_help') }}</p>
            <a href="{{ route('admin.api-tokens.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                {{ __('admin.create_api_token') }}
            </a>
        </div>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.abilities') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.last_used_at') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('admin.expires_at') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('admin.actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse($tokens as $token)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $token->name }}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        @if(empty($token->abilities))
                            <span class="text-slate-400">—</span>
                        @else
                            <span class="font-mono text-xs">{{ implode(', ', $token->abilities) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-admin.badge :variant="$token->status === 'active' ? 'success' : 'muted'">{{ ucfirst($token->status) }}</x-admin.badge>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $token->last_used_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $token->expires_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($token->status === 'active')
                            <form method="POST" action="{{ route('admin.api-tokens.revoke', $token) }}" class="inline" onsubmit="return confirm('{{ __('admin.confirm_revoke_token') }}');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">{{ __('admin.revoke') }}</button>
                            </form>
                        @endif
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
            {{ $tokens->links() }}
        </div>
    </x-admin.card>
@endsection
