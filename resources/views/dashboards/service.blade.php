<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Tableau de bord - Service Technique</h2>
    </x-slot>

    <div class="py-6">
        <h3 class="mb-4 text-lg font-bold">Demandes à traiter</h3>

        @forelse($demandes as $demande)
            <div class="p-4 mb-3 border rounded">
                <p><strong>ID :</strong> {{ $demande->id }}</p>
                <p><strong>Objet :</strong> {{ $demande->objet }}</p>
                <p><strong>Statut :</strong> {{ ucfirst($demande->statut) }}</p>

                <div class="flex gap-2 mt-2">
                    @if($demande->statut === 'validee_responsable')
                        <form action="{{ route('demandes.traiter', $demande) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 text-white bg-yellow-500 rounded">
                                Mettre en traitement
                            </button>
                        </form>
                    @endif

                    @if($demande->statut === 'en_cours_traitement')
                        <form action="{{ route('demandes.cloturer', $demande) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 text-white bg-blue-500 rounded">
                                Clôturer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500">Aucune demande en attente.</p>
        @endforelse
    </div>
</x-app-layout>
