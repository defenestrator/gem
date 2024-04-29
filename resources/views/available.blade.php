<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Available Animals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <h2 class="font-bold dark:text-white text-lg">Gem Reptiles Available Animals</h2>
            <ul class="list-disc" hx-boost="true">
                <li><a href="/ball-pythons">Ball Pythons</a></li>
                <li><a href="/boas">Boas</a></li>
                <li><a href="/colubrids">Colubrids</a></li>
                <li><a href="/geckos">Geckos</a></li>
                <li><a href="/monitors">Monitors</a></li>
                <li><a href="/pythons">Pythons</a></li>
                <li><a href="/skinks">Skinks</a></li>
                <li><a href="/tegus">Tegus</a></li>
                <li><a href="/tortoises">Tortoises</a></li>
                <li><a href="/uromastyx">Uromastyx</a></li>
            </ul>
            </div>
        </div>
    </div>
</x-guest-layout>
