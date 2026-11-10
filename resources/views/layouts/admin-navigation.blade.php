<nav class="admin-nav" aria-label="Navigation administration" x-data="adminNavigation" @keydown.escape.window="open = false">
    <div class="admin-nav-inner">
        <a class="admin-brand" href="{{ route('dashboard') }}" aria-label="SIR DMA — Tableau de bord">
            <x-application-logo class="h-10 w-auto" />
            <span>Gestion des DMA<small>Espace administration</small></span>
        </a>
        <div class="admin-nav-links">
            <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard', 'dashboard.admin', 'admin.index')])>Vue d’ensemble</a>
            <a href="{{ route('demandes.index') }}" @class(['is-active' => request()->routeIs('demandes.*')])>Demandes</a>
            <a href="{{ route('users.index') }}" @class(['is-active' => request()->routeIs('users.*')])>Utilisateurs</a>
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
        <a href="{{ route('dashboard') }}">Vue d’ensemble</a>
        <a href="{{ route('demandes.index') }}">Demandes</a>
        <a href="{{ route('users.index') }}">Utilisateurs</a>
        <a href="{{ route('notifications.index') }}">Notifications</a>
        <a href="{{ route('profile.edit') }}">Mon profil</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Déconnexion</button></form>
    </div>
</nav>
