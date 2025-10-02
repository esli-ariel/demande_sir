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
                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out border border-transparent rounded-md bg-lime-800 hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Modifier</a> |
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700">Supprimer</button>
                        </form>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
