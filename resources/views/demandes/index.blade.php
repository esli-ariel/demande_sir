<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard Exploitation</h2>
    </x-slot>

    <div class="py-12">
         @if ($errors->any())
            <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 mb-4 text-red-800 bg-red-100 rounded">
                    {{ session('error') }}
                </div>
            @endif
               {{-- Barre de recherche + filtres --}}
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
                {{-- Bouton nouvelle demande --}}
                <a href="{{ route('demandes.create') }}"
                   class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    + Nouvelle demande
                </a>

                <form action="{{ route('demandes.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    {{-- Champ recherche --}}
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="🔍 Rechercher (objet, motif, nom...)" 
                           class="p-2 border rounded w-64">

                    {{-- Filtre statut --}}
                    <select name="statut" class="p-2 border rounded">
                        <option value="">-- Tous les statuts --</option>
                        <option value="brouillon" {{ request('statut') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                        <option value="soumise" {{ request('statut') == 'soumise' ? 'selected' : '' }}>Soumise</option>
                        <option value="validee_exploitation" {{ request('statut') == 'validee_exploitation' ? 'selected' : '' }}>Validée Exploitation</option>
                        <option value="refusee_exploitation" {{ request('statut') == 'refusee_exploitation' ? 'selected' : '' }}>Refusée Exploitation</option>
                        <option value="validee_dts" {{ request('statut') == 'validee_dts' ? 'selected' : '' }}>Validée DTS</option>
                        <option value="refusee_dts" {{ request('statut') == 'refusee_dts' ? 'selected' : '' }}>Refusée DTS</option>
                        <option value="validee_controle_avancee" {{ request('statut') == 'validee_controle_avancee' ? 'selected' : '' }}>Validée Contrôle avancé</option>
                        <option value="refusee_controle_avancee" {{ request('statut') == 'refusee_controle_avancee' ? 'selected' : '' }}>Refusée Contrôle avancé</option>
                        <option value="en_cours_traitement" {{ request('statut') == 'en_cours_traitement' ? 'selected' : '' }}>En cours de traitement</option>
                        <option value="terminee_agent" {{ request('statut') == 'terminee_agent' ? 'selected' : '' }}>Terminée (agent)</option>
                        <option value="cloturee_receptionnee" {{ request('statut') == 'cloturee_receptionnee' ? 'selected' : '' }}>Clôturée & Réceptionnée</option>
                    </select>

                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Filtrer
                    </button>
                </form>
            </div>

            <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold">Demandes soumises</h3>

                @forelse($demandes as $demande)
                    <div class="p-4 mb-4 border rounded">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold">#{{ $demande->id }} — {{ $demande->nom ?? $demande->user->name }}</p>
                                <p class="text-sm">{{ $demande->objet_modif ?? $demande->objet }}</p>
                                <p class="text-xs text-gray-500">Créée le: {{ $demande->created_at->format('d/m/Y') }}</p>
                            </div>

                            <div class="text-right">
                                <span class="{{ $demande->getStatutBadgeClass() }}">
                                    {{ ucfirst(str_replace('_',' ', $demande->statut)) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-sm text-gray-700">{{ Str::limit($demande->motif, 250) }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-3">
                            @role('exploitant')
                            <form action="{{ route('demandes.validerExploitation', $demande) }}" method="POST" class="space-y-1">
                                @csrf
                                <input name="visa" placeholder="Visa (nom)" class="w-56 p-1 border rounded" />
                                <textarea name="commentaire" placeholder="Commentaire (optionnel)" class="p-1 border rounded w-72"></textarea>
                                <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Accorder</button>
                            </form>
                            
                            <form action="{{ route('demandes.rejeterExploitation', $demande) }}" method="POST" class="space-y-1">
                                @csrf
                                <input name="visa" placeholder="Visa (nom)" class="w-56 p-1 border rounded" />
                                <textarea name="commentaire" placeholder="Motif du refus" class="p-1 border rounded w-72" required></textarea>
                                <button type="submit" class="px-3 py-1 text-white bg-red-600 rounded">Refuser</button>
                            </form>
                            @endrole
                            {{-- 🔹 Bouton Voir --}}
                            <a href="{{ route('demandes.show', $demande) }}" 
                            class="px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700">
                                Voir
                            </a>

                            {{-- 🔹 Bouton Modifier (demandeur ou admin seulement) --}}
                            @if(auth()->user()->hasRole('demandeur') || auth()->user()->hasRole('admin'))
                                <a href="{{ route('demandes.edit', $demande) }}" 
                                class="px-3 py-1 text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                    Modifier
                                </a>
                            @endif

                            {{-- 🔹 Bouton Supprimer (demandeur ou admin seulement) --}}
                            @if(auth()->user()->hasRole('demandeur') || auth()->user()->hasRole('admin'))
                                <form action="{{ route('demandes.destroy', $demande) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Voulez-vous vraiment supprimer cette demande ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700">
                                        Supprimer
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('demandes.show', $demande) }}" class="ml-2 text-blue-600 underline">Voir la demande</a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">Aucune demande à valider pour l'instant.</p>
                @endforelse
            </div>
        </div>
    </div>
 <!-- Bouton retour -->
    <a href="{{ url()->previous() }}" 
       class="inline-block px-4 py-2 mt-4 text-white bg-gray-600 rounded hover:bg-gray-700">
       ← Retour
    </a>
</x-app-layout>
