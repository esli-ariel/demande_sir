<x-app-layout>
<div class="admin-dashboard">
    <header class="admin-page-heading"><h1>Mes réceptions</h1><a class="admin-button admin-button-secondary" href="{{ route('demandes.index') }}">Toutes mes demandes</a></header>
    <section class="admin-panel"><div class="admin-panel-heading"><h2>Clôture attendue par la réception</h2><span class="admin-count">{{ $demandes->total() }} dossiers</span></div>@include('demandeur.table', ['emptyMessage' => 'Aucune demande en attente de réception.', 'hideEmptyAction' => true])<div class="admin-pagination">{{ $demandes->links('pagination.admin') }}</div></section>
</div>
</x-app-layout>
