<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Détail de la Demande</h2>
    </x-slot>

    <div class="py-6">
        <p><b>Objet :</b> {{ $demande->objet }}</p>
        <p><b>Motif :</b> {{ $demande->motif }}</p>
        <p><b>Statut :</b> {{ $demande->statut }}</p>
        <p><b>Demandeur :</b> {{ $demande->user->name }}</p>
    </div>
</x-app-layout>
