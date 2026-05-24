@php
    /** @var \App\Models\EmailTemplate $template */
    $varList = old('variables', $template->variables ?? []);
    if (! is_array($varList)) {
        $varList = [];
    }
    if ($varList === []) {
        $varList = [''];
    }
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <x-admin.input name="code" :label="__('admin.template_code')" :value="old('code', $template->code)" required />
    <x-admin.input name="name" :label="__('admin.name')" :value="old('name', $template->name)" required />
    <x-admin.input name="subject" :label="__('admin.subject')" :value="old('subject', $template->subject)" required class="md:col-span-2" />
    <x-admin.select name="status" :label="__('admin.status')" required class="md:col-span-2">
        <option value="active" @selected(old('status', $template->status) === 'active')>{{ __('admin.active') }}</option>
        <option value="inactive" @selected(old('status', $template->status) === 'inactive')>{{ __('admin.inactive') }}</option>
    </x-admin.select>
</div>

<div class="mt-6 space-y-3">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.variables') }}</p>
    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.email_template_variables_help') }}</p>
    @foreach($varList as $var)
        <x-admin.input name="variables[]" :value="$var" placeholder="order_number" />
    @endforeach
</div>

<div class="mt-6">
    <x-admin.textarea name="body_html" :label="__('admin.body_html')" rows="12" required :value="old('body_html', $template->body_html)" />
</div>

<div class="mt-6">
    <x-admin.textarea name="body_text" :label="__('admin.body_text')" rows="6" :value="old('body_text', $template->body_text)" />
</div>
