@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.edit_email_template')" :subtitle="$template->code" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.email-templates.update', $template) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.email_templates.partials.form', ['template' => $template])
            <div class="flex gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                <a href="{{ route('admin.email-templates.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>

    @if(! \Illuminate\Support\Str::startsWith($template->code, ['order_', 'payment_']))
        <x-admin.card class="mt-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('admin.danger_zone') }}</p>
            <form method="POST" action="{{ route('admin.email-templates.destroy', $template) }}" class="mt-4" onsubmit="return confirm('{{ __('admin.confirm_delete') }}');">
                @csrf
                @method('DELETE')
                <x-admin.button type="submit" variant="danger">{{ __('admin.delete') }}</x-admin.button>
            </form>
        </x-admin.card>
    @endif
@endsection
