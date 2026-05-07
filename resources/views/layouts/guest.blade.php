<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gem Reptiles') }}@hasSection('title') — @yield('title')@endif</title>
        @stack('meta')

        <!-- Favicons -->
        <link rel="icon" type="image/x-icon" href="https://gemx.sfo3.digitaloceanspaces.com/assets/favicon.ico">
        <link rel="icon" type="image/png" sizes="32x32" href="https://gemx.sfo3.digitaloceanspaces.com/assets/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="https://gemx.sfo3.digitaloceanspaces.com/assets/favicon-16x16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="https://gemx.sfo3.digitaloceanspaces.com/assets/apple-touch-icon.png">
        <link rel="manifest" href="https://gemx.sfo3.digitaloceanspaces.com/assets/site.webmanifest">
        <meta name="msapplication-TileImage" content="https://gemx.sfo3.digitaloceanspaces.com/assets/ms-favicon.png">
        <meta name="msapplication-TileColor" content="#f97316">
        <meta name="theme-color" content="#f97316">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://gemx.sfo3.digitaloceanspaces.com" crossorigin>
        <link rel="preload" href="https://fonts.bunny.net/css?family=montserrat:400,500,600|fauna-one:400&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=montserrat:400,500,600|fauna-one:400&display=swap"></noscript>

        <!-- Critical: body background prevents FOUC when CSS loads async -->
        <style>body{background-color:#9ca3af}</style>
        <!-- Scripts -->
        @production
            <link rel="preload" href="{{ Vite::asset('resources/css/app.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
            <noscript><link rel="stylesheet" href="{{ Vite::asset('resources/css/app.css') }}"></noscript>
            @vite('resources/js/app.js')
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endproduction
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-400 flex flex-col min-h-screen">
        <x-guest-navigation />
        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-gray-300 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        @if(isset($background))
            <div class="w-full min-h-screen flex justify-center items-center bg-[url('https://gemreptiles.com/retic-group-3.png')] bg-top bg-center bg-cover bg-no-repeat">
        @endif
            <div class="mx-auto flex-1 flex flex-col p-8 sm:pt-6">
                {{ $slot }}
            </div>
        @if(isset($background))
            </div>
        @endif

        <x-site-footer />
        @stack('scripts')
    </body>
</html>
