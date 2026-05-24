@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="mt-10 flex justify-center" aria-label="Pagination">
        {{ $paginator->withQueryString()->links() }}
    </nav>
@endif
