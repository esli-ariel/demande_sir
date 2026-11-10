@php($staffSpace = \App\Support\StaffWorkspace::role(auth()->user()))
@php($staffLabel = $staffSpace ? \App\Support\StaffWorkspace::ROLES[$staffSpace]['label'] : null)
@php($demandeurSpace = auth()->user()->hasRole('demandeur') && !auth()->user()->hasRole('admin'))
<nav class="admin-nav" aria-label="{{ $demandeurSpace ? 'Navigation demandeur' : ($staffSpace ? 'Navigation '.$staffLabel : 'Navigation administration') }}" x-data="adminNavigation" @keydown.escape.window="open = false">
    <div class="admin-nav-inner">
        <a class="admin-brand" href="{{ route('dashboard') }}" aria-label="SIR DMA — Tableau de bord">
            <x-application-logo class="h-10 w-auto" />
            <span>Gestion des DMA<small>{{ $demandeurSpace ? 'Espace demandeur' : ($staffSpace ? $staffLabel : 'Espace administration') }}</small></span>
        </a>
        <div class="admin-nav-links">
            @if($staffSpace)
                <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard', 'dashboard.*')])>Tableau de bord</a>
                <a href="{{ route('staff.demandes') }}" @class(['is-active' => request()->routeIs('staff.demandes', 'demandes.*') && request('vue') !== 'mes_decisions'])>À traiter</a>
                <a href="{{ route('staff.demandes', ['vue'=>'mes_decisions']) }}" @class(['is-active' => request('vue') === 'mes_decisions'])>Mes décisions</a>
            @elseif($demandeurSpace)
                <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard', 'dashboard.demandeur')])>Mon tableau de bord</a>
                <a href="{{ route('demandes.index') }}" @class(['is-active' => request()->routeIs('demandes.*')])>Mes demandes</a>
                <a href="{{ route('demandeur.receptions') }}" @class(['is-active' => request()->routeIs('demandeur.receptions')])>Réceptions</a>
            @else
                <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard', 'dashboard.admin', 'admin.index')])>Vue d’ensemble</a>
                <a href="{{ route('demandes.index') }}" @class(['is-active' => request()->routeIs('demandes.*')])>Demandes</a>
                <a href="{{ route('users.index') }}" @class(['is-active' => request()->routeIs('users.*')])>Utilisateurs</a>
            @endif
        </div>
        <div class="admin-nav-account">
            <a href="{{ route('notifications.index') }}" aria-label="Notifications, {{ auth()->user()->unreadNotifications->count() }} non lues">Notifications <span>{{ auth()->user()->unreadNotifications->count() }}</span></a>
            <a href="{{ route('profile.edit') }}">Mon profil</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Déconnexion</button></form>
        </div>
        <button class="admin-theme-toggle" type="button" @click="toggleTheme()"
                :aria-label="dark ? 'Activer le mode jour' : 'Activer le mode nuit'"
                :aria-pressed="dark.toString()">
            <svg x-show="!dark" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M20.9 13.1A9 9 0 0 1 10.9 3.1 9 9 0 1 0 20.9 13.1Z"/></svg>
            <svg x-show="dark" x-cloak aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></svg>
            <span x-text="dark ? 'Jour' : 'Nuit'">Nuit</span>
        </button>
        <button class="admin-menu-toggle" type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="admin-mobile-menu" aria-label="Ouvrir ou fermer le menu">Menu <span aria-hidden="true">☰</span></button>
    </div>
    <div class="admin-mobile-menu" id="admin-mobile-menu" x-show="open" x-cloak>
        <a href="{{ route('dashboard') }}">{{ $demandeurSpace ? 'Mon tableau de bord' : ($staffSpace ? 'Tableau de bord' : 'Vue d’ensemble') }}</a>
        <a href="{{ $staffSpace ? route('staff.demandes') : route('demandes.index') }}">{{ $demandeurSpace ? 'Mes demandes' : ($staffSpace ? 'À traiter' : 'Demandes') }}</a>
        @if($staffSpace)<a href="{{ route('staff.demandes', ['vue'=>'mes_decisions']) }}">Mes décisions</a>@elseif($demandeurSpace)<a href="{{ route('demandeur.receptions') }}">Réceptions</a>@else<a href="{{ route('users.index') }}">Utilisateurs</a>@endif
        <a href="{{ route('notifications.index') }}">Notifications</a>
        <a href="{{ route('profile.edit') }}">Mon profil</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Déconnexion</button></form>
    </div>
</nav>
