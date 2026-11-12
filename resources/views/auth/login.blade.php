<x-auth-shell title="Connexion" description="Retrouvez vos demandes et les actions qui vous attendent.">
    <form class="auth-form" method="POST" action="{{ route('login') }}" @submit="submitting = true" :aria-busy="submitting.toString()">
        @csrf
        <x-auth-field name="email" label="Adresse e-mail" type="email" autocomplete="username" />
        <x-auth-field name="password" label="Mot de passe" type="password" autocomplete="current-password" />
        <div class="auth-options">
            <label class="auth-remember" for="remember_me"><input id="remember_me" type="checkbox" name="remember" value="1" @checked(in_array(old('remember'), ['1', 1, true, 'on'], true))><span>Se souvenir de moi</span></label>
            @if(Route::has('password.request'))<a href="{{ route('password.request') }}">Mot de passe oublié ?</a>@endif
        </div>
        <button type="submit" class="auth-submit" :disabled="submitting"><span x-show="!submitting">Se connecter</span><span x-show="submitting" x-cloak>Connexion…</span><span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a></p>
</x-auth-shell>
