@php($staffRole = \App\Support\StaffWorkspace::role(auth()->user()))
@if($staffRole)
    @php($staffDefinition = \App\Support\StaffWorkspace::ROLES[$staffRole])
    @if(isset($staffDefinition['ability']))
        @can($staffDefinition['ability'], $demande)
        <section class="admin-panel requester-next-action"><div class="admin-panel-heading"><h2>Avis {{ $staffDefinition['label'] }}</h2></div><form class="admin-form" method="POST" action="{{ route($staffDefinition['approve'], $demande) }}">@csrf<div class="admin-form-grid">@if($staffRole === 'structure_specialisee')<div class="admin-full-width"><label for="staff-structure">Structure représentée</label><select id="staff-structure" name="structure_id" required>@foreach(\App\Support\StaffWorkspace::structuresForDecision(auth()->user(), $demande)->orderBy('nom')->get() as $structure)<option value="{{ $structure->id }}">{{ $structure->nom }}</option>@endforeach</select></div>@endif<div><label for="staff-visa">Votre visa</label><input id="staff-visa" name="visa" value="{{ old('visa', auth()->user()->prenom.' '.auth()->user()->name) }}" required maxlength="255"></div><div><label for="staff-comment">Commentaire / motif du refus</label><textarea id="staff-comment" name="commentaire" maxlength="5000" rows="3">{{ old('commentaire') }}</textarea></div></div><div class="admin-form-actions"><button class="admin-button admin-button-primary" type="submit" name="decision" value="accord">Accorder</button><button class="admin-button staff-button-danger" type="submit" name="decision" value="refus" formaction="{{ route($staffDefinition['reject'], $demande) }}">Refuser</button></div></form></section>
        @endcan
    @endif
    @if($staffRole === 'controle_avancee')
        @can('cloturer', $demande)
        <section class="admin-panel requester-next-action"><div class="admin-panel-heading"><h2>Vérification des travaux</h2></div><form class="admin-form" method="POST" action="{{ route('demandes.cloturer', $demande) }}">@csrf<div class="admin-form-grid"><div><label for="control-visa">Votre visa</label><input id="control-visa" name="visa" value="{{ old('visa', auth()->user()->prenom.' '.auth()->user()->name) }}" required maxlength="255"></div><div><label for="control-analysis">Analyse finale</label><textarea id="control-analysis" name="analyse" rows="3" maxlength="5000" required>{{ old('analyse') }}</textarea></div></div><div class="admin-form-actions"><button class="admin-button admin-button-primary">Transmettre à la réception</button></div></form></section>
        @endcan
    @endif
@endif

@if($staffRole === 'service_technique')
@foreach(['traiter'=>'Démarrer les travaux','terminerTravaux'=>'Terminer les travaux'] as $ability=>$title)
@can($ability, $demande)
<section class="admin-panel"><div class="admin-panel-heading"><h2>{{ $title }}</h2></div><form class="admin-form" method="POST" action="{{ route($ability === 'traiter' ? 'demandes.traiter_agent' : 'travaux.terminer', $demande) }}">@csrf
<div class="admin-form-grid"><div><label for="work-visa">Votre visa</label><input id="work-visa" name="visa" value="{{ old('visa', auth()->user()->name) }}" required maxlength="255"></div>
@if($ability === 'traiter')<div><label for="date_debut">Date de début</label><input id="date_debut" name="date_debut" type="date" value="{{ old('date_debut') }}" required></div>
@else<div><label for="date_fin">Date de fin</label><input id="date_fin" name="date_fin" type="date" min="{{ $demande->date_debut }}" value="{{ old('date_fin') }}" required></div><div class="admin-full-width"><label for="travaux_realises">Travaux réalisés</label><textarea id="travaux_realises" name="travaux_realises" rows="4" required maxlength="5000">{{ old('travaux_realises') }}</textarea></div>@endif</div>
<div class="admin-form-actions"><button class="admin-button admin-button-primary">{{ $title }}</button></div></form></section>
@endcan
@endforeach
@endif
