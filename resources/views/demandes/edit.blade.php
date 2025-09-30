<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Modifier Demande</h2>
    </x-slot>

    <div class="py-6">
        <form action="{{ route('demandes.update', $demande) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <input type="text" name="objet" value="{{ $demande->objet }}" class="w-full p-2 border">
            <textarea name="motif" class="w-full p-2 border">{{ $demande->motif }}</textarea>
            <textarea name="situation_souhaitee" class="w-full p-2 border">{{ $demande->situation_souhaitee }}</textarea>

            <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded">Mettre à jour</button>
        </form>
    </div>
</x-app-layout>
