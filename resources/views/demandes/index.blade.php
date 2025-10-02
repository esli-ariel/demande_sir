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
                    <th>Soumissions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demandes as $demande)
                    <tr>
                        <td>{{ $demande->id }}</td>
                        <td>{{ $demande->objet }}</td>
                        <td>
                            <span class="{{ $demande->getStatutBadgeClass() }}">
                        {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                    </span>
                        </td>
                        <td>
                            
                        <!-- Dans demandes/index.blade.php par exemple -->
                            @can('view', $demande)
                                <a href="{{ route('demandes.show', $demande) }}" class="text-blue-500">Voir</a>
                            @endcan

                            @can('update', $demande)
                                <a href="{{ route('demandes.edit', $demande) }}" class="text-green-500">Modifier</a>
                            @endcan

                            @can('delete', $demande)
                            <form action="{{ route('demandes.destroy', $demande) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500">Supprimer</button>
                            </form>
                            @endcan
                        </td>
                        <td>
                            @if($demande->statut === 'brouillon')
                                <form action="{{ route('demandes.submit', $demande) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded">
                                    Soumettre au responsable
                                    </button>
                                </form>
                            @endif
                        </td>  
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
