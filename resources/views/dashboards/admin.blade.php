<x-app-layout>
    <div class="admin-dashboard">
        <header class="admin-page-heading">
            <div><h1>Tableau de bord administrateur</h1></div>
            <a class="admin-button admin-button-orange" href="{{ route('users.index') }}">Gérer les utilisateurs <span aria-hidden="true">↗</span></a>
        </header>

        <div class="admin-section-caption"><h2>Activité des demandes</h2><span>Toutes périodes · {{ number_format($total, 0, ',', ' ') }} demandes</span></div>
        <div class="admin-kpis">
            <a class="admin-kpi" href="{{ route('dashboard') }}#demandes"><div class="admin-kpi-top"><span class="admin-kpi-label">Total des demandes</span><span class="admin-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l4 4v14H6V3h1Zm7 0v5h5M9 12h6M9 16h6"/></svg></span></div><strong>{{ $total }}</strong></a>
            <a class="admin-kpi admin-kpi-accent" href="#repartition"><div class="admin-kpi-top"><span class="admin-kpi-label">Dans le circuit</span><span class="admin-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h15m-4-4 4 4-4 4M20 17H5m4-4-4 4 4 4"/></svg></span></div><strong>{{ $active }}</strong></a>
            <a class="admin-kpi" href="{{ route('dashboard', ['etat' => 'cloturees']) }}#demandes"><div class="admin-kpi-top"><span class="admin-kpi-label">Clôturées et réceptionnées</span><span class="admin-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m7 12 3 3 7-7M20 12a8 8 0 1 1-8-8"/></svg></span></div><strong>{{ $summary['cloturees'] }}</strong></a>
            <a class="admin-kpi" href="{{ route('dashboard', ['etat' => 'refusees']) }}#demandes"><div class="admin-kpi-top"><span class="admin-kpi-label">Demandes refusées</span><span class="admin-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m9 9 6 6m0-6-6 6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0"/></svg></span></div><strong>{{ $summary['refusees'] }}</strong></a>
        </div>

        <section class="admin-panel admin-requests" id="demandes" aria-labelledby="requests-title">
            <div class="admin-panel-heading"><div><h2 id="requests-title">Les demandes</h2></div><span class="admin-count">{{ $demandes->total() }} résultat{{ $demandes->total() > 1 ? 's' : '' }}</span></div>
            <form class="admin-filters" method="GET" action="{{ url()->current() }}#demandes">
                <div class="admin-search"><label for="admin-search">Rechercher une demande</label><input id="admin-search" name="q" value="{{ $search }}" maxlength="150" placeholder="N° DMA, objet ou demandeur…" type="search"></div>
                <div><label for="admin-category">Étape du circuit</label><select id="admin-category" name="etat"><option value="">Toutes les étapes</option>@foreach(\App\Support\DmaStatus::GROUP_LABELS as $key => $label)<option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>@endforeach</select></div>
                <button class="admin-button admin-button-primary" type="submit">Filtrer</button>
                @if($search !== '' || $category !== '')<a class="admin-reset" href="{{ url()->current() }}#demandes">Réinitialiser</a>@endif
            </form>
            <div class="admin-table-wrap">
                <table class="admin-table"><caption class="sr-only">Demandes DMA, de la plus récente à la plus ancienne</caption><thead><tr><th scope="col">Dossier / objet</th><th scope="col">Demandeur</th><th scope="col">Étape actuelle</th><th scope="col">Créée le</th><th scope="col"><span class="sr-only">Action</span></th></tr></thead><tbody>
                @forelse($demandes as $demande)
                    <tr><td data-label="Dossier"><span class="admin-reference">{{ $demande->numero_dma ?: 'Dossier #'.$demande->id }}</span><a class="admin-request-title" href="{{ route('demandes.show', $demande) }}">{{ $demande->objet_modif ?: 'Objet non renseigné' }}</a></td><td data-label="Demandeur">{{ $demande->user?->name ?? $demande->nom ?? 'Non renseigné' }}</td><td data-label="Étape"><span class="admin-badge admin-badge-{{ \App\Support\DmaStatus::group($demande->statut) }}">{{ \App\Support\DmaStatus::label($demande->statut) }}</span></td><td data-label="Créée le"><time datetime="{{ $demande->created_at?->toDateString() }}">{{ $demande->created_at?->format('d/m/Y') ?? '—' }}</time></td><td class="admin-row-action"><a href="{{ route('demandes.show', $demande) }}" aria-label="Consulter le dossier {{ $demande->numero_dma ?: '#'.$demande->id }}">Consulter <span aria-hidden="true">↗</span></a></td></tr>
                @empty
                    <tr><td colspan="5"><div class="admin-empty"><strong>{{ $search !== '' || $category !== '' ? 'Aucune demande ne correspond à ces filtres.' : 'Aucune demande pour le moment.' }}</strong><p>{{ $search !== '' || $category !== '' ? 'Essayez un autre mot-clé ou une autre étape.' : 'Les demandes apparaîtront ici dès leur création.' }}</p></div></td></tr>
                @endforelse
                </tbody></table>
            </div>
            <div class="admin-pagination">{{ $demandes->fragment('demandes')->links('pagination.admin') }}</div>
        </section>

        <div class="admin-insights">
            <section class="admin-panel" id="repartition" aria-labelledby="distribution-title"><div class="admin-panel-heading"><div><h2 id="distribution-title">Où en sont les demandes ?</h2></div></div><div class="admin-distribution">
                @foreach(\App\Support\DmaStatus::GROUP_LABELS as $key => $label)
                    @if($key !== 'autres' || $summary[$key] > 0)
                    <a href="{{ route('dashboard', ['etat' => $key]) }}#demandes" class="admin-distribution-row"><div><span>{{ $label }}</span><strong>{{ $summary[$key] }}</strong></div><div class="admin-track" aria-hidden="true"><span class="admin-bar-{{ $key }}" style="width: {{ $total > 0 ? $summary[$key] / $total * 100 : 0 }}%"></span></div></a>
                    @endif
                @endforeach
            </div></section>
            <section class="admin-panel" aria-labelledby="monthly-title"><div class="admin-panel-heading"><div><h2 id="monthly-title">Demandes créées en {{ $year }}</h2></div></div><div class="admin-monthly" role="img" aria-label="Demandes créées en {{ $year }} : {{ collect($months)->map(fn ($month, $index) => $month.' '.$monthlyCounts[$index])->join(', ') }}">
                @foreach($months as $index => $month)<div class="admin-month"><span class="admin-month-value">{{ $monthlyCounts[$index] }}</span><div class="admin-month-track"><div class="admin-month-fill" style="height: {{ $monthlyCounts->max() > 0 ? $monthlyCounts[$index] / $monthlyCounts->max() * 100 : 0 }}%"></div></div><span>{{ $month }}</span></div>@endforeach
            </div></section>
        </div>
        <footer class="admin-summary-footer"><span><strong>{{ $usersCount }}</strong> utilisateurs enregistrés</span><a href="{{ route('users.index') }}">Consulter les utilisateurs <span aria-hidden="true">→</span></a></footer>
    </div>
</x-app-layout>
