@if ($paginator->hasPages())
  <nav class="pager" aria-label="Pagination">
    @if ($paginator->onFirstPage())
      <span class="meta">Previous</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}">Previous</a>
    @endif
    <span class="meta">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}">Next</a>
    @else
      <span class="meta">Next</span>
    @endif
  </nav>
@endif
