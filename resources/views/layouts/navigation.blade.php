<nav x-data="{ open: false, time: '' }" 
     x-init="setInterval(() => time = new Date().toLocaleTimeString(), 1000)" 
     class="fixed top-0 left-0 z-50 w-full shadow-md bg-gradient-to-r from-green-600 via-green-500 to-orange-400">
    

     
    <!-- Contenu principal -->
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 ">
        <div class="flex items-center justify-between h-16">
            
            <!-- Logo + Liens -->
            <div class="flex items-center space-x-8">
                <a href="{{ route('dashboard') }}">
                    <x-application-logo class="block w-auto h-10 text-white fill-current" />
                </a>

                <div class="hidden font-medium text-white sm:flex sm:space-x-6">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white hover:text-orange-200">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @role('demandeur')
                        <x-nav-link :href="route('demandes.index')" :active="request()->routeIs('demandes.*')" class="text-white hover:text-orange-200">
                            {{ __('Demandes') }}
                        </x-nav-link>
                    @endrole

                    @role('admin')
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="text-white hover:text-orange-200">
                            {{ __('Utilisateurs') }}
                        </x-nav-link>
                    @endrole

                    @role('exploitant')
                        <x-nav-link :href="route('demandes.index_exploitation')" :active="request()->routeIs('demandes.index_exploitation')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à valider') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('dts')
                        <x-nav-link :href="route('demandes.index_dts')" :active="request()->routeIs('demandes.index_dts')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à traiter') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('structure_specialisee')
                        <x-nav-link :href="route('demandes.index_structure')" :active="request()->routeIs('demandes.index_structure')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à valider') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('controle_avancee')
                        <x-nav-link :href="route('demandes.index_controle')" :active="request()->routeIs('demandes.index_controle')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à approuver') }}
                        </x-nav-link>
                    @endrole

                    @role('service_technique')
                        <x-nav-link :href="route('demandes.index_service')" :active="request()->routeIs('demandes.index_service')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à executer') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('controle_avancee')
                        <x-nav-link :href="route('demandes.cloture')" :active="request()->routeIs('demandes.cloture')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à cloturer ') }}
                        </x-nav-link>
                    @endrole
                </div>
            </div>

            <!-- Infos utilisateur -->
            <div class="items-center hidden space-x-4 text-white sm:flex">
                
                <!-- Heure -->
                <div class="text-sm font-semibold" x-text="time"></div>

                <!-- Nom & prénom -->
                <div class="px-3 py-1 font-semibold rounded-lg bg-white/20 backdrop-blur-md">
                    {{ Auth::user()->prenom }} {{ Auth::user()->name }}
                </div>

                <!-- Rôle -->
                <div class="px-3 py-1 font-semibold capitalize rounded-lg bg-white/20 backdrop-blur-md">
                    {{ Auth::user()->roles->pluck('name')->first() }}
                </div>
                

                      @auth
<div class="relative " >
    <button id="notifButton" class="relative text-gray-700">
        🔔 notifs
        @if(auth()->user()->unreadNotifications->count())
            <span class="absolute top-0 right-0 w-4 h-4 text-xs text-white bg-red-500 rounded-full">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </button>

    <div id="notifPanel" class="absolute right-0 hidden w-64 mt-2 bg-white border rounded shadow-lg">
        @forelse(auth()->user()->unreadNotifications as $notif)
            <div class="p-2 border-b hover:bg-gray-50">
                <strong>{{ ucfirst($notif->data['action']) }}</strong> : {{ $notif->data['objet'] }}<br>
                <small>{{ $notif->created_at->diffForHumans() }}</small>
            </div>
        @empty
            <p class="p-2 text-gray-500">Aucune notification</p>
        @endforelse
    </div>
</div>

<script>
document.getElementById('notifButton').onclick = () => {
    document.getElementById('notifPanel').classList.toggle('hidden');
};
</script>
@endauth

                
                <!-- Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-white transition duration-150 ease-in-out hover:text-orange-200">
                            <svg class="w-4 h-4 ms-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
          
            <!-- Menu mobile -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="p-2 text-white rounded-md hover:bg-white/20 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu responsive mobile -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden text-white sm:hidden bg-green-700/95">
        <div class="pt-2 pb-3 space-y-1 text-sm font-medium">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

             @role('demandeur')
                        <x-nav-link :href="route('demandes.index')" :active="request()->routeIs('demandes.*')" class="text-white hover:text-orange-200">
                            {{ __('Demandes') }}
                        </x-nav-link>
                    @endrole

                    @role('admin')
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="text-white hover:text-orange-200">
                            {{ __('Utilisateurs') }}
                        </x-nav-link>
                    @endrole

                    @role('exploitant')
                        <x-nav-link :href="route('demandes.index_exploitation')" :active="request()->routeIs('demandes.index_exploitation')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à valider') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('dts')
                        <x-nav-link :href="route('demandes.index_dts')" :active="request()->routeIs('demandes.index_dts')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à traiter') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('structure_specialisee')
                        <x-nav-link :href="route('demandes.index_structure')" :active="request()->routeIs('demandes.index_structure')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à valider') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('controle_avancee')
                        <x-nav-link :href="route('demandes.index_controle')" :active="request()->routeIs('demandes.index_controle')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à approuver') }}
                        </x-nav-link>
                    @endrole

                    @role('service_technique')
                        <x-nav-link :href="route('demandes.index_service')" :active="request()->routeIs('demandes.index_service')" class="text-white hover:text-orange-200">
                            {{ __('Demandes à executer') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('controle_avancee')
                        <x-nav-link :href="route('demandes.cloture')" :active="request()->routeIs('demandes.cloture')" class="text-white hover:text-orange-200">
                            {{ __('Cloture ') }}
                        </x-nav-link>
                    @endrole
                    
            <div class="px-4 pt-2 mt-2 border-t border-green-500">
                <div>👤 {{ Auth::user()->prenom }} {{ Auth::user()->name }}</div>
                <div class="capitalize">🎯 Rôle : {{ Auth::user()->roles->pluck('name')->first() }}</div>
                <div class="mt-1 text-xs opacity-90" x-text="time"></div>
            </div>
        </div>
    </div>
</nav>
