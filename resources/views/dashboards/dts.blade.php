<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard DTS</h2>
    </x-slot>

    <div class="py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif

        <div class="p-6 bg-white shadow-sm sm:rounded-lg">
            <h3 class="mb-4 text-lg font-bold">Demandes validées par l'exploitation</h3>

            @forelse($demandes as $demande)
                <div class="p-4 mb-4 border rounded">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-semibold">#{{ $demande->id }} — {{ $demande->objet_modif ?? $demande->objet }}</p>
                            <p class="text-sm text-gray-600">Demandeur : {{ $demande->user->name }}</p>
                        </div>
                        <div>
                            <span class="{{ $demande->getStatutBadgeClass() }}">{{ ucfirst(str_replace('_',' ', $demande->statut)) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-3">
                        @role('dts')
                        <form action="{{ route('demandes.valider_dts', $demande) }}" method="POST" class="space-y-1">
                            @csrf
                            <input name="visa" placeholder="Visa (nom)" class="w-56 p-1 border rounded" />
                            <textarea name="commentaire" placeholder="Commentaire" class="p-1 border rounded w-72"></textarea>
                            <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Accorder</button>
                        </form>
                       
                        <form action="{{ route('demandes.rejeter_dts', $demande) }}" method="POST" class="space-y-1">
                            @csrf
                            <input name="visa" placeholder="Visa (nom)" class="w-56 p-1 border rounded" />
                            <textarea name="commentaire" placeholder="Motif du refus" class="p-1 border rounded w-72" required></textarea>
                            <button type="submit" class="px-3 py-1 text-white bg-red-600 rounded">Refuser</button>
                        </form>
                        @endrole

                        <a href="{{ route('demandes.show', $demande) }}" class="ml-2 text-blue-600 underline">Voir</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Aucune demande pour la DTS.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
