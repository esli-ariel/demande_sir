<x-app-layout>
 <!--   <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-green-800 text--800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot> -->
 <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard Demandeur</h2>
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
        <h3 class="mb-4 text-lg font-bold">Mes demandes</h3>
        <ul>
            @foreach($demandes as $demande)
                <li>{{ $demande->objet }} - Statut : {{ $demande->statut }}</li>
            @endforeach
        </ul>
    </div>

</x-app-layout>
