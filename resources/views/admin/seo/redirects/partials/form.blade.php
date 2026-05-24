@php
    /** @var \App\Models\SeoRedirect $redirect */
@endphp

<x-admin.input
    name="from_url"
    :label="__('admin.from_url')"
    :value="old('from_url', $redirect->from_url)"
    required
    autocomplete="off"
/>

<x-admin.input
    name="to_url"
    :label="__('admin.to_url')"
    :value="old('to_url', $redirect->to_url)"
    required
    autocomplete="off"
/>

<x-admin.select name="status_code" :label="__('admin.status_code')" required>
    @foreach ([301 => '301 — Permanent', 302 => '302 — Temporary'] as $code => $label)
        <option value="{{ $code }}" @selected((int) old('status_code', $redirect->status_code) === $code)>{{ $label }}</option>
    @endforeach
</x-admin.select>

<x-admin.select name="status" :label="__('admin.status')" required>
    @foreach (['active', 'inactive'] as $s)
        <option value="{{ $s }}" @selected(old('status', $redirect->status) === $s)>{{ ucfirst($s) }}</option>
    @endforeach
</x-admin.select>
