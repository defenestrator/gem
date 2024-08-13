<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Reptiles for every occasion</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 selection:bg-orange-500 selection:text-white">
            <x-guest-navigation />
            <div class="flex flex-grow justify-center items-center">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-200">Welcome to Gem Reptiles</h1>
                    <h2>Focused on captive-bred Pythons and select Colubrids</h2>
                    <p class="mt-4 text-lg text-orange-600 dark:text-gray-400">We are the wild-type</p>
                </div>
            </div>
        </div>
    </body>
</html>
