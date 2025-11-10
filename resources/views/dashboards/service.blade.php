<x-app-layout>
    <x-slot name="header" class="bg-green-900">
        <h2 class="text-xl font-semibold">🛠️ Dashboard Service Technique</h2>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gray-100">
        {{-- Image de fond --}}
        <div class="absolute inset-0 z-0 opacity-10"
             style="background-image: url('{{ asset('images/Raffinerie-sir.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: cover;">
        </div>

        <div class="relative z-10 py-12">
            <div class="mx-auto space-y-8 max-w-7xl sm:px-6 lg:px-8">

                {{-- Message de bienvenue --}}
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        👋 Bonjour, <b>{{ Auth::user()->name }}</b> !
                        <p class="text-sm text-gray-600">
                            Voici un aperçu des demandes en cours ou traitées par le Service Technique.
                        </p>
                    </div>
                </div>

                {{-- Statistiques globales --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                   
                    <x-stat-card color="green" label="Total" :value="$stats['total'] ?? 0" icon="📄" />
                    <x-stat-card color="blue" label="Validées" :value="$stats['validees'] ?? 0" icon="✅" />
                    <x-stat-card color="indigo" label="Terminées" :value="$stats['terminees'] ?? 0" icon="❌" />
                    <x-stat-card color="yellow" label="En attente" :value="$stats['en_cours'] ?? 0" icon="⏳" />
                        
                
                </div>

                {{-- Liste des demandes --}}
                <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-bold text-gray-700">📁 Demandes suivies par le Service Technique</h3>

                    @if($demandes->isEmpty())
                        <p class="text-gray-500">Aucune demande à afficher.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 rounded-lg">
                                <thead class="text-sm text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 border">#</th>
                                        <th class="px-4 py-2 border">Objet</th>
                                        <th class="px-4 py-2 border">Demandeur</th>
                                        <th class="px-4 py-2 border">Statut</th>
                                        <th class="px-4 py-2 border">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($demandes as $demande)
                                        <tr class="text-sm hover:bg-gray-50">
                                            <td class="px-4 py-2 border">{{ $demande->id }}</td>
                                            <td class="px-4 py-2 border">{{ $demande->objet_modif ?? $demande->objet }}</td>
                                            <td class="px-4 py-2 border">{{ $demande->user->name ?? 'N/A' }}</td>
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
