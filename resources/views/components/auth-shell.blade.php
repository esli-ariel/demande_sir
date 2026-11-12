@props(['title', 'description'])
<x-guest-layout>
    <x-slot:pageTitle>{{ $title }} · Gestion des DMA</x-slot:pageTitle>
    <div class="auth-page" x-data="authPage" @pageshow.window="submitting = false">
        <img class="auth-watermark" src="{{ asset('images/Raffinerie-SIR.jpeg') }}" alt="" aria-hidden="true" decoding="async" fetchpriority="low">
        <header class="auth-header">
            <a class="auth-brand" href="{{ route('login') }}" aria-label="Gestion des DMA — Connexion">
                <img src="{{ asset('images/android-chrome-192x192.png') }}" alt="SIR" width="44" height="44">
                <span>Gestion des DMA<small>Société Ivoirienne de Raffinage</small></span>
            </a>
            <button class="auth-theme" type="button" x-cloak @click="toggleTheme()" :aria-label="dark ? 'Activer le mode jour' : 'Activer le mode nuit'" :aria-pressed="dark.toString()">
                <svg x-show="!dark" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20.9 13.1A9 9 0 0 1 10.9 3.1 9 9 0 1 0 20.9 13.1Z"/></svg>
                <svg x-show="dark" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></svg>
                <span x-text="dark ? 'Jour' : 'Nuit'">Nuit</span>
            </button>
        </header>
        <main class="auth-main">
            <div class="auth-intro">
                <span class="auth-intro-line" aria-hidden="true"></span>
                <h2>De la demande<br>à la réception.</h2>
                <p>Créez vos demandes de modification et retrouvez leur avancement dans un même espace.</p>
            </div>
            <section class="auth-card" aria-labelledby="auth-title">
                <header class="auth-card-heading"><h1 id="auth-title">{{ $title }}</h1><p>{{ $description }}</p></header>
                @if(session('status'))<div class="auth-status" role="status">{{ session('status') }}</div>@endif
                @if($errors->any())
                    <div class="auth-error-summary" role="alert" tabindex="-1" data-auth-errors>
                        <strong>Veuillez vérifier les informations indiquées.</strong>
                        <ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                    </div>
                @endif
                {{ $slot }}
            </section>
        </main>
        <footer class="auth-footer">Gestion des demandes de modification · SIR</footer>
    </div>
</x-guest-layout>
