<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">📊 Dashboard Demandeur</h2>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gray-100">
        {{-- Image de fond filigrane --}}
        <div class="absolute inset-0 z-0 opacity-10"
             style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: 110%;">
        </div>

        <div class="relative z-10 py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

                {{-- Message de bienvenue --}}
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        👋 Bonjour, <b>{{ Auth::user()->name }}</b> !
                        <p class="text-sm text-gray-600">
                            Voici le récapitulatif de vos demandes et leur statut.
                        </p>
                    </div>
                </div>

                {{-- Statistiques --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-4 bg-blue-100 border border-blue-300 rounded-lg shadow">
                        <h4 class="text-sm font-semibold text-blue-800">Total de mes demandes</h4>
                        <p class="mt-2 text-3xl font-bold text-blue-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>

                    <div class="p-4 bg-yellow-100 border border-yellow-300 rounded-lg shadow">
                        <h4 class="text-sm font-semibold text-yellow-800">En attente</h4>
                        <p class="mt-2 text-3xl font-bold text-yellow-900">{{ $stats['en_attente'] ?? 0 }}</p>
                    </div>

                    <div class="p-4 bg-green-100 border border-green-300 rounded-lg shadow">
                        <h4 class="text-sm font-semibold text-green-800">Validées</h4>
                        <p class="mt-2 text-3xl font-bold text-green-900">{{ $stats['validees'] ?? 0 }}</p>
                    </div>

                    <div class="p-4 bg-red-100 border border-red-300 rounded-lg shadow">
                        <h4 class="text-sm font-semibold text-red-800">Rejetées</h4>
                        <p class="mt-2 text-3xl font-bold text-red-900">{{ $stats['rejetees'] ?? 0 }}</p>
                    </div>
                </div>

                {{-- Liste des demandes --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="mb-4 text-lg font-bold text-gray-700">📁 Mes demandes</h3>

                    @if($demandes->isEmpty())
                        <p class="text-gray-500">Vous n’avez encore soumis aucune demande.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 rounded-lg">
                                <thead class="bg-gray-100 text-gray-700 text-sm uppercase">
                                    <tr>
                                        <th class="px-4 py-2 border">#</th>
                                        <th class="px-4 py-2 border">Objet</th>
                                        <th class="px-4 py-2 border">Motif</th>
                                        <th class="px-4 py-2 border">Statut</th>
                                        <th class="px-4 py-2 border">Créée le</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($demandes as $demande)
                                        <tr class="text-sm hover:bg-gray-50">
                                            <td class="px-4 py-2 border">{{ $demande->id }}</td>
                                            <td class="px-4 py-2 border">{{ $demande->objet_modif ?? $demande->objet }}</td>
                                            <td class="px-4 py-2 border">{{ Str::limit($demande->motif, 50) }}</td>
                                            <td class="px-4 py-2 border">
                                                <span class="{{ $demande->getStatutBadgeClass() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 border">{{ $demande->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
