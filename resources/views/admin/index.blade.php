<x-app-layout>
    <div class="admin-dashboard">
        <header class="admin-page-heading">
            <div><h1>Utilisateurs</h1></div>
            <a class="admin-button admin-button-orange" href="{{ route('users.create') }}">Nouvel utilisateur <span aria-hidden="true">＋</span></a>
        </header>
        <section class="admin-panel">
            <div class="admin-panel-heading"><h2>Comptes et rôles</h2><span class="admin-count">{{ $totalUsers }} comptes</span></div>
            <form class="admin-filters" method="GET" action="{{ route('users.index') }}">
                <div class="admin-search"><label for="user-search">Rechercher</label><input id="user-search" name="q" type="search" maxlength="150" value="{{ $search }}" placeholder="Nom, prénom ou e-mail"></div>
                <div><label for="user-role">Rôle</label><select id="user-role" name="role"><option value="">Tous les rôles</option>@foreach($roles as $role)<option value="{{ $role->name }}" @selected($selectedRole === $role->name)>{{ \App\Support\AdminRole::label($role->name) }}</option>@endforeach</select></div>
                <button class="admin-button admin-button-primary">Filtrer</button>
                @if($search || $selectedRole)<a class="admin-reset" href="{{ route('users.index') }}">Réinitialiser</a>@endif
            </form>
            <div class="admin-table-wrap"><table class="admin-table admin-user-table"><caption class="sr-only">Utilisateurs et rôles</caption><thead><tr><th scope="col">Utilisateur</th><th scope="col">E-mail</th><th scope="col">Rôle</th><th scope="col">Actions</th></tr></thead><tbody>
                @forelse($users as $user)
                <tr><td data-label="Utilisateur"><div class="admin-person"><span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->prenom ?: $user->name, 0, 1)) }}</span><div><strong>{{ $user->prenom }} {{ $user->name }}</strong>@if(auth()->id() === $user->id)<span class="admin-reference">Votre compte</span>@endif</div></div></td><td data-label="E-mail">{{ $user->email }}</td><td data-label="Rôle">@forelse($user->roles as $role)<span class="admin-badge">{{ \App\Support\AdminRole::label($role->name) }}</span>@empty<span class="admin-badge">Sans rôle</span>@endforelse</td><td class="admin-row-action"><div class="admin-inline-actions"><a href="{{ route('users.edit', $user) }}" aria-label="Modifier {{ $user->prenom }} {{ $user->name }}">Modifier</a>@if(auth()->id() !== $user->id)<form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Supprimer définitivement ce compte et les données liées, dont ses demandes ? Cette action est irréversible.');">@csrf @method('DELETE')<button class="admin-text-danger" aria-label="Supprimer {{ $user->prenom }} {{ $user->name }}">Supprimer</button></form>@endif</div></td></tr>
                @empty<tr><td colspan="4"><div class="admin-empty"><strong>Aucun utilisateur trouvé.</strong></div></td></tr>@endforelse
            </tbody></table></div>
            <div class="admin-pagination">{{ $users->links('pagination.admin') }}</div>
        </section>
    </div>
</x-app-layout>
