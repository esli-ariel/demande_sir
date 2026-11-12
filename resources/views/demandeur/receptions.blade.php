<x-app-layout>
<div class="admin-dashboard">
    <header class="admin-page-heading"><h1>Mes réceptions</h1><a class="admin-button admin-button-secondary" href="{{ route('archives.index') }}">Mes demandes clôturées</a></header>
    @include('admin.partials.errors')
    <section class="admin-panel"><div class="admin-panel-heading"><h2>Mes travaux à réceptionner</h2><span class="admin-count">{{ $demandes->total() }} {{ $demandes->total() === 1 ? 'dossier' : 'dossiers' }}</span></div>
        @if($demandes->isEmpty())<div class="admin-empty"><strong>Aucune demande en attente de votre réception.</strong></div>@endif
    </section>
    @foreach($demandes as $demande)
        <section class="admin-panel admin-spaced">
            <div class="admin-panel-heading"><div><span class="admin-reference">{{ $demande->numero_dma ?: 'Dossier #'.$demande->id }}</span><h2>{{ $demande->objet_modif ?: 'Objet non renseigné' }}</h2></div><a class="requester-section-link" href="{{ route('demandes.show', $demande) }}">Consulter le dossier →</a></div>
            <dl class="admin-detail-data"><div><dt>Travaux réalisés</dt><dd>{{ $demande->travaux_realises ?: 'Compte rendu non renseigné' }}</dd></div><div><dt>Fin des travaux</dt><dd>{{ $demande->date_fin ? \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') : 'Date non renseignée' }}</dd></div></dl>
        </section>
        @include('demandeur.partials.reception-form')
    @endforeach
    <div class="admin-pagination">{{ $demandes->links('pagination.admin') }}</div>
</div>
</x-app-layout>
