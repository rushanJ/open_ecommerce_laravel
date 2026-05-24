@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.create_api_token')" />

    <x-admin.card class="mt-6 space-y-4">
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
            <p class="font-semibold">{{ __('admin.copy_token_now') }}</p>
            <p class="mt-1 text-amber-900/90">{{ __('admin.api_token_created') }}</p>
        </div>

        <div>
            <label class="text-sm font-medium text-slate-700">{{ __('admin.token_value') }}</label>
            <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center">
                <code class="block w-full break-all rounded-lg bg-slate-900 px-3 py-2 text-xs text-green-400">{{ $plain }}</code>
                <button type="button" data-token="{{ e($plain) }}" class="shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50" onclick="navigator.clipboard.writeText(this.dataset.token)">
                    {{ __('admin.copy') }}
                </button>
            </div>
        </div>

        <a href="{{ route('admin.api-tokens.index') }}" class="inline-flex text-sm font-semibold text-[color:var(--mk-admin-primary)] hover:underline">{{ __('admin.back_to_tokens') }}</a>
    </x-admin.card>
@endsection
