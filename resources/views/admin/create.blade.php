<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Créer un utilisateur</h2>
    </x-slot>

    <div class="py-6">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="text" name="name" placeholder="Nom" class="w-full p-2 border">
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
</x-app-layout>
