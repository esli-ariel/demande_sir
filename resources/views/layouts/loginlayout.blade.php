<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SIR-Gestion DMA') }}</title>

    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="p-0 m-0 antialiased">
    {{ $slot }}
</body>
</html>
