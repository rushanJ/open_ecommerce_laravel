@php
    $selected = collect(old('category_ids', $product->exists ? $product->categories->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
@endphp

<fieldset class="space-y-2">
    <legend class="sr-only">{{ __('admin.categories') }}</legend>
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $cat)
            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                <input
                    type="checkbox"
                    name="category_ids[]"
                    value="{{ $cat->id }}"
                    class="rounded border-gray-300 text-[color:var(--mk-admin-primary)] focus:ring-[color:var(--mk-admin-primary)] dark:border-gray-600 dark:bg-gray-900"
                    @checked(in_array((int) $cat->id, $selected, true))
                >
                <span class="text-gray-800 dark:text-gray-200">{{ $cat->name }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
