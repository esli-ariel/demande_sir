<x-app-layout>
<div class="admin-dashboard">
    <header class="admin-page-heading"><h1>{{ $workspace['label'] }} · demandes</h1><a class="admin-button admin-button-secondary" href="{{ route('dashboard') }}">Tableau de bord</a></header>
    <section class="admin-panel"><div class="admin-panel-heading"><h2>{{ ($workspace['readonly'] ?? false) ? 'Suivi des DMA après exploitation' : 'Ma file de travail' }}</h2><span class="admin-count">{{ $demandes->total() }} résultats</span></div>
        <form class="admin-filters" method="GET" action="{{ route('staff.demandes') }}"><div class="admin-search"><label for="staff-search">Rechercher</label><input id="staff-search" type="search" name="q" value="{{ $search }}" maxlength="150" placeholder="N° DMA, objet ou demandeur"></div><div><label for="staff-scope">Afficher</label><select id="staff-scope" name="vue">@foreach(['a_traiter' => (($workspace['readonly'] ?? false) ? 'Dossiers en cours' : 'À traiter'), 'mes_decisions' => (($workspace['readonly'] ?? false) ? 'Dossiers terminés ou refusés' : 'Mes décisions'), 'tous' => 'Tous les dossiers accessibles', 'cloturees'=>'DMA clôturées', 'refusees'=>'Dossiers refusés'] as $key=>$label)<option value="{{ $key }}" @selected($scope === $key)>{{ $label }}</option>@endforeach</select></div><button class="admin-button admin-button-primary">Filtrer</button>@if($search || $scope !== 'a_traiter')<a class="admin-reset" href="{{ route('staff.demandes') }}">Réinitialiser</a>@endif</form>
        @include('staff.table')<div class="admin-pagination">{{ $demandes->links('pagination.admin') }}</div>
    </section>
</div>
</x-app-layout>
