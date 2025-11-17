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
            <input id="password" type="password" name="password" placeholder="Mot de passe" class="w-full p-2 border">
            
            <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-green-600 hover:text-green-800 focus:outline-none">
                            <!-- Icône fermée -->
                            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 3l18 18M10.58 10.59a3 3 0 004.24 4.24M9.88 9.88a5 5 0 016.24 6.24m4.47-3.12A10.97 10.97 0 0012 5c-4.48 0-8.27 2.94-9.54 7 1.03 3.3 3.77 5.94 7.1 6.77" />
                            </svg>

                            <!-- Icône ouverte -->
                            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor" class="hidden w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
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


     <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            } else {
                input.type = 'password';
                eyeClosed.classList.remove('hidden');
                eyeOpen.classList.add('hidden');
            }
        }
    </script>

</x-app-layout>
