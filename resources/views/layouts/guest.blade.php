<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gem Reptiles')  }} scaly critters for every occcasion</title>

        <!-- Favicons -->
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">
        <meta name="msapplication-TileImage" content="/ms-favicon.png">
        <meta name="theme-color" content="#f97316">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600|fauna-one:400&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100 flex flex-col min-h-screen">
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
