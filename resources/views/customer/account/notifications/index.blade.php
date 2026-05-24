@extends('customer.account.layouts.app')

@section('title', __('customer.notifications'))

@section('account_content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-bold text-slate-900">{{ __('customer.notifications') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('customer.notifications_subtitle') }}</p>

        <ul class="mt-6 divide-y divide-slate-100">
            @forelse($notifications as $n)
                <li class="flex flex-wrap items-start justify-between gap-3 py-4">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-slate-900">{{ $n->title }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $n->message }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ $n->created_at?->format('Y-m-d H:i') }}</p>
                    </div>
                    @if($n->read_at === null)
                        <form method="POST" action="{{ route('customer.account.notifications.read', $n) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('customer.mark_as_read') }}</button>
                        </form>
                    @endif
                </li>
            @empty
                <li class="py-10 text-center text-sm text-slate-500">{{ __('customer.no_notifications') }}</li>
            @endforelse
        </ul>

        <div class="mt-6">{{ $notifications->links() }}</div>
    </div>
@endsection
