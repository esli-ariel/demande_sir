<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard Service Technique</h2>
    </x-slot>

    <div class="py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif

        <div class="p-6 bg-white shadow-sm sm:rounded-lg">
            <h3 class="mb-4 text-lg font-bold">Demandes à traiter</h3>

            @forelse($demandes as $demande)
                <div class="p-4 mb-4 border rounded">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-semibold">#{{ $demande->id }} — {{ $demande->objet_modif ?? $demande->objet }}</p>
                        </div>
                        <div>
                            <span class="{{ $demande->getStatutBadgeClass() }}">{{ ucfirst(str_replace('_',' ', $demande->statut)) }}</span>
                        </div>
                    </div>

                    <form action="{{ route('demandes.traiter_agent', $demande) }}" method="POST" class="mt-3 space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input type="date" name="date_debut" class="p-1 border rounded" required>
                            <input type="date" name="date_fin" class="p-1 border rounded" required>
                        </div>
                        <textarea name="travaux_realises" placeholder="Travaux réalisés (détail)" class="w-full p-1 border rounded" required></textarea>

                        @can('traiter_demande')
                            <button type="submit" class="px-3 py-1 text-white bg-blue-600 rounded">Enregistrer & Mettre en traitement</button>
                        @endcan
                    </form>

                    <a href="{{ route('demandes.show', $demande) }}" class="inline-block mt-2 text-blue-600 underline">Voir</a>
                </div>
            @empty
                <p class="text-gray-500">Aucune demande pour le service technique.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
