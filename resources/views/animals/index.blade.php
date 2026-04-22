<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Animals
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Search & Filter -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
                <form method="GET" action="{{ route('animals.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search animals..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700">Filter</button>
                        <a href="{{ route('animals.index') }}" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-700 text-center">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Sort Options -->
            <div class="mb-6 flex flex-wrap gap-2">
                <span class="text-gray-600 dark:text-gray-400 font-semibold">Sort by:</span>
                @foreach (['recent' => 'Newest', 'oldest' => 'Oldest', 'name-asc' => 'Name A–Z', 'name-desc' => 'Name Z–A'] as $value => $label)
                    <a href="{{ route('animals.index', array_merge(request()->query(), ['sort' => $value])) }}"
                        class="px-3 py-1 rounded-lg text-sm font-medium {{ $currentSort === $value ? 'bg-orange-600 text-white' : 'bg-orange-500 text-white hover:bg-orange-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if ($animals->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($animals as $animal)
                        @php $thumb = $animal->media->first(); @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                            <a href="{{ route('animals.show', $animal) }}">
                                @if ($thumb)
                                    <img src="{{ $thumb->url }}" alt="{{ $animal->pet_name }}"
                                        class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <span class="text-gray-400 text-sm">No photo</span>
                                    </div>
                                @endif
                            </a>

                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-orange-600 dark:text-orange-400">
                                        <a href="{{ route('animals.show', $animal) }}" class="hover:underline">
                                            {{ $animal->pet_name }}
                                        </a>
                                    </h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $animal->female ? 'Female' : 'Male' }}
                                    </span>
                                </div>
                                @if ($animal->category)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        <span class="font-semibold">Category:</span> {{ $animal->category }}
                                    </p>
                                @endif
                                @if ($animal->slug)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        <span class="font-semibold">Unique ID:</span>
                                        @if ($animal->mm_url)
                                            <a href="{{ $animal->mm_url }}" target="_blank" class="text-orange-500 hover:underline">
                                                {{ $animal->slug }}
                                            </a>
                                        @else
                                            {{ $animal->slug }}
                                        @endif
                                    </p>
                                @endif
                                @if ($animal->date_of_birth)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        <span class="font-semibold">DOB:</span> {{ $animal->date_of_birth->format('M d, Y') }}
                                    </p>
                                @endif
                                @if ($animal->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                        {{ Str::limit($animal->description, 80) }}
                                    </p>
                                @endif
                                <a href="{{ route('animals.show', $animal) }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block text-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $animals->links() }}</div>
            @else
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                    <p class="text-gray-600 dark:text-gray-300">No animals found.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
