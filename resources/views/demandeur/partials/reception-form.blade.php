@can('receptionner', $demande)
    @php($receiptHasOld = old('_reception_demande_id') === (string) $demande->id)
    <section class="admin-panel admin-spaced requester-next-action" id="reception-demandeur-{{ $demande->id }}">
        <div class="admin-panel-heading"><h2>Confirmer la réception des travaux</h2></div>
        <form class="admin-form" method="POST" action="{{ route('demandes.receptionner', $demande) }}" onsubmit="return confirm('Confirmer la réception des travaux et clôturer définitivement votre DMA ?');">
            @csrf
            <input type="hidden" name="_reception_demande_id" value="{{ $demande->id }}">
            <div class="admin-form-grid">
                <div>
                    <label for="requester-visa-{{ $demande->id }}">Votre visa de réception</label>
                    <input id="requester-visa-{{ $demande->id }}" name="visa_demandeur" maxlength="255" required value="{{ $receiptHasOld && is_string(old('visa_demandeur')) ? old('visa_demandeur') : mb_substr(trim(auth()->user()->prenom.' '.auth()->user()->name), 0, 255) }}">
                </div>
                <div>
                    <label for="requester-comment-{{ $demande->id }}">Commentaire de réception</label>
                    <textarea id="requester-comment-{{ $demande->id }}" name="commentaire" rows="3" maxlength="5000">{{ $receiptHasOld && is_string(old('commentaire')) ? old('commentaire') : '' }}</textarea>
                </div>
                <div class="admin-full-width">
                    <label class="requester-receipt-confirmation" for="requester-confirm-{{ $demande->id }}">
                        <input id="requester-confirm-{{ $demande->id }}" type="checkbox" name="confirmation_reception" value="1" required @checked($receiptHasOld && in_array(old('confirmation_reception'), ['1',1,true], true))>
                        <span>Je confirme avoir reçu et vérifié les travaux réalisés pour cette demande.</span>
                    </label>
                </div>
            </div>
            <div class="admin-form-actions"><button class="admin-button admin-button-orange" type="submit">Réceptionner et clôturer ma DMA</button></div>
        </form>
    </section>
@endcan
