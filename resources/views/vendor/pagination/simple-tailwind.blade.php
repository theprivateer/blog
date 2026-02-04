@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="split align-center">

        @if ($paginator->onFirstPage())
            <span class="text-muted">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn outline">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn outline">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="text-muted">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
