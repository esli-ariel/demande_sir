<section class="admin-panel admin-spaced">
    <div class="admin-panel-heading"><h2>Historique des décisions</h2></div>
    <ol class="admin-timeline">
        @forelse($decisions as $decision)
            <li>
                <div><strong>{{ $decision->signed_at ? ($decision->signer_structure_name ?: \App\Support\AdminRole::label($decision->role)) : ($decision->structure?->nom ?? \App\Support\AdminRole::label($decision->role)) }}</strong><span class="admin-badge">{{ match($decision->decision) {'accord'=>'Accord','refus'=>'Refus',default=>'En attente'} }}</span></div>
                @if($decision->signed_at)
                    <p><strong>{{ $decision->signer_name }}</strong> · Visa nominatif confirmé</p>
                    <p>{{ match($decision->visa_action) {'work_start'=>'Début des travaux','work_finish'=>'Fin des travaux','control_final'=>'Contrôle final','requester_receipt'=>'Réception du demandeur',default=>'Décision de validation'} }} · <time datetime="{{ $decision->signed_at->toIso8601String() }}">{{ $decision->signed_at->format('d/m/Y à H:i:s') }} UTC</time></p>
                @else
                    <p>Visa historique : {{ $decision->visa ?: 'Non renseigné' }}</p>
                    <p>{{ $decision->date_validation ? \Carbon\Carbon::parse($decision->date_validation)->format('d/m/Y') : 'Date non renseignée' }}</p>
                @endif
                @if($decision->commentaire)<blockquote>{{ $decision->commentaire }}</blockquote>@endif
            </li>
        @empty
            <li>Aucune décision enregistrée.</li>
        @endforelse
    </ol>
</section>
