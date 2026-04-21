<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $classified->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Status Badge -->
                <div class="mb-6">
                    <span class="inline-block px-3 py-1 text-sm rounded-full 
                        @if ($classified->status === 'published')
                            bg-green-100 text-green-800
                        @elseif ($classified->status === 'draft')
                            bg-gray-100 text-gray-800
                        @else
                            bg-red-100 text-red-800
                        @endif
                    ">
                        {{ ucfirst($classified->status) }}
                    </span>
                </div>

                <!-- Title and Price -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-2">
                        {{ $classified->title }}
                    </h1>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        ${{ number_format($classified->price, 2) }}
                    </p>
                </div>

                <!-- Seller Info -->
                @if ($classified->user)
                    <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300">
                            <span class="font-semibold">Seller:</span> {{ $classified->user->name }}
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            <span class="font-semibold">Posted:</span> {{ $classified->created_at->format('M d, Y') }}
                        </p>
                    </div>
                @endif

                <!-- Animal Info -->
                @if ($classified->animal)
                    <div class="mb-6 border-b border-gray-300 dark:border-gray-600 pb-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
                            Animal Information
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-semibold">Name</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $classified->animal->pet_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-semibold">Species</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $classified->animal->species_id ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-semibold">Age</p>
                                <p class="text-gray-800 dark:text-gray-200">N/A</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Description -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
                        Description
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        {{ $classified->description }}
                    </p>
                </div>

                <!-- Action Buttons -->
                @auth
                    @if (auth()->user()->id === $classified->user_id)
                        <div class="flex gap-4">
                            <a href="{{ route('dashboard.classifieds.edit', $classified) }}" class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-700 font-semibold">
                                Edit
                            </a>
                            <form action="{{ route('dashboard.classifieds.destroy', $classified) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this classified?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-700 font-semibold">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                @endauth

                <!-- Back Link -->
                <div class="mt-6">
                    @if (request()->is('dashboard/*'))
                        <a href="{{ route('dashboard.classifieds.index') }}" class="text-orange-600 dark:text-orange-400 hover:underline font-semibold">
                            ← Back to My Classifieds
                        </a>
                    @else
                        <a href="{{ route('classifieds.index') }}" class="text-orange-600 dark:text-orange-400 hover:underline font-semibold">
                            ← Back to All Classifieds
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
