<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Mes demandes</h2>
    </x-slot>

    <div class="py-6">
        <a href="{{ route('demandes.create') }}" 
           class="px-4 py-2 text-white bg-blue-500 rounded">Nouvelle Demande</a>

        <table class="w-full mt-4 border table-auto">
            <thead>
                <tr class="bg-gray-200">
                    <th>ID</th>
                    <th>Objet</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demandes as $demande)
                    <tr>
                        <td>{{ $demande->id }}</td>
                        <td>{{ $demande->objet }}</td>
                        <td>{{ $demande->statut }}</td>
                        <td>
                            <a href="{{ route('demandes.show', $demande) }}" class="text-blue-600">Voir</a> |
                            <a href="{{ route('demandes.edit', $demande) }}" class="text-green-600">Modifier</a> |
                            <form action="{{ route('demandes.destroy', $demande) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
