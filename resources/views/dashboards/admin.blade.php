<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-green-800 text--800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
<div class="relative min-h-screen bg-gray-100 overflow-hidden">
        {{-- Image de fond filigrane --}}
        <div class="absolute inset-0 opacity-10 z-0"
             style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: 110%;">
        </div>
    {{-- Contenu principal --}}
        <div class="relative z-10 py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                    {{-- ======= SECTION 1 : STATISTIQUES DES DEMANDES ======= --}}
                    <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">
                        Statistiques générales sur les demandes
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="p-4 bg-green-100 border-l-4 border-green-600 rounded shadow">
                            <h4 class="text-lg font-semibold">Total des demandes</h4>
                            <p class="text-3xl font-bold text-green-700">{{ $totalDemandes ?? 0 }}</p>
                        </div>

                        <div class="p-4 bg-blue-100 border-l-4 border-blue-600 rounded shadow">
                            <h4 class="text-lg font-semibold">Demandes validées</h4>
                            <p class="text-3xl font-bold text-blue-700">{{ $demandesValidees ?? 0 }}</p>
                        </div>

                        <div class="p-4 bg-red-100 border-l-4 border-red-600 rounded shadow">
                            <h4 class="text-lg font-semibold">Demandes refusées</h4>
                            <p class="text-3xl font-bold text-red-700">{{ $demandesRefusees ?? 0 }}</p>
                        </div>

                        <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 rounded shadow">
                            <h4 class="text-lg font-semibold">En attente</h4>
                            <p class="text-3xl font-bold text-yellow-700">{{ $demandesAttente ?? 0 }}</p>
                        </div>
                    </div>

                    {{-- Graphique Chart.js --}}
                    <div class="mt-8">
                        <canvas id="chartDemandes"></canvas>
                    </div>

                    {{-- ======= SECTION 2 : STATISTIQUES UTILISATEURS ======= --}}
                    <h3 class="text-lg font-bold mt-10 mb-4 text-gray-800 border-b pb-2">
                        Statistiques sur les utilisateurs
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="p-4 bg-indigo-100 border-l-4 border-indigo-600 rounded shadow">
                            <h4 class="text-lg font-semibold">Administrateurs</h4>
                            <p class="text-3xl font-bold text-indigo-700">{{ $admins ?? 0 }}</p>
                        </div>

                        <div class="p-4 bg-teal-100 border-l-4 border-teal-600 rounded shadow">
                            <h4 class="text-lg font-semibold">Responsables</h4>
                            <p class="text-3xl font-bold text-teal-700">{{ $responsables ?? 0 }}</p>
                        </div>

                        <div class="p-4 bg-pink-100 border-l-4 border-pink-600 rounded shadow">
                            <h4 class="text-lg font-semibold">Demandeurs</h4>
                            <p class="text-3xl font-bold text-pink-700">{{ $demandeurs ?? 0 }}</p>
                        </div>

                        <div class="p-4 bg-cyan-100 border-l-4 border-cyan-600 rounded shadow">
                            <h4 class="text-lg font-semibold">Agents techniques</h4>
                            <p class="text-3xl font-bold text-cyan-700">{{ $agents ?? 0 }}</p>
                        </div>
                    </div>

                    {{-- Graphique Chart.js utilisateurs --}}
                    <div class="mt-8">
                        <canvas id="chartUsers"></canvas>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Script pour Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx1 = document.getElementById('chartDemandes');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Validées', 'Refusées', 'En attente'],
                datasets: [{
                    label: 'État des demandes',
                    data: [{{ $demandesValidees ?? 0 }}, {{ $demandesRefusees ?? 0 }}, {{ $demandesAttente ?? 0 }}],
                    backgroundColor: ['#16a34a', '#dc2626', '#facc15']
                }]
            },
        });

        const ctx2 = document.getElementById('chartUsers');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Admins', 'Responsables', 'Demandeurs', 'Agents'],
                datasets: [{
                    label: 'Répartition des utilisateurs',
                    data: [{{ $admins ?? 0 }}, {{ $responsables ?? 0 }}, {{ $demandeurs ?? 0 }}, {{ $agents ?? 0 }}],
                    backgroundColor: ['#4f46e5', '#0d9488', '#db2777', '#06b6d4']
                }]
            },
        });
    </script>
    <div class="py-6">
        <h3 class="mb-4 text-lg font-bold">Toutes les demandes</h3>
        <table class="w-full border table-auto">
            <thead>
                <tr class="bg-gray-200">
                    <th>ID</th>
                    <th>Demandeur</th>
                    <th>Objet</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demandes as $demande)
                    <tr>
                        <td>{{ $demande->id }}</td>
                        <td>{{ $demande->user->name }}</td>
                        <td>{{ $demande->objet_modif }}</td>
                        <td>
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
</x-app-layout>
