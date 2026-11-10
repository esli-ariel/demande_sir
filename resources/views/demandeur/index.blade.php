<x-app-layout>
<div class="admin-dashboard">
    <header class="admin-page-heading"><h1>Mes demandes</h1><a class="admin-button admin-button-orange" href="{{ route('demandes.create') }}">Nouvelle DMA ＋</a></header>
    <section class="admin-panel"><div class="admin-panel-heading"><h2>Mes dossiers</h2><span class="admin-count">{{ $demandes->total() }} résultats</span></div>
        <form class="admin-filters" method="GET" action="{{ route('demandes.index') }}"><div class="admin-search"><label for="requester-search">Rechercher</label><input id="requester-search" name="search" type="search" value="{{ $search }}" maxlength="150" placeholder="N° DMA, objet ou motif"></div><div><label for="requester-status">Étape du circuit</label><select id="requester-status" name="statut"><option value="">Toutes les étapes</option>@foreach($statuses as $value)<option value="{{ $value }}" @selected($status === $value)>{{ \App\Support\DmaStatus::label($value) }}</option>@endforeach</select></div><button class="admin-button admin-button-primary">Filtrer</button>@if($search || $status)<a class="admin-reset" href="{{ route('demandes.index') }}">Réinitialiser</a>@endif</form>
        @include('demandeur.table')
        <div class="admin-pagination">{{ $demandes->links('pagination.admin') }}</div>
    </section>
</div>
</x-app-layout>
