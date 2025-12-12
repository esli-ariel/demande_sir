<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Clôture / Réception des demandes</h2>
    </x-slot>

    <div class="relative min-h-screen py-12 bg-gray-100">
        {{-- Filigrane --}}
        <div class="absolute inset-0 opacity-10"
            style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                   background-repeat: no-repeat;
                   background-position: center;
                   background-size: cover;">
        </div>

        <div class="relative z-10 mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Messages --}}
            @if(session('success'))
                <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
            @endif

            <div class="space-y-6">
                @forelse($demandes as $demande)
                    <div class="p-6 bg-white shadow-sm sm:rounded-lg">

                        {{-- TITRE --}}
                        <h3 class="mb-4 text-lg font-bold text-gray-700">
                            Clôture / Réception de la demande #{{ $demande->id }}
                        </h3>

                        {{-- RÉSUMÉ --}}
                        <div class="p-4 mb-4 border rounded bg-gray-50">
                            <p class="font-semibold text-gray-800">Structure : {{ $demande->structure }}</p>
                            <p class="text-sm text-gray-600">Objet : {{ $demande->objet_modif }}</p>
                            <p class="text-xs text-gray-500">Créée le : {{ $demande->created_at->format('d/m/Y') }}</p>
                            <p class="mt-2 text-sm text-gray-700">{{ Str::limit($demande->motif, 200) }}</p>
                            <span class="{{ $demande->getStatutBadgeClass() }}">
                                {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                            </span>
                        </div>

                        {{-- FORMULAIRE CONTROLE AVANCEE --}}
                        @role('controle_avancee')
                        @if($demande->statut === 'terminee_agent')
                           <form action="{{ route('demandes.cloturer', $demande) }}" method="POST" class="space-y-2">
                                @csrf
                                <textarea name="visa_controle" placeholder="Visa contrôle" class="w-full p-2 border rounded"></textarea>
                                <textarea name="analyse" placeholder="Analyse finale" class="w-full p-2 border rounded" required></textarea>
                                <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Clôturer</button>
                                 {{-- 🔹 Bouton Voir --}}
                            <a href="{{ route('demandes.show', $demande) }}" 
                            class="px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700">
                                Voir
                            </a>
                            </form>
                        @endif
                        @endrole

                        {{-- FORMULAIRE DEMANDEUR --}}
                        @role('demandeur')
                        @if($demande->statut === 'cloturee')
                              <form action="{{ route('demandes.receptionner', $demande) }}" method="POST" class="space-y-2">
                                @csrf
                                <textarea name="visa_demandeur" placeholder="Votre visa" class="w-full p-2 border rounded" required></textarea>
                                <textarea name="commentaire" placeholder="Commentaire (optionnel)" class="w-full p-2 border rounded"></textarea>
                                <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Réceptionner</button>
                                 {{-- 🔹 Bouton Voir --}}
                            <a href="{{ route('demandes.show', $demande) }}" 
                            class="px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700">
                                Voir
                            </a>
                            </form>
                        @endif
                        @endrole

                    </div>
                @empty
                    <p class="text-gray-500">Aucune demande à clôturer pour le moment.</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
