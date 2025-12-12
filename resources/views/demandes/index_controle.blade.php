<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Demandes en attente du Contrôle avancé</h2>
    </x-slot>

    <div class="relative min-h-screen py-12 bg-gray-100">
        <div class="absolute inset-0 opacity-10"
            style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                   background-repeat: no-repeat;
                   background-position: center;
                   background-size: cover;">
        </div>

        <div class="relative z-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold text-gray-700">
                    🧾 Demandes à approuver par le Contrôle avancé
                </h3>

                @forelse($demandes as $demande)
                    <div class="p-4 mb-6 transition border rounded bg-gray-50 hover:shadow-md">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-semibold">#{{ $demande->id }} — {{ $demande->structure }}</p>
                                <p>Objet : {{ $demande->objet_modif ?? $demande->objet }}</p>
                            </div>
                            <div>
                                <span class="{{ $demande->getStatutBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions pour le Contrôle avancé --}}
                        @role('controle_avancee')
                       <div class="grid gap-4 mt-4 md:grid-cols-2">
    {{-- ✅ Validation --}}
    <form action="{{ route('demandes.valider_controle', $demande) }}" method="POST" class="p-3 border rounded bg-green-50">
        @csrf
        <label class="block text-sm font-semibold text-gray-700">Signature / Visa :</label>
        <input name="visa" class="w-full p-2 mt-1 border rounded" placeholder="Ex: Visa du Contrôle" required>

        <label class="block mt-2 text-sm font-semibold text-gray-700">Commentaire (optionnel) :</label>
        <textarea name="commentaire" class="w-full p-2 mt-1 border rounded" placeholder="Ajoutez un commentaire"></textarea>

        <button type="submit" class="w-full px-4 py-2 mt-3 text-white bg-green-600 rounded hover:bg-green-700">
            ✅ Valider la demande
        </button>
    </form>

    {{-- ❌ Rejet --}}
    <form action="{{ route('demandes.rejeter_controle', $demande) }}" method="POST" class="p-3 border rounded bg-red-50">
        @csrf
        <label class="block text-sm font-semibold text-gray-700">Signature / Visa :</label>
        <input name="visa" class="w-full p-2 mt-1 border rounded" placeholder="Ex: Visa du Contrôle" required>

        <label class="block mt-2 text-sm font-semibold text-gray-700">Motif du rejet :</label>
        <textarea name="commentaire" class="w-full p-2 mt-1 border rounded" required placeholder="Expliquez la raison du rejet"></textarea>

        <button type="submit" class="w-full px-4 py-2 mt-3 text-white bg-red-600 rounded hover:bg-red-700">
            ❌ Rejeter la demande
        </button>
    </form>
    {{-- 🔹 Bouton Voir --}}
                            <a href="{{ route('demandes.show', $demande) }}" 
                            class="px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700">
                                Voir
                            </a>
</div>

                        @endrole
                    </div>
                @empty
                    <p class="text-gray-500">Aucune demande en attente pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
