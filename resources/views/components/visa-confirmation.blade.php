@props(['id', 'role', 'confirmation' => true])
<div class="admin-full-width dma-visa-confirmation">
    <div class="dma-visa-identity"><span class="admin-reference">Visa nominatif</span><strong>{{ trim(auth()->user()->prenom.' '.auth()->user()->name) }}</strong><span>{{ \App\Support\AdminRole::label($role) }}</span></div>
    <label for="{{ $id }}-password">Confirmez avec votre mot de passe</label>
    <input id="{{ $id }}-password" name="password" type="password" autocomplete="current-password" maxlength="4096" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @if($errors->has('password')) aria-describedby="{{ $id }}-password-error" @endif>
    @if($errors->has('password'))<p id="{{ $id }}-password-error" class="text-sm text-red-600">{{ $errors->first('password') }}</p>@endif
    @if($confirmation)
        <label class="requester-receipt-confirmation dma-visa-consent" for="{{ $id }}-consent"><input id="{{ $id }}-consent" type="checkbox" name="visa_confirmation" value="1" required><span>Je confirme apposer mon visa sur la décision choisie pour cette DMA.</span></label>
    @endif
</div>
