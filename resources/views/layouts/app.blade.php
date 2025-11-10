<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIR-gestion de DMA') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased
                bg-[url('public/images/Raffinerie-SIR.jpeg')] bg-center bg-no-repeat bg-fixed bg-contain "">
        <div class="min-h-screen pt-16 bg-gray-100 ">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="">
                @if(session('success'))
    <div class="p-2 mb-4 text-green-800 bg-green-100 rounded">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="p-2 mb-4 text-red-800 bg-red-100 rounded">
        {{ session('error') }}
    </div>
@endif
  

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
