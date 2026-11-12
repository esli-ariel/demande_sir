<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Désactiver le compte') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Votre accès sera bloqué et vos sessions seront fermées. Vos demandes et décisions resteront conservées. Un administrateur pourra réactiver votre compte.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Désactiver le compte') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty() || $errors->has('account')" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Voulez-vous désactiver votre compte ?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Saisissez votre mot de passe pour confirmer la désactivation de votre compte. Vos demandes et décisions seront conservées.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Mot de Passe') }}"
                />

                <x-input-error :messages="$errors->get('account')" class="mt-2" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Annuler') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Désactiver le compte') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
