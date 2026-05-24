@props([
    'order',
])

@php
    /** @var \App\Models\Order $order */
@endphp

<x-admin.card :title="__('admin.update_status')">
    <form method="POST" action="{{ route('admin.orders.status.update', $order) }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <x-admin.select name="status" :label="__('admin.order_status')" required>
            @foreach (['pending','processing','confirmed','packed','shipped','delivered','cancelled','failed','refunded'] as $st)
                <option value="{{ $st }}" @selected(old('status') === $st) @disabled(! $order->canTransitionTo($st))>
                    {{ __('admin.'.$st) }}
                </option>
            @endforeach
        </x-admin.select>

        <x-admin.textarea name="note" :label="__('admin.reason')" rows="3">{{ old('note') }}</x-admin.textarea>

        <x-admin.button type="submit" variant="primary" size="sm">{{ __('admin.save') }}</x-admin.button>
    </form>
</x-admin.card>

