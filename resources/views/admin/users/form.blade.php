@php($editing = isset($user))
<x-app-layout>
    <div class="admin-dashboard admin-form-page">
        <a class="admin-back" href="{{ route('users.index') }}">← Utilisateurs</a>
        <header class="admin-page-heading"><h1>{{ $editing ? 'Modifier un utilisateur' : 'Nouvel utilisateur' }}</h1></header>
        <section class="admin-panel">
            <div class="admin-panel-heading"><h2>{{ $editing ? $user->prenom.' '.$user->name : 'Informations du compte' }}</h2></div>
            @include('admin.partials.errors')
            <form class="admin-form" method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}" x-data="{ showPassword: false }">
                @csrf @if($editing) @method('PUT') @endif
                <div class="admin-form-grid">
                    <div><label for="user-prenom">Prénom</label><input id="user-prenom" name="prenom" value="{{ old('prenom', $user->prenom ?? '') }}" maxlength="255" required autocomplete="given-name">@error('prenom')<p class="admin-field-error">{{ $message }}</p>@enderror</div>
                    <div><label for="user-name">Nom</label><input id="user-name" name="name" value="{{ old('name', $user->name ?? '') }}" maxlength="255" required autocomplete="family-name">@error('name')<p class="admin-field-error">{{ $message }}</p>@enderror</div>
                    <div class="admin-full-width"><label for="user-email">Adresse e-mail</label><input id="user-email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" maxlength="255" required autocomplete="email">@error('email')<p class="admin-field-error">{{ $message }}</p>@enderror</div>
                    <div class="admin-full-width"><label for="user-role-select">Rôle</label><select id="user-role-select" name="role" required><option value="">Choisir un rôle</option>@foreach($roles as $role)<option value="{{ $role->name }}" @selected(old('role', $editing ? $user->roles->first()?->name : '') === $role->name)>{{ \App\Support\AdminRole::label($role->name) }}</option>@endforeach</select>@error('role')<p class="admin-field-error">{{ $message }}</p>@enderror</div>
                    @unless($editing)
                    <div><label for="new-user-password">Mot de passe · 8 caractères minimum</label><div class="admin-password-field"><input id="new-user-password" :type="showPassword ? 'text' : 'password'" type="password" name="password" minlength="8" required autocomplete="new-password"><button type="button" @click="showPassword = !showPassword" :aria-pressed="showPassword.toString()" x-text="showPassword ? 'Masquer' : 'Afficher'">Afficher</button></div>@error('password')<p class="admin-field-error">{{ $message }}</p>@enderror</div>
                    <div><label for="new-user-password-confirm">Confirmer le mot de passe</label><input id="new-user-password-confirm" :type="showPassword ? 'text' : 'password'" type="password" name="password_confirmation" minlength="8" required autocomplete="new-password"></div>
                    @endunless
                </div>
                <div class="admin-form-actions"><a class="admin-button admin-button-secondary" href="{{ route('users.index') }}">Annuler</a><button class="admin-button admin-button-primary">{{ $editing ? 'Enregistrer les modifications' : 'Créer l’utilisateur' }}</button></div>
            </form>
        </section>
    </div>
</x-app-layout>
