@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.create_api_token')" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.api-tokens.store') }}" class="space-y-6">
            @csrf
            <x-admin.input name="name" :label="__('admin.name')" :value="old('name')" required />

            <div>
                <p class="text-sm font-medium text-slate-800">{{ __('admin.abilities') }}</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach($abilityOptions as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="abilities[]" value="{{ $key }}" class="rounded border-slate-300" @checked(in_array($key, old('abilities', []), true))>
                            <span class="font-mono text-xs">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('abilities')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-admin.input type="datetime-local" name="expires_at" :label="__('admin.expires_at')" :value="old('expires_at')" />

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                <a href="{{ route('admin.api-tokens.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>
@endsection
