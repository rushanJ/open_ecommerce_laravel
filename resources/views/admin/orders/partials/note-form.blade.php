@props([
    'order',
])

<x-admin.card :title="__('admin.add_note')">
    <form method="POST" action="{{ route('admin.orders.notes.store', $order) }}" class="space-y-4">
        @csrf

        <x-admin.textarea name="note" :label="__('admin.add_note')" rows="4" required>{{ old('note') }}</x-admin.textarea>

        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="is_customer_visible" value="1" class="rounded border-gray-300 text-[color:var(--mk-admin-primary)] focus:ring-[color:var(--mk-admin-primary)]"
                @checked(old('is_customer_visible', false))
            />
            {{ __('admin.customer_visible') }}
        </label>

        <x-admin.button type="submit" variant="primary" size="sm">{{ __('admin.save') }}</x-admin.button>
    </form>
</x-admin.card>

