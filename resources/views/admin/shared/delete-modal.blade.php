{{--
  Lightweight delete confirmation block (no heavy JS).
  Expects: $title, $message, $action (URL), optional $cancelUrl
--}}
@props([
    'title',
    'message',
    'action',
    'cancelUrl' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-red-200 bg-red-50/80 p-4 dark:border-red-900/60 dark:bg-red-950/30']) }}>
    <h3 class="text-sm font-semibold text-red-900 dark:text-red-200">{{ $title }}</h3>
    <p class="mt-1 text-sm text-red-800 dark:text-red-100/90">{{ $message }}</p>
    <div class="mt-4 flex flex-wrap gap-2">
        @if ($cancelUrl)
            <x-admin.button type="link" href="{{ $cancelUrl }}" variant="secondary" size="sm">
                {{ __('admin.cancel') }}
            </x-admin.button>
        @endif
        <form action="{{ $action }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <x-admin.button type="submit" variant="danger" size="sm">
                {{ __('admin.delete') }}
            </x-admin.button>
        </form>
    </div>
</div>
