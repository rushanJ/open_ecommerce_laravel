@extends('admin.layouts.app')

@section('title', __('admin.adjust_inventory').' — '.config('app.name'))

@section('breadcrumb', __('admin.adjust_inventory'))

@section('content')
    <x-admin.page-header :title="__('admin.adjust_inventory')">
        <x-slot:actions>
            <x-admin.button type="link" href="{{ route('admin.inventory.index') }}" variant="ghost" size="sm">{{ __('admin.back') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.inventory.adjust') }}" class="space-y-4">
            @csrf
            <x-admin.select name="warehouse_id" :label="__('admin.warehouse')" required>
                <option value="">{{ __('admin.select_option') }}</option>
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>{{ $w->name }} ({{ $w->code }})</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="product_id" :label="__('admin.product')" required>
                <option value="">{{ __('admin.select_option') }}</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="variant_id" :label="__('admin.variant')">
                <option value="">{{ __('admin.none') }} ({{ __('admin.simple') }})</option>
                @foreach ($products as $p)
                    @foreach ($p->variants as $v)
                        <option value="{{ $v->id }}" data-product="{{ $p->id }}" @selected(old('variant_id') == $v->id)>
                            {{ $p->name }} — {{ $v->name ?? $v->sku }}
                        </option>
                    @endforeach
                @endforeach
            </x-admin.select>

            <x-admin.select name="type" :label="__('admin.movement_type')" required>
                <option value="purchase" @selected(old('type') === 'purchase')>{{ __('admin.purchase') }}</option>
                <option value="return" @selected(old('type') === 'return')>{{ __('admin.return') }}</option>
                <option value="adjustment" @selected(old('type') === 'adjustment')>{{ __('admin.adjustment') }}</option>
            </x-admin.select>

            <x-admin.input name="quantity" type="number" step="0.0001" :label="__('admin.quantity')" :value="old('quantity')" required />
            <x-admin.textarea name="note" :label="__('admin.note')" :value="old('note')" />

            <div class="flex flex-wrap gap-2 pt-2">
                <x-admin.button type="submit" variant="primary">{{ __('admin.save') }}</x-admin.button>
                <x-admin.button type="link" href="{{ route('admin.inventory.index') }}" variant="secondary">{{ __('admin.cancel') }}</x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
