@if($paginator->hasPages())
<nav class="admin-pager" aria-label="Pagination">
    <p>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }} résultats</p>
    <div>
        @if($paginator->onFirstPage())<span aria-disabled="true">← Précédent</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Précédent</a>@endif
        <span>Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Suivant →</a>@else<span aria-disabled="true">Suivant →</span>@endif
    </div>
</nav>
@endif
