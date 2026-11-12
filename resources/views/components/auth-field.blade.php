@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'hint' => null, 'minlength' => null])
<div class="auth-field" @if($type === 'password') x-data="{ visible: false }" @endif>
    <label for="{{ $name }}">{{ $label }}</label>
    <div @class(['auth-input-wrap', 'auth-password' => $type === 'password'])>
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" required
            @if($type === 'password') :type="visible ? 'text' : 'password'" maxlength="4096"
            @else value="{{ is_string(old($name)) ? old($name) : '' }}" maxlength="255" @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($type === 'email') autocapitalize="none" spellcheck="false" @endif
            @if($minlength) minlength="{{ $minlength }}" @endif
            aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            @if($errors->has($name) || $hint) aria-describedby="{{ $errors->has($name) ? $name.'-error' : $name.'-hint' }}" @endif>
        @if($type === 'password')
            <button type="button" class="auth-password-toggle" x-cloak @click="visible = !visible" aria-controls="{{ $name }}" :aria-pressed="visible.toString()"
                data-show-label="{{ $name === 'password_confirmation' ? 'Afficher la confirmation du mot de passe' : 'Afficher le mot de passe' }}" data-hide-label="{{ $name === 'password_confirmation' ? 'Masquer la confirmation du mot de passe' : 'Masquer le mot de passe' }}"
                :aria-label="visible ? $el.dataset.hideLabel : $el.dataset.showLabel"><span x-text="visible ? 'Masquer' : 'Afficher'">Afficher</span></button>
        @endif
    </div>
    @if($hint)<p class="auth-hint" id="{{ $name }}-hint">{{ $hint }}</p>@endif
    @if($errors->has($name))<p class="auth-field-error" id="{{ $name }}-error">{{ $errors->first($name) }}</p>@endif
</div>
