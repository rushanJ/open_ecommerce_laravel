@props([
    'viewRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'canView' => true,
    'canEdit' => true,
    'canDelete' => true,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-end gap-1']) }}>
    @if ($canView && $viewRoute)
        <x-admin.button type="link" href="{{ $viewRoute }}" variant="ghost" size="sm">
            {{ __('admin.view') }}
        </x-admin.button>
    @endif
    @if ($canEdit && $editRoute)
        <x-admin.button type="link" href="{{ $editRoute }}" variant="secondary" size="sm">
            {{ __('admin.edit') }}
        </x-admin.button>
    @endif
    @if ($canDelete && $deleteRoute)
        <form action="{{ $deleteRoute }}" method="POST" class="inline" onsubmit="return confirm(@json(__('admin.delete_warning')))">
            @csrf
            @method('DELETE')
            <x-admin.button type="submit" variant="danger" size="sm" class="!min-h-0">
                {{ __('admin.delete') }}
            </x-admin.button>
        </form>
    @endif
</div>
