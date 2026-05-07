<x-app-layout>
    @push('meta')
    <meta name="description" content="Reptile classified ads on Gem Reptiles. Buy and sell captive-bred snakes, lizards, and other reptiles.">
    @endpush
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Classified Ads
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search & Filter -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Search & Filter</h3>
                <form method="GET" action="{{ route('classifieds.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search classifieds..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Price</label>
                        <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="Min" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Price</label>
                        <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Max" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 flex-1">
                            Filter
                        </button>
                        <a href="{{ route('classifieds.index') }}" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-700 flex-1 text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Sort Options -->
            <div class="mb-6 flex flex-wrap gap-2">
                <span class="text-gray-600 dark:text-gray-400 font-semibold">Sort by:</span>
                <a href="{{ route('classifieds.index', array_merge(request()->query(), ['sort' => 'recent'])) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ $currentSort === 'recent' ? 'bg-orange-600 text-white' : 'bg-orange-500 text-white hover:bg-orange-600' }}">
                    Newest
                </a>
                <a href="{{ route('classifieds.index', array_merge(request()->query(), ['sort' => 'price-low'])) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ $currentSort === 'price-low' ? 'bg-orange-600 text-white' : 'bg-orange-500 text-white hover:bg-orange-600' }}">
                    Lowest Price
                </a>
                <a href="{{ route('classifieds.index', array_merge(request()->query(), ['sort' => 'price-high'])) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ $currentSort === 'price-high' ? 'bg-orange-600 text-white' : 'bg-orange-500 text-white hover:bg-orange-600' }}">
                    Highest Price
                </a>
                <a href="{{ route('classifieds.index', array_merge(request()->query(), ['sort' => 'oldest'])) }}" class="px-3 py-1 rounded-lg text-sm font-medium {{ $currentSort === 'oldest' ? 'bg-orange-600 text-white' : 'bg-orange-500 text-white hover:bg-orange-600' }}">
                    Oldest
                </a>
            </div>

            @if ($classifieds->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($classifieds as $classified)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                            @if ($classified->media->isNotEmpty())
                                <a href="{{ route('classifieds.show', $classified) }}">
                                    <img src="{{ $classified->media->first()->url }}" alt="{{ $classified->title }}"
                                        class="w-full aspect-square object-cover">
                                </a>
                            @endif
                            <div class="p-4">
                            <h3 class="text-lg font-semibold text-orange-600 dark:text-orange-400 mr-2 mb-2">
                                <a href="{{ route('classifieds.show', $classified) }}" class="hover:underline">
                                    {{ $classified->title }}
                                </a>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                {{ Str::limit($classified->description, 100) }}
                            </p>
                            @if ($classified->user)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                    <span class="font-semibold">Seller:</span> {{ $classified->user->name }}
                                </p>
                            @endif
                            <div class="flex items-center gap-3 mb-4">
                                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                    ${{ number_format($classified->price, 2) }}
                                </p>
                                <a href="{{ route('classifieds.inquiries.create', $classified) }}"
                                    class="bg-orange-500 hover:bg-orange-700 text-white text-xs font-semibold py-1 px-3 rounded-lg transition">
                                    Inquire
                                </a>
                            </div>
                            <a href="{{ route('classifieds.show', $classified) }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block">
                                View Details
                            </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $classifieds->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                    <p class="text-gray-600 dark:text-gray-300">No classifieds found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
