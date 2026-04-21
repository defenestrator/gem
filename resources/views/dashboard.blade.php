<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $user              = auth()->user();
        $totalAnimals      = $user->animals()->count();
        $publishedAnimals  = $user->animals()->where('status', 'published')->count();
        $totalClassifieds  = $user->classifieds()->count();
        $pubClassifieds    = $user->classifieds()->where('status', 'published')->count();
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Quick actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('dashboard.animals.index') }}"
                        class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 text-sm font-semibold">
                        My Animals
                    </a>
                    <a href="{{ route('dashboard.animals.create') }}"
                        class="border border-orange-500 text-orange-600 dark:text-orange-400 py-2 px-4 rounded-lg hover:bg-orange-50 dark:hover:bg-gray-700 text-sm font-semibold">
                        + Add Animal
                    </a>
                    <a href="{{ route('dashboard.classifieds.index') }}"
                        class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 text-sm font-semibold">
                        My Classifieds
                    </a>
                    <a href="{{ route('dashboard.classifieds.create') }}"
                        class="border border-orange-500 text-orange-600 dark:text-orange-400 py-2 px-4 rounded-lg hover:bg-orange-50 dark:hover:bg-gray-700 text-sm font-semibold">
                        + Add Classified
                    </a>
                    <a href="{{ route('profile.edit') }}"
                        class="border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 py-2 px-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-semibold">
                        Account Settings
                    </a>
                </div>
            </div>

            {{-- Import MorphMarket JSON --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Import MorphMarket.com Data</h3>
                @if(session('import_success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                        {{ session('import_success') }}
                    </div>
                @endif
                @error('animals_json')
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-lg">
                        {{ $message }}
                    </div>
                @enderror
                <form method="POST" action="{{ route('dashboard.animals.import.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="animals_json" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            MorphMarket.com JSON File
                        </label>
                        <input type="file" id="animals_json" name="animals_json" accept="application/json" required
                            class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <button type="submit" class="bg-orange-500 text-white py-2 px-6 rounded-lg hover:bg-orange-700 font-semibold">
                        Upload & Sync
                    </button>
                </form>
            </div>

            {{-- Welcome --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Welcome back, {{ $user->name }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $user->email }}</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">
                        Member since {{ $user->created_at->format('F Y') }}
                    </p>
                </div>
                <a href="{{ route('profile.edit') }}"
                    class="self-start sm:self-center bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 font-semibold text-sm whitespace-nowrap">
                    Edit Profile
                </a>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 flex flex-col gap-1">
                    <span class="text-3xl font-bold text-orange-500">{{ $totalAnimals }}</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Animals</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $publishedAnimals }} published</span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 flex flex-col gap-1">
                    <span class="text-3xl font-bold text-green-500">{{ $publishedAnimals }}</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Published Animals</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">visible to all</span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 flex flex-col gap-1">
                    <span class="text-3xl font-bold text-orange-500">{{ $totalClassifieds }}</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Classifieds</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $pubClassifieds }} published</span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 flex flex-col gap-1">
                    <span class="text-3xl font-bold text-green-500">{{ $pubClassifieds }}</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Active Listings</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">published classifieds</span>
                </div>
            </div>

            {{-- Media upload --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Upload Media</h3>
                <x-media-upload />
            </div>


        </div>
    </div>
</x-app-layout>
