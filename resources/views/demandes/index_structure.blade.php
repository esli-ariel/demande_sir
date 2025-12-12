<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Demandes à valider (Structures spécialisées)</h2>
    </x-slot>

    <div class="relative min-h-screen py-12 bg-gray-100">
        <div class="absolute inset-0 opacity-10"
            style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                   background-repeat: no-repeat;
                   background-position: center;
                   background-size: cover;">
        </div>

        <div class="relative z-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
            @endif

            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold text-gray-700">Demandes soumises aux Structures spécialisées</h3>

                @forelse($demandes as $demande)
                    <div class="p-4 mb-4 border rounded bg-gray-50 hover:shadow">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-gray-800">
                                    #{{ $demande->id }} — {{ $demande->structure }}
                                </p>
                                <p class="text-sm text-gray-600">Objet : {{ $demande->objet_modif }}</p>
                            </div>
                            <div>
                                <span class="{{ $demande->getStatutBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                                </span>
                            </div>
                        </div>
                        
                        @role('structure_specialisee')
<form action="{{ route('demandes.validerStructure', $demande) }}" method="POST" class="space-y-2">
    @csrf
    <label class="block">Visa</label>
    <input type="text" name="visa" class="w-full p-2 border rounded" required>

    <label class="block">Commentaire</label>
    <textarea name="commentaire" class="w-full p-2 border rounded"></textarea>

    <div class="flex gap-2 mt-2">
        <button name="decision" value="accord" class="px-4 py-2 text-white bg-green-600 rounded">
            ✅ Valider
        </button>
        <button name="decision" value="refus" class="px-4 py-2 text-white bg-red-600 rounded">
            ❌ Refuser
        </button>
        {{-- 🔹 Bouton Voir --}}
        <a href="{{ route('demandes.show', $demande) }}" 
            class="px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700">
            Voir
        </a>
    </div>
</form>
@endrole

                    </div>
                @empty
                    <p class="text-gray-500">Aucune demande à valider pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
