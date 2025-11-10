<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">📊 Dashboard Demandeur</h2>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gray-100">
        {{-- Image de fond --}}
        <div class="absolute inset-0 z-0 opacity-10"
             style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: cover;">
        </div>

        <div class="relative z-10 py-8">
            <div class="mx-auto space-y-8 max-w-7xl sm:px-6 lg:px-8">

                {{-- Message de bienvenue --}}
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        👋 Bonjour, <b>{{ Auth::user()->name }}</b> !
                        <p class="text-sm text-gray-600">
                            Voici le récapitulatif de vos demandes et leur statut.
                        </p>
                    </div>
                </div>

                {{-- Cartes statistiques --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-stat-card color="blue" label="Total" :value="$totalDemandes ?? 0" icon="📄" />
                    <x-stat-card color="yellow" label="En attente" :value="$demandesEnCours ?? 0" icon="⏳" />
                    <x-stat-card color="green" label="Validées" :value="$demandesValidees ?? 0" icon="✅" />
                    <x-stat-card color="red" label="Rejetées" :value="$demandesRejetees ?? 0" icon="❌" />
                </div>

                {{-- Graphiques compacts --}}
                <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
                    <div class="p-4 bg-white rounded-lg shadow-sm">
                        <h4 class="mb-2 font-semibold">Répartition des demandes</h4>
                        <canvas id="repartitionChart" class="w-full h-48"></canvas>
                    </div>
                    <div class="p-4 bg-white rounded-lg shadow-sm">
                        <h4 class="mb-2 font-semibold">Taux de validation (%)</h4>
                        <canvas id="tauxChart" class="w-full h-48"></canvas>
                    </div>
                </div>

                {{-- Tableau des demandes --}}
                <div class="p-4 mt-6 bg-white rounded-lg shadow-sm">
                    <h3 class="mb-4 text-lg font-bold text-gray-700">📁 Mes demandes</h3>
                    @if($demandes->isEmpty())
                        <p class="text-gray-500">Vous n’avez encore soumis aucune demande.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 rounded-lg">
                                <thead class="text-sm text-gray-700 uppercase bg-gray-100">
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

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Répartition des demandes
        new Chart(document.getElementById('repartitionChart'), {
            type: 'pie',
            data: {
                labels: ['Validées', 'Rejetées', 'En attente'],
                datasets: [{
                    data: [{{ $demandesValidees }}, {{ $demandesRejetees }}, {{ $demandesEnCours }}],
                    backgroundColor: ['#22c55e', '#ef4444', '#facc15']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Taux de validation
        new Chart(document.getElementById('tauxChart'), {
            type: 'doughnut',
            data: {
                labels: ['Validées', 'Rejetées'],
                datasets: [{
                    data: [{{ $demandesValidees }}, {{ $demandesRejetees }}],
                    backgroundColor: ['#22c55e', '#ef4444']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>

</x-app-layout>
