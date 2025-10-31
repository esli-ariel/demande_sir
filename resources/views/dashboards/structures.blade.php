<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">🏢 Dashboard Structures Spécialisées</h2>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gray-100">
        <div class="absolute inset-0 z-0 opacity-10"
             style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: 110%;">
        </div>

        <div class="relative z-10 py-12">
            <div class="mx-auto space-y-8 max-w-7xl sm:px-6 lg:px-8">

                {{-- Statistiques globales --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="p-4 bg-blue-100 border border-blue-300 rounded-lg shadow">
                        <h4 class="text-sm font-semibold text-blue-800">Total des demandes</h4>
                        <p class="mt-2 text-3xl font-bold text-blue-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="p-4 bg-yellow-100 border border-yellow-300 rounded-lg shadow">
                        <h4 class="text-sm font-semibold text-yellow-800">En attente</h4>
                        <p class="mt-2 text-3xl font-bold text-yellow-900">{{ $stats['en_attente'] ?? 0 }}</p>
                    </div>
                    <div class="p-4 bg-green-100 border border-green-300 rounded-lg shadow">
                        <h4 class="text-sm font-semibold text-green-800">Validées</h4>
                        <p class="mt-2 text-3xl font-bold text-green-900">{{ $stats['traitees'] ?? 0 }}</p>
                    </div>
                    <div class="p-4 bg-red-100 border border-red-300 rounded-lg shadow">
                        <h4 class="text-sm font-semibold text-red-800">Rejetées</h4>
                        <p class="mt-2 text-3xl font-bold text-red-900">{{ $stats['rejetees'] ?? 0 }}</p>
                    </div>
                </div>

                {{-- Graphique --}}
                <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-bold text-gray-700">📊 Nombre de demandes par structure</h3>
                    <canvas id="chartStructures"></canvas>
                </div>

                {{-- Dernières demandes --}}
                <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-bold text-gray-700">📁 Dernières demandes reçues</h3>
                    @if($demandes->isEmpty())
                        <p class="text-gray-500">Aucune demande enregistrée.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 rounded-lg">
                                <thead class="text-sm text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 border">#</th>
                                        <th class="px-4 py-2 border">Objet</th>
                                        <th class="px-4 py-2 border">Structure</th>
                                        <th class="px-4 py-2 border">Statut</th>
                                        <th class="px-4 py-2 border">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($demandes as $demande)
                                        <tr class="text-sm hover:bg-gray-50">
                                            <td class="px-4 py-2 border">{{ $demande->id }}</td>
                                            <td class="px-4 py-2 border">{{ $demande->objet }}</td>
                                            <td class="px-4 py-2 border">{{ $demande->structure->nom ?? 'N/A' }}</td>
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
