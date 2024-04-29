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
        <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900 selection:bg-orange-500 selection:text-white">
            @if (Route::has('login'))
                <div class="fixed bottom-0 right-0 p-4 text-right z-10">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-orange-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-orange-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-7xl p-8">
                <div class="flex">
                    <h2 >The Wild Type</h2>
                    
                </div>
                <div class="flex"><img src="favicon.png" /></div>
                <div class="w-7xl border-b-2 mb-6 border-green-400">                    
                    <h2 class="pb-2 font-bold dark:text-white text-lg">Gem Reptiles</h2>
                </div>
                <div class="flex">
                    <a hx-boost="true" href="/available"><h3 class="button text-orange-500 font-black text-xl uppercase hover:text-green-300 transition-all duration-400 ">Price List</h3></a>
                </div>
            </div>
        </div>
    </body>
</html>
