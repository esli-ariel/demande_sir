<x-app-layout>
<div @class(['admin-dashboard', 'staff-completion-kpis'=>in_array($role,['service_technique','reception'])])>
    <header class="admin-page-heading"><h1>{{ $workspace['label'] }}</h1><a class="admin-button admin-button-orange" href="{{ route('staff.demandes') }}">{{ ($workspace['readonly'] ?? false) ? 'Consulter les dossiers →' : 'Ouvrir ma file de travail →' }}</a></header>
    <div class="admin-section-caption"><h2>Mon activité</h2></div>
    <div class="admin-kpis">
        @foreach([['Dossiers accessibles', $total, 'tous'], [($workspace['readonly'] ?? false) ? 'Dossiers en cours' : ($role === 'reception' ? 'À clôturer' : 'À traiter'), $pending, 'a_traiter'], [in_array($role,['chef_structure','reception']) ? 'DMA clôturées' : ($role === 'service_technique' ? 'Travaux en cours' : 'Mes avis rendus'), $processed, in_array($role,['chef_structure','reception']) ? 'cloturees' : ($role === 'service_technique' ? 'a_traiter' : 'mes_decisions')], [$role === 'reception' ? 'Mes clôtures' : ($role === 'chef_structure' ? 'Dossiers refusés' : ($role === 'service_technique' ? 'Travaux terminés' : 'Mes refus')), $refused, $role === 'chef_structure' ? 'refusees' : ($role === 'service_technique' ? 'tous' : 'mes_decisions')]] as [$label,$value,$scope])
        @php($icon = match($loop->index) {0 => 'M7 3h7l4 4v14H6V3h1Zm7 0v5h5M9 12h6M9 16h6', 1 => 'M4 7h15m-4-4 4 4-4 4M20 17H5m4-4-4 4 4 4', 2 => 'm7 12 3 3 7-7M20 12a8 8 0 1 1-8-8', default => in_array($role,['service_technique','reception']) ? 'm7 12 3 3 7-7M20 12a8 8 0 1 1-8-8' : 'm9 9 6 6m0-6-6 6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0'})
        <a class="admin-kpi" href="{{ route('staff.demandes', ['vue' => $scope]) }}"><div class="admin-kpi-top"><span class="admin-kpi-label">{{ $label }}</span><span class="admin-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg></span></div><strong>{{ $value }}</strong></a>
        @endforeach
    </div>
    <section class="admin-panel"><div class="admin-panel-heading"><h2>{{ ($workspace['readonly'] ?? false) ? 'Dossiers en cours de suivi' : 'Les dossiers à traiter' }}</h2><a class="requester-section-link" href="{{ route('staff.demandes') }}">Tout voir →</a></div>@include('staff.table')</section>
</div>
</x-app-layout>
