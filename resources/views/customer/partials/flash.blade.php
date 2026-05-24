@if (session('success'))
    <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8" role="status">
        <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</p>
    </div>
@endif
@if ($errors->any())
    <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8" role="alert">
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
@if (session('error'))
    <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8" role="alert">
        <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</p>
    </div>
@endif
@if (session('status'))
    <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8" role="status">
        <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800">{{ session('status') }}</p>
    </div>
@endif
