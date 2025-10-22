<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Modifier utilisateur</h2>
    </x-slot>

    <div class="py-6">
         @if ($errors->any())
            <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <input type="text" name="name" value="{{ $user->name }}" class="w-full p-2 border">
            <input type="text" name="prenom" value="{{ $user->prenom }}" class="w-full p-2 border">
            <input type="email" name="email" value="{{ $user->email }}" class="w-full p-2 border">

            <select name="role" class="w-full p-2 border">
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" 
                        {{ $user->roles->contains('name', $role->name) ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">Mettre à jour</button>
        </form>
    </div>
     <!-- Bouton retour -->
    <a href="{{ url()->previous() }}" 
       class="inline-block px-4 py-2 mt-4 text-white bg-gray-600 rounded hover:bg-gray-700">
       ← Retour
    </a>
</x-app-layout>
