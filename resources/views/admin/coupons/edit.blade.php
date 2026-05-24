@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.edit_coupon')" :subtitle="$coupon->code" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.coupons.partials.form')

            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                    <a href="{{ route('admin.coupons.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
                </div>

                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}');">
                    @csrf
                    @method('DELETE')
                    <x-admin.button type="submit" variant="danger">{{ __('admin.delete') }}</x-admin.button>
                </form>
            </div>
        </form>
    </x-admin.card>
@endsection

