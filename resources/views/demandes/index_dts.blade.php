<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Demandes à valider (DTS)</h2>
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

            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold text-gray-700">Demandes soumises à la DTS</h3>

                @forelse($demandes as $demande)
                    <div class="p-4 mb-4 border rounded bg-gray-50 hover:shadow">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-gray-800">
                                    #{{ $demande->id }} — {{ $demande->structure }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Objet : {{ $demande->objet_modif }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Créée le : {{ $demande->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="{{ $demande->getStatutBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-sm text-gray-700">
                                {{ Str::limit($demande->motif, 200) }}
                            </p>
                        </div>

                        {{-- Actions DTS --}}
                        @role('dts')
                        <div class="flex flex-wrap gap-2 mt-3">
                            <form action="{{ route('demandes.valider_dts', $demande) }}" method="POST" class="space-y-1">
                                @csrf
                                <input name="visa" placeholder="Visa (nom)" class="w-56 p-1 border rounded" />
                                <textarea name="commentaire" placeholder="Commentaire (optionnel)" class="p-1 border rounded w-72"></textarea>
                                <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Valider</button>
                            </form>
                            
                            <form action="{{ route('demandes.rejeter_dts', $demande) }}" method="POST" class="space-y-1">
                                @csrf
                                <input name="visa" placeholder="Visa (nom)" class="w-56 p-1 border rounded" />
                                <textarea name="commentaire" placeholder="Motif du refus" class="p-1 border rounded w-72" required></textarea>
                                <button type="submit" class="px-3 py-1 text-white bg-red-600 rounded">Refuser</button>
                            </form>

                            <a href="{{ route('demandes.show', $demande) }}" class="ml-2 text-blue-600 underline">
                                Voir les détails
                            </a>
                        </div>
                        @endrole
                    </div>
                @empty
                    <p class="text-gray-500">Aucune demande à valider pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
