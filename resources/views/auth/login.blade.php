<x-guest-layout>
    <div class="relative flex flex-col items-center justify-center min-h-screen overflow-hidden bg-center bg-cover"
         style="background-image: url('{{ asset('images/Raffinerie-sir.jpeg') }}');">

        <!-- Overlay dégradé vert/orange -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-700/70 via-green-600/60 to-orange-500/60"></div>

        <!-- Carte de connexion -->
        <div class="relative w-full sm:max-w-md bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl p-8 z-10 border border-green-100
                    transform opacity-0 translate-y-6 animate-[fadeInUp_0.8s_ease-out_forwards]">

            <!-- Logo et titre -->
            <div class="flex flex-col items-center mb-6">
                <img src="{{ asset('/images/android-chrome-192x192.png') }}" alt="Logo" 
                     class="h-16 w-auto mb-3 drop-shadow-md animate-[float_3s_ease-in-out_infinite]">
                <h2 class="text-2xl font-bold text-green-700">Connexion à votre compte</h2>
                <p class="mt-1 text-sm text-gray-500">Bienvenue, veuillez vous identifier</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <x-input-label for="email" :value="__('Adresse e-mail')" class="text-green-700" />
                    <x-text-input id="email"
                        class="block w-full mt-1 border-green-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                        type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-orange-600" />
                </div>

                <!-- Password avec icône œil -->
                <div class="relative mb-4">
                    <x-input-label for="password" :value="__('Mot de passe')" class="text-green-700" />
                    <div class="relative">
                        <input id="password"
                            class="block w-full pr-10 mt-1 border-green-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                            type="password" name="password" required autocomplete="current-password" />

                        <!-- Icône œil -->
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
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-orange-600" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center mb-4">
                    <input id="remember_me" type="checkbox"
                        class="text-green-600 border-gray-300 rounded shadow-sm focus:ring-green-500"
                        name="remember">
                    <label for="remember_me" class="text-sm text-gray-700 ms-2">{{ __('Se souvenir de moi') }}</label>
                </div>

                <!-- Actions -->
                <div class="flex flex-col items-center justify-between gap-3 mt-6 sm:flex-row">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">
                            {{ __('Créer un compte') }}
                        </a>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-gray-500 hover:text-green-600">
                                {{ __('Mot de passe oublié ?') }}
                            </a>
                        @endif
                    </div>

                    <x-primary-button
                        class="px-6 py-2 font-semibold text-white transition-all duration-200 bg-green-600 rounded-lg shadow-md hover:bg-green-700 focus:ring-green-500">
                        {{ __('Se connecter') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script pour afficher/masquer le mot de passe -->
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

    <!-- Animations personnalisées -->
    <style>
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(25px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
    </style>
</x-guest-layout>
