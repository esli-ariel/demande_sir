<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Créer un utilisateur</h2>
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
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="text" name="name" placeholder="Nom" class="w-full p-2 border">
            <input type="text" name="prenom" placeholder="Prenom" class="w-full p-2 border">
            <input type="email" name="email" placeholder="Email" class="w-full p-2 border">
            <input type="password" name="password" placeholder="Mot de passe" class="w-full p-2 border">
            <input type="password" name="password_confirmation" placeholder="Confirmer mot de passe" class="w-full p-2 border">

            <select name="role" class="w-full p-2 border">
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700">Créer</button>
        </form>
    </div>
     <!-- Bouton retour -->
    <a href="{{ url()->previous() }}" 
       class="inline-block px-4 py-2 mt-4 text-white bg-gray-600 rounded hover:bg-gray-700">
       ← Retour
    </a>
</x-app-layout>
