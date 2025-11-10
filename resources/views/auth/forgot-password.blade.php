<x-guest-layout>
    <div class="relative flex items-center justify-center min-h-screen bg-center bg-cover"
        style="background-image: url('{{ asset('images/Raffinerie-sir.jpeg') }}');">
        
        <!-- Dégradé vert/orange semi-transparent -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-600/70 via-green-500/60 to-orange-400/60"></div>

        <!-- Conteneur du formulaire -->
        <div class="relative z-10 w-full max-w-md p-8 shadow-2xl bg-white/90 backdrop-blur-md rounded-2xl">
            <h2 class="mb-4 text-3xl font-bold text-center text-green-700">
                Réinitialiser le mot de passe
            </h2>

            <p class="mb-6 text-sm text-center text-gray-600">
                {{ __('Mot de passe oublié ? Pas de problème. Indique ton adresse e-mail et nous t’enverrons un lien pour en définir un nouveau.') }}
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" :value="__('Adresse e-mail')" />
                    <x-text-input id="email"
                        class="block w-full mt-1 border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500"
                        type="email" name="email" :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('login') }}"
                        class="text-sm font-medium text-green-700 transition hover:text-orange-500">
                        {{ __('Retour à la connexion') }}
                    </a>

                    <x-primary-button class="px-6 py-2 font-semibold text-white transition duration-300 bg-green-600 rounded-lg shadow-md hover:bg-orange-500">
                        {{ __('Envoyer le lien') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
