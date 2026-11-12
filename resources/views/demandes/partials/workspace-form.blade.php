@php($demandeurSpace = auth()->user()->hasRole('demandeur') && !auth()->user()->hasRole('admin'))
@php($editing = isset($demande))
<x-app-layout>
<div class="admin-dashboard admin-form-page">
    <a class="admin-back" href="{{ route('demandes.index') }}">← Demandes</a>
    <header class="admin-page-heading"><h1>{{ $editing ? ($demandeurSpace ? 'Modifier mon brouillon' : 'Modifier la demande') : 'Nouvelle demande DMA' }}</h1>@if($editing)<span class="admin-badge admin-badge-{{ \App\Support\DmaStatus::group($demande->statut) }}">{{ \App\Support\DmaStatus::label($demande->statut) }}</span>@endif</header>
    <section class="admin-panel"><div class="admin-panel-heading"><h2>{{ $editing ? ($demande->numero_dma ?: 'Dossier #'.$demande->id) : 'Informations de la demande' }}</h2></div>
        @include('admin.partials.errors')
        <form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ $editing ? route('demandes.update', $demande) : route('demandes.store') }}">@csrf @if($editing) @method('PUT') @endif
            <input type="hidden" name="structures_included" value="1">
            <div class="admin-form-grid">
                @foreach(['objet_modif' => 'Objet de la modification', 'structure' => 'Structure', 'unite_concernee' => 'Unité concernée', 'repere' => 'Repère', 'fonction' => 'Fonction'] as $field => $label)
                <div @class(['admin-full-width' => $field === 'objet_modif'])><label for="dma-{{ $field }}">{{ $label }}</label><input id="dma-{{ $field }}" name="{{ $field }}" value="{{ old($field, $editing ? $demande->$field : '') }}" maxlength="255" @required(!$editing || in_array($field, ['objet_modif', 'structure']))>@error($field)<p class="admin-field-error">{{ $message }}</p>@enderror</div>
                @endforeach
                @foreach(['motif' => 'Motif de la demande', 'situation_existante' => 'Situation existante', 'situation_souhaitee' => 'Situation souhaitée'] as $field => $label)
                <div class="admin-full-width"><label for="dma-{{ $field }}">{{ $label }}</label><textarea id="dma-{{ $field }}" name="{{ $field }}" rows="4" maxlength="10000" @required(!$editing)>{{ old($field, $editing ? $demande->$field : '') }}</textarea>@error($field)<p class="admin-field-error">{{ $message }}</p>@enderror</div>
                @endforeach
                <fieldset class="admin-full-width"><legend>Structures spécialisées</legend><div class="admin-checkboxes">@forelse($structures as $structure)<label><input type="checkbox" name="structures_specialisees[]" value="{{ $structure->id }}" @checked(in_array($structure->id, old('structures_specialisees', $editing ? $demande->structuresSpecialisees->modelKeys() : [])))>{{ $structure->nom }}</label>@empty<span>Aucune structure disponible.</span>@endforelse</div></fieldset>
                <div class="admin-full-width"><label for="dma-files">{{ $editing ? 'Ajouter des pièces jointes' : 'Pièces jointes' }}</label><input id="dma-files" type="file" name="pieces_jointes[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" aria-describedby="dma-files-help"><p id="dma-files-help" class="admin-field-hint">PDF, Word, JPG ou PNG · 10 fichiers maximum · 5 Mo maximum par fichier</p></div>
            </div>
            <div class="admin-form-actions"><a class="admin-button admin-button-secondary" href="{{ route('demandes.index') }}">Annuler</a><button class="admin-button admin-button-primary">{{ $editing ? 'Enregistrer les modifications' : 'Enregistrer le brouillon' }}</button></div>
        </form>
    </section>
    @if($editing && $demande->piecesJointes->isNotEmpty())<section class="admin-panel admin-spaced"><div class="admin-panel-heading"><h2>Pièces jointes existantes</h2></div><div class="admin-file-list">@foreach($demande->piecesJointes as $piece)<div><a href="{{ asset('storage/'.$piece->chemin_fichier) }}" target="_blank" rel="noopener">{{ basename($piece->chemin_fichier) }} ↗</a><form method="POST" action="{{ route('pieces.destroy', $piece) }}" onsubmit="return confirm('Supprimer définitivement cette pièce jointe ?');">@csrf @method('DELETE')<button class="admin-text-danger">Supprimer</button></form></div>@endforeach</div></section>@endif
</div>
</x-app-layout>
