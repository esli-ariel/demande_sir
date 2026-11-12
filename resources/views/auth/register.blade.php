<x-auth-shell title="Créer un compte" description="Votre compte demandeur pour déposer et suivre vos DMA.">
    <form class="auth-form" method="POST" action="{{ route('register') }}" @submit="submitting = true" :aria-busy="submitting.toString()">
        @csrf
        <input type="hidden" name="role" value="demandeur">
        <x-auth-field name="name" label="Nom" autocomplete="family-name" />
        <x-auth-field name="prenom" label="Prénom" autocomplete="given-name" />
        <x-auth-field name="email" label="Adresse e-mail" type="email" autocomplete="username" />
        <x-auth-field name="password" label="Mot de passe" type="password" autocomplete="new-password" minlength="8" hint="8 caractères minimum." />
        <x-auth-field name="password_confirmation" label="Confirmation du mot de passe" type="password" autocomplete="new-password" minlength="8" />
        <button type="submit" class="auth-submit" :disabled="submitting"><span x-show="!submitting">Créer mon compte</span><span x-show="submitting" x-cloak>Création du compte…</span><span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a></p>
</x-auth-shell>
