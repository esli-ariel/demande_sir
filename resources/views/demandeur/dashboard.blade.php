<x-app-layout>
<div class="admin-dashboard requester-dashboard">
    <header class="admin-page-heading"><h1>Mon espace demandeur</h1><a class="admin-button admin-button-orange" href="{{ route('demandes.create') }}">Nouvelle DMA ＋</a></header>
    <div class="admin-section-caption"><h2>Mes demandes en un regard</h2><span>Toutes périodes · {{ $total }} dossiers</span></div>
    <div class="admin-kpis">
        @foreach([
            ['Total de mes demandes', $total, route('demandes.index'), 'M7 3h7l4 4v14H6V3h1Zm7 0v5h5M9 12h6M9 16h6'],
            ['Brouillons à compléter', $summary['brouillons'], route('demandes.index', ['statut' => 'brouillon']), 'm4 16 12-12 4 4-12 12H4v-4Z'],
            ['Dans le circuit', $active, '#repartition', 'M4 7h15m-4-4 4 4-4 4M20 17H5m4-4-4 4 4 4'],
            ['Réceptions attendues', $receipts, route('demandeur.receptions'), 'm7 12 3 3 7-7M20 12a8 8 0 1 1-8-8']
        ] as [$label, $value, $url, $path])
        <a class="admin-kpi" href="{{ $url }}"><div class="admin-kpi-top"><span class="admin-kpi-label">{{ $label }}</span><span class="admin-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg></span></div><strong>{{ $value }}</strong></a>
        @endforeach
    </div>
    <section class="admin-panel"><div class="admin-panel-heading"><h2>Mes dernières demandes</h2><a class="requester-section-link" href="{{ route('demandes.index') }}">Tout voir →</a></div>@include('demandeur.table', ['emptyMessage' => 'Vous n’avez pas encore créé de demande.'])</section>
    <div class="admin-insights">
        <section class="admin-panel" id="repartition"><div class="admin-panel-heading"><h2>Avancement de mes demandes</h2></div><div class="admin-distribution">@foreach(\App\Support\DmaStatus::GROUP_LABELS as $key => $label)@if($key !== 'autres' || $summary[$key])<div class="admin-distribution-row"><div><span>{{ $label }}</span><strong>{{ $summary[$key] }}</strong></div><div class="admin-track" aria-hidden="true"><span class="admin-bar-{{ $key }}" style="width:{{ $total ? $summary[$key] / $total * 100 : 0 }}%"></span></div></div>@endif @endforeach</div></section>
        <section class="admin-panel"><div class="admin-panel-heading"><h2>Mes actions</h2></div><div class="requester-action-list"><a href="{{ route('demandes.index', ['statut' => 'brouillon']) }}"><span>Reprendre mes brouillons</span><strong>{{ $summary['brouillons'] }} →</strong></a><a href="{{ route('demandeur.receptions') }}"><span>Consulter les réceptions attendues</span><strong>→</strong></a><a href="{{ route('notifications.index') }}"><span>Lire mes notifications</span><strong>{{ auth()->user()->unreadNotifications->count() }} →</strong></a><a href="{{ route('demandes.index') }}"><span>Retrouver un dossier</span><strong>→</strong></a></div></section>
    </div>
</div>
</x-app-layout>
