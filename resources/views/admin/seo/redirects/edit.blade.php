@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.edit_redirect')" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.seo.redirects.update', $redirect) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.seo.redirects.partials.form', ['redirect' => $redirect])

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                <a href="{{ route('admin.seo.redirects.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>
@endsection
