<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Demandes à traiter (Service technique)</h2>
    </x-slot>

    <div class="relative min-h-screen py-12 bg-gray-100">
        <div class="absolute inset-0 opacity-10"
            style="background-image: url('{{ asset('images/Raffinerie-SIR.jpeg') }}');
                   background-repeat: no-repeat;
                   background-position: center;
                   background-size: cover;">
        </div>

        <div class="relative z-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-bold text-gray-700">Demandes à exécuter</h3>

                @forelse($demandes as $demande)
                    <div class="p-4 mb-4 border rounded bg-gray-50 hover:shadow">
                        <p class="font-semibold">#{{ $demande->id }} — {{ $demande->structure }}</p>
                        <p>Objet : {{ $demande->objet_modif }}</p>

                        @role('service_technique')
                        <div class="flex flex-wrap gap-2 mt-3">
                            <form action="{{ route('demandes.traiter', $demande) }}" method="POST">
                                @csrf
                                <input type="date" name="date_debut" class="p-1 border rounded" required>
                                <input type="date" name="date_fin" class="p-1 border rounded" required>
                                <textarea name="travaux_realises" placeholder="Travaux effectués" class="w-64 p-1 border rounded" required></textarea>
                                <button type="submit" class="px-3 py-1 text-white bg-green-600 rounded">Clôturer</button>
                            </form>
                        </div>
                        @endrole
                    </div>
                @empty
                    <p class="text-gray-500">Aucune demande à traiter pour l'instant.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
