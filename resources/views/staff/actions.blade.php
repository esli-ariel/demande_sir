@php($staffRole = \App\Support\StaffWorkspace::role(auth()->user()))
@if($staffRole)
    @php($staffDefinition = \App\Support\StaffWorkspace::ROLES[$staffRole])
    @if(isset($staffDefinition['ability']))
        @can($staffDefinition['ability'], $demande)
        <section class="admin-panel requester-next-action"><div class="admin-panel-heading"><h2>Avis {{ $staffDefinition['label'] }}</h2></div><form class="admin-form" method="POST" action="{{ route($staffDefinition['approve'], $demande) }}">@csrf<div class="admin-form-grid"><div><label for="staff-visa">Votre visa</label><input id="staff-visa" name="visa" value="{{ old('visa', auth()->user()->prenom.' '.auth()->user()->name) }}" required maxlength="255"></div><div><label for="staff-comment">Commentaire / motif du refus</label><textarea id="staff-comment" name="commentaire" maxlength="5000" rows="3">{{ old('commentaire') }}</textarea></div></div><div class="admin-form-actions"><button class="admin-button admin-button-primary" type="submit">Accorder</button><button class="admin-button staff-button-danger" type="submit" formaction="{{ route($staffDefinition['reject'], $demande) }}">Refuser</button></div></form></section>
        @endcan
    @endif
@endif
