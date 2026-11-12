@php($demandeurSpace = auth()->user()->hasRole('demandeur') && !auth()->user()->hasRole('admin'))
<x-app-layout>
<div class="admin-dashboard">
    @if($fromArchives ?? false)
        <a class="admin-back" href="{{ route('archives.index', $archiveFilters ?? []) }}">← Archives des demandes</a>
    @else
        <a class="admin-back" href="{{ route('demandes.index') }}">← Demandes</a>
    @endif
    <header class="admin-page-heading"><div><span class="admin-reference">{{ $demande->numero_dma ?: 'Dossier #'.$demande->id }}</span><h1>{{ $demande->objet_modif ?: 'Détail de ma demande' }}</h1></div><div class="admin-inline-actions"><span class="admin-badge admin-badge-{{ \App\Support\DmaStatus::group($demande->statut) }}">{{ \App\Support\DmaStatus::label($demande->statut) }}</span>@unless($demandeurSpace)@can('update', $demande)<a class="admin-button admin-button-orange" href="{{ route('demandes.edit', $demande) }}">Modifier</a>@endcan
@endunless</div></header>
    @if($fromArchives ?? false)
        <section class="admin-panel admin-spaced">
            <div class="admin-panel-heading"><h2>Dossier clôturé · lecture seule</h2><span class="admin-count">{{ $demande->closed_at?->format('d/m/Y à H:i') ?? 'Date de clôture non renseignée' }}</span></div>
        </section>
    @endif
    @include('demandes.partials.workflow')
    @include('admin.partials.errors')
    @unless($fromArchives ?? false)
        @include('staff.actions')
        @include('demandeur.partials.reception-form')
    @endunless
    @if($demandeurSpace)
        @can('submit', $demande)
        <section class="admin-panel requester-next-action"><div class="admin-panel-heading"><h2>Mon brouillon</h2></div><div class="requester-action-buttons"><a class="admin-button admin-button-secondary" href="{{ route('demandes.edit', $demande) }}">Reprendre le brouillon</a><form method="POST" action="{{ route('demandes.submit', $demande) }}" onsubmit="return confirm('Envoyer cette demande à l’exploitation ? Vous ne pourrez plus modifier ce dossier après l’envoi.');">@csrf<button class="admin-button admin-button-orange">Envoyer la demande</button></form><form method="POST" action="{{ route('demandes.destroy', $demande) }}" onsubmit="return confirm('Supprimer définitivement ce brouillon ?');">@csrf @method('DELETE')<button class="admin-text-danger">Supprimer le brouillon</button></form></div></section>
        @endcan

    @endif
    <div class="admin-detail-grid">
        <div class="admin-detail-main">
            <section class="admin-panel"><div class="admin-panel-heading"><h2>La modification</h2></div><dl class="admin-detail-data">@foreach(['motif' => 'Motif', 'situation_existante' => 'Situation existante', 'situation_souhaitee' => 'Situation souhaitée'] as $field => $label)<div><dt>{{ $label }}</dt><dd>{{ $demande->$field ?: 'Non renseigné' }}</dd></div>@endforeach</dl></section>
            <section class="admin-panel admin-spaced"><div class="admin-panel-heading"><h2>Travaux</h2></div><dl class="admin-detail-data"><div><dt>Travaux réalisés</dt><dd>{{ $demande->travaux_realises ?: 'Non renseignés' }}</dd></div><div><dt>Date de début</dt><dd>{{ $demande->date_debut ? \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') : '—' }}</dd></div><div><dt>Date de fin</dt><dd>{{ $demande->date_fin ? \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') : '—' }}</dd></div></dl></section>
            <section class="admin-panel admin-spaced"><div class="admin-panel-heading"><h2>Historique des décisions</h2></div><ol class="admin-timeline">@forelse($decisions as $decision)<li><div><strong>{{ $decision->structure?->nom ?? \App\Support\AdminRole::label($decision->role) }}</strong><span class="admin-badge">{{ match($decision->decision) {'accord' => 'Accord', 'refus' => 'Refus', default => 'En attente'} }}</span></div><p>{{ $decision->user?->name ?? $decision->visa ?? 'Intervenant non renseigné' }} · {{ $decision->date_validation ? \Carbon\Carbon::parse($decision->date_validation)->format('d/m/Y') : 'Date non renseignée' }}</p>@if($decision->commentaire)<blockquote>{{ $decision->commentaire }}</blockquote>@endif</li>@empty<li>Aucune décision enregistrée.</li>@endforelse</ol></section>
        </div>
        <aside>
            <section class="admin-panel"><div class="admin-panel-heading"><h2>Informations du dossier</h2></div><dl class="admin-detail-data"><div><dt>Demandeur</dt><dd>{{ $demande->user?->prenom }} {{ $demande->user?->name ?? $demande->nom }}</dd></div><div><dt>Création</dt><dd>{{ $demande->created_at?->format('d/m/Y') }}</dd></div>@foreach(['structure' => 'Structure', 'unite_concernee' => 'Unité concernée', 'repere' => 'Repère', 'fonction' => 'Fonction'] as $field => $label)<div><dt>{{ $label }}</dt><dd>{{ $demande->$field ?: '—' }}</dd></div>@endforeach<div><dt>Structures spécialisées</dt><dd>@forelse($demande->structuresSpecialisees as $structure)<span class="admin-badge">{{ $structure->nom }}</span>@empty Aucune @endforelse</dd></div></dl></section>
            <section class="admin-panel admin-spaced"><div class="admin-panel-heading"><h2>Pièces jointes</h2><span class="admin-count">{{ $demande->piecesJointes->count() }}</span></div><div class="admin-file-list">@forelse($demande->piecesJointes as $piece)<div><a href="{{ asset('storage/'.$piece->chemin_fichier) }}" target="_blank" rel="noopener">{{ basename($piece->chemin_fichier) }} ↗</a></div>@empty<p>Aucune pièce jointe.</p>@endforelse</div></section>
        </aside>
    </div>
</div>
</x-app-layout>
