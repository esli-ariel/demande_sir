<x-app-layout>
    <div class="admin-dashboard">
        <header class="admin-page-heading">
            <h1>Archives des demandes</h1>
            <span class="admin-badge">Consultation en lecture seule</span>
        </header>
        @include('admin.partials.errors')
        <section class="admin-panel">
            <div class="admin-panel-heading">
                <h2>Demandes définitivement clôturées</h2>
                <span class="admin-count">{{ $demandes->total() }} {{ $demandes->total() === 1 ? 'dossier' : 'dossiers' }}</span>
            </div>
            <form class="admin-filters archive-filters" method="GET" action="{{ route('archives.index') }}">
                <div class="admin-search">
                    <label for="archive-search">Rechercher</label>
                    <input id="archive-search" type="search" name="q" value="{{ $search }}" maxlength="150" placeholder="N° DMA, objet ou demandeur">
                </div>
                <div>
                    <label for="archive-unit">Unité concernée</label>
                    <input id="archive-unit" name="unite" value="{{ $unit }}" maxlength="255" placeholder="Toutes les unités">
                </div>
                <div>
                    <label for="archive-year">Année de clôture</label>
                    <select id="archive-year" name="annee">
                        <option value="">Toutes les années</option>
                        @if(!empty($filters['annee']) && !$years->contains((string) $filters['annee']))
                            <option value="{{ $filters['annee'] }}" selected>{{ $filters['annee'] }}</option>
                        @endif
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected((string) ($filters['annee'] ?? '') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="archive-from">Clôturée à partir du</label>
                    <input id="archive-from" type="date" name="du" value="{{ $filters['du'] ?? '' }}">
                </div>
                <div>
                    <label for="archive-to">Clôturée jusqu’au</label>
                    <input id="archive-to" type="date" name="au" value="{{ $filters['au'] ?? '' }}">
                </div>
                <div class="archive-filter-actions">
                    <button class="admin-button admin-button-primary" type="submit">Filtrer</button>
                    @if($search || $unit || !empty($filters['annee']) || !empty($filters['du']) || !empty($filters['au']))
                        <a class="admin-reset" href="{{ route('archives.index') }}">Réinitialiser</a>
                    @endif
                </div>
            </form>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <caption class="sr-only">Archives des demandes clôturées accessibles à votre compte</caption>
                    <thead>
                        <tr><th scope="col">Dossier / objet</th><th scope="col">Demandeur</th><th scope="col">Unité</th><th scope="col">Clôturée le</th><th scope="col">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($demandes as $demande)
                            <tr>
                                <td data-label="Dossier">
                                    <span class="admin-reference">{{ $demande->numero_dma ?: 'Dossier #'.$demande->id }}</span>
                                    <a class="admin-request-title" href="{{ route('archives.show', ['demande'=>$demande->id] + $filters) }}">{{ $demande->objet_modif ?: 'Objet non renseigné' }}</a>
                                </td>
                                <td data-label="Demandeur">{{ $demande->user ? trim($demande->user->prenom.' '.$demande->user->name) : $demande->nom }}</td>
                                <td data-label="Unité">{{ $demande->unite_concernee ?: 'Non renseignée' }}</td>
                                <td data-label="Clôturée le">{{ $demande->closed_at?->format('d/m/Y à H:i') ?? 'Date non renseignée' }}</td>
                                <td class="admin-row-action"><a href="{{ route('archives.show', ['demande'=>$demande->id] + $filters) }}" aria-label="Consulter {{ $demande->numero_dma ?: 'le dossier '.$demande->id }}">Consulter ↗</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="admin-empty"><strong>Aucune demande clôturée ne correspond à cette recherche.</strong></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $demandes->links('pagination.admin') }}</div>
        </section>
    </div>
</x-app-layout>
