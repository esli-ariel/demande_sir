<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Nouvelle Demande</h2>
    </x-slot>

    <div class="py-6">
        <form action="{{ route('demandes.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="text" name="structure" placeholder="Structure" class="w-full p-2 border">
            <input type="text" name="unite" placeholder="Unité" class="w-full p-2 border">
            <input type="text" name="objet" placeholder="Objet" class="w-full p-2 border">
            <textarea name="motif" placeholder="Motif" class="w-full p-2 border"></textarea>
            <input type="text" name="repere" placeholder="Repère" class="w-full p-2 border">
            <textarea name="situation_existante" placeholder="Situation existante" class="w-full p-2 border"></textarea>
            <textarea name="situation_souhaitee" placeholder="Situation souhaitée" class="w-full p-2 border"></textarea>

            <button type="submit" class="px-4 py-2 text-white bg-green-500 rounded">Enregistrer</button>
        </form>
    </div>
</x-app-layout>
