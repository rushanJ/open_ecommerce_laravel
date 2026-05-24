@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.create_email_template')" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.email-templates.store') }}" class="space-y-6">
            @csrf
            @include('admin.email_templates.partials.form', ['template' => $template])
            <div class="flex gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                <a href="{{ route('admin.email-templates.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>
@endsection
