<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $user              = auth()->user()->load('seller');
        $totalAnimals      = $user->animals()->count();
        $publishedAnimals  = $user->animals()->where('status', 'published')->count();
        $totalClassifieds  = config('features.classifieds') ? $user->classifieds()->count() : 0;
        $pubClassifieds    = config('features.classifieds') ? $user->classifieds()->where('status', 'published')->count() : 0;
        $totalInquiries    = $user->is_admin ? \App\Models\Inquiry::count() : 0;
        $newInquiries      = $user->is_admin ? \App\Models\Inquiry::where('status', 'new')->count() : 0;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Onboarding prompt --}}
            @unless ($user->profileComplete())
                <div x-data="{ open: true }" x-show="open" x-transition
                    class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4 flex items-start gap-4">
                    <svg class="w-5 h-5 text-orange-500 shrink-0 mt-0.5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M12 1l-12 22h24l-12-22zm-1 8h2v7h-2v-7zm1 11.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-orange-800 dark:text-orange-300">Your profile is incomplete</p>
                        <ul class="mt-1 text-sm text-orange-700 dark:text-orange-400 space-y-0.5 list-disc list-inside">
                            @unless ($user->profile_photo_path)
                                <li>Add a profile photo</li>
                            @endunless
                            @unless ($user->seller)
                                <li>Set up your vendor profile so buyers can find you</li>
                            @endunless
                        </ul>
                        <a href="{{ route('profile.edit') }}"
                            class="inline-block mt-2 text-sm font-semibold text-orange-700 dark:text-orange-200 hover:underline">
                            Complete your profile →
                        </a>
                    </div>
                    <button @click="open = false" class="text-orange-400 hover:text-orange-600 dark:hover:text-orange-200 transition-colors shrink-0" aria-label="Dismiss">
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"/>
                        </svg>
                    </button>
                </div>
            @endunless

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
                    @if(config('features.classifieds'))
                    <a href="{{ route('dashboard.classifieds.index') }}"
                        class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 text-sm font-semibold">
                        My Classifieds
                    </a>
                    <a href="{{ route('dashboard.classifieds.create') }}"
                        class="border border-orange-500 text-orange-600 dark:text-orange-400 py-2 px-4 rounded-lg hover:bg-orange-50 dark:hover:bg-gray-700 text-sm font-semibold">
                        + Add Classified
                    </a>
                    @endif
                    @if($user->is_admin)
                    <a href="{{ route('dashboard.inquiries.index') }}"
                        class="relative bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 text-sm font-semibold inline-flex items-center gap-2">
                        Inquiries
                        @if($newInquiries)
                            <span class="bg-white text-orange-600 text-xs font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $newInquiries }}</span>
                        @endif
                    </a>
                    <a href="{{ route('pulse') }}"
                        class="bg-purple-500 text-white py-2 px-4 rounded-lg hover:bg-purple-700 text-sm font-semibold">
                        Monitoring
                    </a>
                    @endif
                    <a href="{{ route('profile.edit') }}"
                        class="border border-orange-300 dark:border-orange-600 text-gray-600 dark:text-white py-2 px-4 rounded-lg bg-orange-200 dark:bg-orange-700 hover:bg-orange-50 dark:hover:bg-orange-800 text-sm font-semibold">
                        Account Settings
                    </a>
                </div>
            </div>

            @if($user->is_admin)
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
                    <div class="flex items-center gap-3 mb-4">
                        <input type="hidden" name="publish" value="0">
                        <input type="checkbox" id="publish" name="publish" value="1"
                            class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                        <label for="publish" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Publish imported
                        </label>
                    </div>
                    <button type="submit" class="bg-orange-500 text-white py-2 px-6 rounded-lg hover:bg-orange-700 font-semibold">
                        Upload & Sync
                    </button>
                </form>
            </div>

            @endif

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
                @if($user->is_admin)
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
                @endif
                @if(config('features.classifieds'))
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
                @endif
                @if($user->is_admin)
                <a href="{{ route('dashboard.inquiries.index') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 flex flex-col gap-1 hover:shadow-md transition">
                    <span class="text-3xl font-bold {{ $newInquiries ? 'text-orange-500' : 'text-blue-500' }}">{{ $totalInquiries }}</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Inquiries</span>
                    <span class="text-xs {{ $newInquiries ? 'text-orange-500 font-semibold' : 'text-gray-400 dark:text-gray-500' }}">
                        {{ $newInquiries ? $newInquiries . ' new' : 'none new' }}
                    </span>
                </a>
                @endif
            </div>



        </div>
    </div>
</x-app-layout>
