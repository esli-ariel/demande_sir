<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-green-800 text--800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
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
                        <td>{{ $demande->objet }}</td>
                        <td>{{ $demande->statut }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
