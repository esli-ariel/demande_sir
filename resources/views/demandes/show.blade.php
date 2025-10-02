<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Détail de la Demande</h2>
    </x-slot>

    <div class="py-6">
        <p><b>Objet :</b> {{ $demande->objet }}</p>
        <p><b>Motif :</b> {{ $demande->motif }}</p>
        <p><b>Statut :</b> {{ $demande->statut }}</p>
        <p><b>Demandeur :</b> {{ $demande->user->name }}</p>
        
    </div>
    <div class="mb-4">
        <strong>Statut :</strong>
        <span class="{{ $demande->getStatutBadgeClass() }}">
            {{ ucfirst(str_replace('_',' ', $demande->statut)) }}
        </span>
    </div>
   
    <!-- Bouton soumettre -->
            @if($demande->statut === 'brouillon')
                <form action="{{ route('demandes.submit', $demande) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded">
                        Soumettre au responsable
                    </button>
                </form>
            @endif
</x-app-layout>
