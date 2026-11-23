@if($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        <span>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        <div class="actions">
            @if($paginator->onFirstPage())
                <span class="button secondary disabled">Previous</span>
            @else
                <a class="button secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
            @endif
            <span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
            @if($paginator->hasMorePages())
                <a class="button secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="button secondary disabled">Next</span>
            @endif
        </div>
    </nav>
@endif
