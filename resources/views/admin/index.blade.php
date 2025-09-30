<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Gestion des utilisateurs</h2>
    </x-slot>

    <div class="py-6">
        <a href="{{ route('users.create') }}" class="px-4 py-2 text-white bg-blue-500 rounded">Nouvel utilisateur</a>

        <table class="w-full mt-4 border table-auto">
            <thead>
                <tr class="bg-gray-200">
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                    <td>
                        <a href="{{ route('users.edit', $user) }}" class="text-green-600">Modifier</a> |
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
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
