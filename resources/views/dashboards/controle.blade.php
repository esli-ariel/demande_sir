<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard Contrôle avancé</h2>
    </x-slot>
<div class="relative min-h-screen bg-gray-100 overflow-hidden">
        {{-- Image de fond filigrane --}}
        <div class="absolute inset-0 opacity-10 z-0"
             style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: 110%;">
        </div>
    <div class="py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif

        <div class="p-6 bg-white shadow-sm sm:rounded-lg">
            <h3 class="mb-4 text-lg font-bold">Demandes pour contrôle avancé</h3>

            @forelse($demandes as $demande)
                <div class="p-4 mb-4 border rounded">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-semibold">#{{ $demande->id }} — {{ $demande->objet_modif ?? $demande->objet }}</p>
                            <p class="text-sm text-gray-500">Validations structures : {{ $demande->validations->where('role','structure_specialisee')->count() }}</p>
                        </div>
                        <div>
                            <span class="{{ $demande->getStatutBadgeClass() }}">{{ ucfirst(str_replace('_',' ', $demande->statut)) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-3">
                        @can('valider_controle')
                        <form action="{{ route('demandes.valider_controle', $demande) }}" method="POST" class="space-y-1">
                            @csrf
                            <input name="visa" placeholder="Visa (nom)" class="w-56 p-1 border rounded" />
                            <textarea name="commentaire" placeholder="Commentaire" class="p-1 border rounded w-72"></textarea>
                            <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Valider + attribuer DMA</button>
                        </form>
                        @endcan

                        @can('rejeter_controle')
                        <form action="{{ route('demandes.rejeter_controle', $demande) }}" method="POST" class="space-y-1">
                            @csrf
                            <input name="visa" placeholder="Visa (nom)" class="w-56 p-1 border rounded" />
                            <textarea name="commentaire" placeholder="Motif du refus" class="p-1 border rounded w-72" required></textarea>
                            <button type="submit" class="px-3 py-1 text-white bg-red-600 rounded">Refuser</button>
                        </form>
                        @endcan

                        <a href="{{ route('demandes.show', $demande) }}" class="ml-2 text-blue-600 underline">Voir</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Aucune demande pour contrôle avancé.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
