@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.notifications')" />

    <x-admin.card class="mt-6">
        @if($notifications->isNotEmpty())
            <form method="POST" action="{{ route('admin.notifications.read_all') }}" class="mb-4">
                @csrf
                @method('PATCH')
                <x-admin.button type="submit" variant="ghost" size="sm">{{ __('admin.mark_all_read') }}</x-admin.button>
            </form>
        @endif

        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($notifications as $n)
                <li class="flex flex-wrap items-start justify-between gap-3 py-4">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $n->title }}</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $n->message }}</p>
                        <p class="mt-2 text-xs text-gray-400">{{ $n->created_at?->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="shrink-0">
                        @if($n->read_at === null)
                            <form method="POST" action="{{ route('admin.notifications.read', $n) }}">
                                @csrf
                                @method('PATCH')
                                <x-admin.button type="submit" variant="ghost" size="sm">{{ __('admin.mark_as_read') }}</x-admin.button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">{{ __('admin.read') }}</span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="py-10 text-center text-sm text-gray-500">{{ __('admin.no_notifications') }}</li>
            @endforelse
        </ul>

        <div class="mt-4">{{ $notifications->links() }}</div>
    </x-admin.card>
@endsection
