@php
    $paginationAttributes = isset($attributes) && $attributes instanceof \Illuminate\View\ComponentAttributeBag
        ? $attributes
        : new \Illuminate\View\ComponentAttributeBag;
@endphp
@isset($paginator)
    @if ($paginator->hasPages())
        <div {{ $paginationAttributes->merge(['class' => 'mt-4 overflow-x-auto']) }} aria-label="{{ __('admin.pagination_nav') }}">
            {{ $paginator->withQueryString()->links() }}
        </div>
    @endif
@endisset
