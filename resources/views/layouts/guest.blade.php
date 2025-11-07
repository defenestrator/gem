<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gem Reptiles')  }} captive-bred specimens and pets for every occcasion</title>

        <!-- Favicons -->
        <link rel="icon" type="image/x-icon" href="{{ '/storage/favicon.ico' }}">
        <link rel="icon" type="image/png" sizes="256x256" href="{{ '/storage/favicon-256.png' }}">
        
        <link rel="icon" type="image/png" sizes="100x100" href="{{ '/storage/favicon-100.png' }}">
        <link rel="icon" type="image/png" href="{{ '/storage/favicon.png' }}">
        <link rel="apple-touch-icon" href="{{ '/storage/apple-touch-icon.png' }}">
        <meta name="msapplication-TileImage" content="{{ '/storage/ms-favicon.png' }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">    
        <x-guest-navigation />
        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        @if(isset($background))
            <div class="w-full min-h-screen flex justify-center items-center bg-[url('https://gem.test/retic-group-3.png')] bg-top bg-center bg-cover bg-no-repeat">
        @endif
            <div class="mx-auto min-h-screen flex flex-col p-8 sm:pt-6">
                {{ $slot }}
            </div>
        @if(isset($background))
            </div>
        @endif
    </body>
</html>
