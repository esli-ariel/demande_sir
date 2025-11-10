<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">🏢 Dashboard Structures Spécialisées</h2>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gray-100">
        <div class="absolute inset-0 z-0 opacity-10"
             style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: cover;">
        </div>

        <div class="relative z-10 py-12">
            <div class="mx-auto space-y-8 max-w-7xl sm:px-6 lg:px-8">

                {{-- Statistiques globales --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

                        <x-stat-card color="green" label="Total" :value="$stats['total'] ?? 0" icon="📄" />
                        <x-stat-card color="blue" label="Validées" :value="$stats['en_attente'] ?? 0" icon="✅" />
                        <x-stat-card color="red" label="Refusées" :value="$stats['traitees'] ?? 0" icon="❌" />
                        <x-stat-card color="yellow" label="En attente" :value="$stats['rejetees'] ?? 0" icon="⏳" />
                    
                </div>

                {{-- Graphique 
                <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-bold text-gray-700">📊 Nombre de demandes par structure</h3>
                    <canvas id="chartStructures"></canvas>
                </div> --}}

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
                                            <td class="px-4 py-2 border">{{ $demande->objet_modif }}</td>
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
