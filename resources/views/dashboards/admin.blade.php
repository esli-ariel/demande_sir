<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-green-800">
            {{ __('Tableau de bord - Administration') }}
        </h2>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gray-100">
        {{-- Filigrane --}}
        <div class="absolute inset-0 z-0 opacity-10"
             style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: cover;">
        </div>

        {{-- Contenu principal --}}
        <div class="relative z-10 py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 bg-white rounded-lg shadow-xl">

                    {{-- SECTION 1 : STATISTIQUES GÉNÉRALES --}}
                    <h3 class="pb-2 mb-6 text-lg font-bold text-gray-800 border-b">
                        📈 Statistiques générales des demandes
                    </h3>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                        <x-stat-card color="green" label="Total" :value="$totalDemandes ?? 0" icon="📄" />
                        <x-stat-card color="blue" label="Validées" :value="$demandesValidees ?? 0" icon="✅" />
                        <x-stat-card color="red" label="Refusées" :value="$demandesRefusees ?? 0" icon="❌" />
                        <x-stat-card color="yellow" label="En attente" :value="$demandesAttente ?? 0" icon="⏳" />
                    </div>

                    {{-- GRAPHIQUES PRINCIPAUX --}}
                    <div class="grid grid-cols-1 gap-8 mt-10 md:grid-cols-2">
                        <div class="p-4 rounded-lg shadow-sm bg-gray-50">
                            <h4 class="mb-2 font-semibold text-center text-gray-700">Répartition des demandes</h4>
                            <canvas id="chartDemandes"></canvas>
                        </div>

                        <div class="p-4 rounded-lg shadow-sm bg-gray-50">
                            <h4 class="mb-2 font-semibold text-center text-gray-700">Évolution mensuelle</h4>
                            <canvas id="chartEvolution"></canvas>
                        </div>
                    </div>

                    {{-- SECTION 2 : STATISTIQUES UTILISATEURS --}}
                    <h3 class="pb-2 mt-12 mb-6 text-lg font-bold text-gray-800 border-b">
                        👥 Statistiques sur les utilisateurs
                    </h3>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                        <x-stat-card color="indigo" label="Administrateurs" :value="$admins ?? 0" icon="🛠️" />
                        <x-stat-card color="teal" label="Responsables" :value="$responsables ?? 0" icon="📋" />
                        <x-stat-card color="pink" label="Demandeurs" :value="$demandeurs ?? 0" icon="🙋‍♂️" />
                        <x-stat-card color="blue" label="Agents" :value="$agents ?? 0" icon="👷" />
                    </div>

                    <div class="grid grid-cols-1 gap-8 mt-10 md:grid-cols-2">
                        <div class="p-4 rounded-lg shadow-sm bg-gray-50">
                            <h4 class="mb-2 font-semibold text-center text-gray-700">Répartition des utilisateurs</h4>
                            <canvas id="chartUsers"></canvas>
                        </div>

                        <div class="p-4 rounded-lg shadow-sm bg-gray-50">
                            <h4 class="mb-2 font-semibold text-center text-gray-700">Taux de validation (%)</h4>
                            <canvas id="chartTaux"></canvas>
                        </div>
                    </div>

                    {{-- TABLE DES DEMANDES --}}
                    <div class="mt-12">
                        <h3 class="mb-4 text-lg font-bold">📋 Liste récente des demandes</h3>
                        <div class="overflow-x-auto rounded-lg shadow bg-gray-50">
                            <table class="w-full border-collapse">
                                <thead class="text-white bg-green-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left">#</th>
                                        <th class="px-4 py-2 text-left">Demandeur</th>
                                        <th class="px-4 py-2 text-left">Objet</th>
                                        <th class="px-4 py-2 text-left">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($demandes as $demande)
                                        <tr class="border-b hover:bg-gray-100">
                                            <td class="px-4 py-2">{{ $demande->id }}</td>
                                            <td class="px-4 py-2">{{ $demande->user->name }}</td>
                                            <td class="px-4 py-2">{{ $demande->objet_modif }}</td>
                                            <td class="px-4 py-2">
                                                <span class="{{ $demande->getStatutBadgeClass() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Données principales
        const valides = {{ $demandesValidees ?? 0 }};
        const refusees = {{ $demandesRefusees ?? 0 }};
        const attente = {{ $demandesAttente ?? 0 }};
        const total = {{ $totalDemandes ?? 1 }};
        const tauxValidation = ((valides / total) * 100).toFixed(1);
        const tauxRefus = ((refusees / total) * 100).toFixed(1);
        const evolutionLabels = {!! json_encode($labels) !!};
        const evolutionData = {!! json_encode($data) !!};
        
        // Diagramme des demandes
        new Chart(document.getElementById('chartDemandes'), {
            type: 'doughnut',
            data: {     
                    labels: ['Validées', 'Refusées', 'En attente'],
                datasets:       [{
                    data: [valides, refusees, attente],
                    backgroundColor: ['#22c55e', '#ef4444', '#facc15']
                    }]
                },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Évolution mensuelle (exemple statique à remplacer par des vraies données)

    

    new Chart(document.getElementById('chartEvolution'), {
        type: 'line',
        data: {
            labels: evolutionLabels,
            datasets: [{
                label: 'Demandes créées',
                data: evolutionData,
                borderColor: '#16a34a',
                backgroundColor: '#bbf7d0',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });



        // Diagramme des utilisateurs
        new Chart(document.getElementById('chartUsers'), {
        type: 'pie',
                data: {
                labels: ['Admins', 'Responsables', 'Demandeurs', 'Agents'],
                datasets: [{
                    data: [{{ $admins ?? 0 }}, {{ $responsables ?? 0 }}, {{ $demandeurs ?? 0 }}, {{ $agents ?? 0 }}],
                    backgroundColor: ['#4f46e5', '#0d9488', '#db2777', '#06b6d4']
                    }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        // Taux de validation
        new Chart(document.getElementById('chartTaux'), {
            type: 'bar',
            data: {
                labels: ['Taux de validation', 'Taux de refus'],
                datasets: [{
                    data: [tauxValidation, tauxRefus],
                    backgroundColor: ['#16a34a', '#dc2626']
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, max: 100 } },
                plugins: { legend: { display: false } }
            }
        });
    </script>

    {{-- Composant carte statistique --}}
    @once
        @push('components')
            @component('components.stat-card')
            @endcomponent
        @endpush
    @endonce
</x-app-layout>
