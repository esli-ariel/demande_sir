<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard Structures spécialisées</h2>
    </x-slot>

    <div class="py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif

        <div class="p-6 bg-white shadow-sm sm:rounded-lg">
            <h3 class="mb-4 text-lg font-bold">Demandes à examiner (structures)</h3>

            @forelse($demandes as $demande)
                <div class="p-4 mb-4 border rounded">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-semibold">#{{ $demande->id }} — {{ $demande->objet_modif ?? $demande->objet }}</p>
                            <p class="text-xs text-gray-500">Validations déjà enregistrées :</p>
                            <ul class="text-sm">
                                @foreach($demande->validations->where('role', 'structure_specialisee') as $v)
                                    <li class="text-gray-700">- {{ $v->user->name ?? $v->visa }} : {{ $v->decision }} ({{ optional($v->date_validation)->format('d/m/Y') }})</li>
                                @endforeach
                            </ul>
                        </div>

                        <div>
                            <span class="{{ $demande->getStatutBadgeClass() }}">{{ ucfirst(str_replace('_',' ', $demande->statut)) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-3">
                        @can('valider_structure')
                        <form action="{{ route('demandes.valider_structure', $demande) }}" method="POST" class="space-y-1">
                            @csrf
                            <input name="visa" placeholder="Visa (nom structure)" class="w-56 p-1 border rounded" />
                            <textarea name="commentaire" placeholder="Commentaire" class="p-1 border rounded w-72"></textarea>
                            <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Accorder</button>
                        </form>
                        @endcan

                        @can('rejeter_structure')
                        <form action="{{ route('demandes.rejeter_structure', $demande) }}" method="POST" class="space-y-1">
                            @csrf
                            <input name="visa" placeholder="Visa (nom structure)" class="w-56 p-1 border rounded" />
                            <textarea name="commentaire" placeholder="Motif du refus" class="p-1 border rounded w-72" required></textarea>
                            <button type="submit" class="px-3 py-1 text-white bg-red-600 rounded">Refuser</button>
                        </form>
                        @endcan

                        <a href="{{ route('demandes.show', $demande) }}" class="ml-2 text-blue-600 underline">Voir</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Aucune demande en attente pour les structures spécialisées.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
