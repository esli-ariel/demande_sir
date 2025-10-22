<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard Réception</h2>
    </x-slot>

    <div class="py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif

        <div class="p-6 bg-white shadow-sm sm:rounded-lg">
            <h3 class="mb-4 text-lg font-bold">Réception et clôture</h3>

            @forelse($demandes as $demande)
                <div class="p-4 mb-4 border rounded">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-semibold">#{{ $demande->id }} — {{ $demande->objet_modif ?? $demande->objet }}</p>
                            <p class="text-sm text-gray-600">Travaux : {{ Str::limit($demande->travaux_realises, 120) }}</p>
                        </div>
                        <div>
                            <span class="{{ $demande->getStatutBadgeClass() }}">{{ ucfirst(str_replace('_',' ', $demande->statut)) }}</span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <form action="{{ route('demandes.cloturer_reception', $demande) }}" method="POST" class="space-y-2">
                            @csrf
                            <input name="visa_demandeur" placeholder="Visa demandeur (nom)" class="w-56 p-1 border rounded" />
                            <input name="visa_responsable" placeholder="Visa responsable intervention" class="w-56 p-1 border rounded" />
                            <textarea name="commentaire" placeholder="Commentaire réception" class="w-full p-1 border rounded"></textarea>

                            @can('cloturer_reception')
                                <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Clôturer & Réceptionner</button>
                            @endcan
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Aucune demande à réceptionner pour l'instant.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
