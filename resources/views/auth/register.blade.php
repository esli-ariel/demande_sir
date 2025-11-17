<x-guest-layout>
    <div class="relative flex items-center justify-center min-h-screen bg-center bg-cover"
        style="background-image: url('{{ asset('images/Raffinerie-sir.jpeg') }}');">
        <!-- Dégradé vert/orange semi-transparent -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-600/70 via-green-500/60 to-orange-400/60"></div>

        <!-- Conteneur du formulaire -->
        <div class="relative z-10 w-full max-w-md p-8 shadow-2xl bg-white/90 backdrop-blur-md rounded-2xl">
            
        <!-- Logo et titre -->
            <div class="flex flex-col items-center mb-6">
                <img src="{{ asset('/images/android-chrome-192x192.png') }}" alt="Logo" class="h-16 w-auto mb-3 drop-shadow-md animate-[float_3s_ease-in-out_infinite]">
                <h2 class="mb-6 text-3xl font-bold text-center text-green-700">Créer un compte</h2>
                <p class="mt-1 text-sm text-gray-500">Bienvenue, veuillez creer un nouveau compte</p>
            </div>
        

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nom -->
                <div>
                    <x-input-label for="name" :value="__('Nom')" />
                    <x-text-input id="name" class="block w-full mt-1 border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                        type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Prénom -->
                <div class="mt-4">
                    <x-input-label for="prenom" :value="__('Prénom')" />
                    <x-text-input id="prenom" class="block w-full mt-1 border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                        type="text" name="prenom" :value="old('prenom')" required autocomplete="prenom" />
                    <x-input-error :messages="$errors->get('prenom')" class="mt-2" />
                </div>

                <!-- Email -->
                <div class="mt-4">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block w-full mt-1 border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                        type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Mot de passe -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Mot de passe')" />
                    <x-text-input id="password" class="block w-full mt-1 border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                        type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirmation -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                    <x-text-input id="password_confirmation" class="block w-full mt-1 border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                        type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Rôle -->
                <div class="mt-4">
                <x-input-label for="role" :value="_('role')" />
                    <select name="role" class="w-full p-2 border" id="">
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                    </select>
                </div>
                
                <!-- Lien & bouton -->
                <div class="flex items-center justify-between mt-6">
                    <a class="text-sm font-medium text-green-700 transition hover:text-orange-500"
                        href="{{ route('login') }}">
                        {{ __('Déjà inscrit ? Se connecter') }}
                    </a>

                    <x-primary-button class="px-6 py-2 font-semibold text-white transition duration-300 bg-green-600 rounded-lg shadow-md hover:bg-orange-500">
                        {{ __('S’inscrire') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
