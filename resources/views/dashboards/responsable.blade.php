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

    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard Responsable</h2>
    </x-slot>

    <div class="py-6">
        <h3 class="mb-4 text-lg font-bold">Demandes à valider</h3>
        <!--<ul>
            @foreach($demandes as $demande)
                <li>{{ $demande->objet }} -
                     Statut : {{ $demande->statut }}</li>
            @endforeach
        </ul>-->
       <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                
                <h3 class="mb-4 text-lg font-bold">Demandes en attente de validation</h3>

                @if($demandes->isEmpty())
                    <p class="text-gray-500">Aucune demande en attente.</p>
                @else
                    <table class="w-full border border-collapse border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-3 py-2 border">ID</th>
                                <th class="px-3 py-2 border">Objet</th>
                                <th class="px-3 py-2 border">Demandeur</th>
                                <th class="px-3 py-2 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($demandes as $demande)
                                <tr>
                                    <td class="px-3 py-2 border">{{ $demande->id }}</td>
                                    <td class="px-3 py-2 border">{{ $demande->objet }}</td>
                                    <td class="px-3 py-2 border">{{ $demande->user->name }}</td>
                                    <td class="flex gap-2 px-3 py-2 border">
                                        <!-- Bouton Valider -->
                                        <form action="{{ route('demandes.valider', $demande) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-white bg-green-500 rounded">
                                                Valider
                                            </button>
                                        </form>

                                        <!-- Bouton Rejeter -->
                                        <form action="{{ route('demandes.rejeter', $demande) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-white bg-red-500 rounded">
                                                Rejeter
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            </div>
        </div>
    </div>

    </div>

</x-app-layout>
