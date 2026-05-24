@php
    $selected = old('events', $endpoint->events ?? []);
@endphp

<x-admin.input name="name" :label="__('admin.name')" :value="old('name', $endpoint->name)" required />
<x-admin.input name="url" type="url" :label="__('admin.url')" :value="old('url', $endpoint->url)" required />

<div>
    <p class="text-sm font-medium text-slate-800">{{ __('admin.events') }}</p>
    <p class="mt-1 text-xs text-slate-500">{{ __('admin.webhook_events_help') }}</p>
    <div class="mt-2 grid gap-2 sm:grid-cols-2">
        @foreach($availableEvents as $event)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="events[]" value="{{ $event }}" class="rounded border-slate-300" @checked(in_array($event, $selected, true))>
                <span class="font-mono text-xs">{{ $event }}</span>
            </label>
        @endforeach
    </div>
    @error('events')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<x-admin.input name="secret" type="password" :label="__('admin.webhook_secret')" :placeholder="__('admin.webhook_secret_placeholder')" />
<p class="text-xs text-slate-500">{{ __('admin.webhook_secret_hint') }}</p>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('admin.status') }}</label>
    <select name="status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[color:var(--mk-admin-primary)] focus:ring-2 focus:ring-[color:var(--mk-admin-primary)]">
        <option value="active" @selected(old('status', $endpoint->status) === 'active')>{{ __('admin.active') }}</option>
        <option value="inactive" @selected(old('status', $endpoint->status) === 'inactive')>{{ __('admin.inactive') }}</option>
    </select>
    @error('status')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
