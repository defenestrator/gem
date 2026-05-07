<x-app-layout>
    @push('meta')
    <meta name="description" content="Browse captive-bred reptiles for sale at Gem Reptiles. Filter by availability, species, and category.">
    @endpush
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Animals
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Search & Filter -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
                <form method="GET" action="{{ route('animals.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search animals..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>
                    {{-- preserve sort when filtering --}}
                    @if ($currentSort !== 'recent')
                        <input type="hidden" name="sort" value="{{ $currentSort }}">
                    @endif
                    <div class="flex gap-2">
                        <button type="submit" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700">Filter</button>
                        <a href="{{ route('animals.index') }}" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-700 text-center">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Availability Tabs -->
            <div class="mb-4 flex flex-wrap gap-2">
                <a href="{{ route('animals.index', array_merge(request()->except('availability', 'page'), [])) }}"
                    class="px-3 py-1 rounded-full text-sm font-medium transition
                        {{ !$availability ? 'bg-orange-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 hover:border-orange-400' }}">
                    All
                </a>
                @foreach ($availabilities as $av)
                    <a href="{{ route('animals.index', array_merge(request()->except('availability', 'page'), ['availability' => $av->value])) }}"
                        class="px-3 py-1 rounded-full text-sm font-medium transition
                            {{ $availability === $av->value
                                ? $av->badgeClasses() . ' ring-2 ring-offset-1 ring-current'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 hover:border-orange-400' }}">
                        {{ $av->label() }}
                    </a>
                @endforeach
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
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition flex flex-col">
                            <a href="{{ route('animals.show', $animal) }}" class="relative block">
                                @if ($thumb)
                                    <img src="{{ $thumb->url }}" alt="{{ $animal->pet_name }}"
                                        class="w-full aspect-square object-cover">
                                @else
                                    <div class="w-full aspect-square bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <span class="text-gray-400 text-sm">No photo</span>
                                    </div>
                                @endif
                                {{-- Availability ribbon --}}
                                @if ($animal->availability)
                                    <span class="absolute top-2 left-2 px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $animal->availability->badgeClasses() }}">
                                        {{ $animal->availability->label() }}
                                    </span>
                                @endif
                            </a>

                            <div class="p-4 flex flex-col flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="text-lg font-semibold text-orange-600 dark:text-orange-400 mr-2">
                                        <a href="{{ route('animals.show', $animal) }}" class="hover:underline">
                                            {{ $animal->pet_name }}
                                        </a>
                                    </h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $animal->female ? 'Female' : 'Male' }}
                                    </span>
                                </div>
                                @if ($animal->species)
                                    <p class="text-xs italic text-gray-400 dark:text-gray-500 mb-2">
                                        <a href="{{ route('species.show', $animal->species) }}" class="hover:text-orange-500 hover:underline transition">
                                            {{ $animal->species->species }}
                                        </a>
                                    </p>
                                @endif
                                @if ($animal->category)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        <span class="font-semibold">Category:</span> {{ $animal->category }}
                                    </p>
                                @endif
                                @if ($animal->date_of_birth)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        <span class="font-semibold">DOB:</span> {{ $animal->date_of_birth->format('M d, Y') }}
                                    </p>
                                @endif
                                @if ($animal->availability === \App\Enums\AnimalAvailability::ForSale && $animal->price)
                                    <div class="flex items-center gap-3 mb-2">
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            ${{ number_format($animal->price, 2) }}
                                        </p>
                                        <a href="{{ route('animals.inquiries.create', $animal) }}"
                                            class="bg-orange-500 hover:bg-orange-700 text-white text-xs font-semibold py-1 px-3 rounded-lg transition">
                                            Inquire
                                        </a>
                                    </div>
                                    @if(config('features.easyship'))
                                    <x-shipping-quote :compact="true" />
                                    @endif
                                @endif
                                @if ($animal->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                        {{ Str::limit($animal->description, 80) }}
                                    </p>
                                @endif
                                <a href="{{ route('animals.show', $animal) }}" class="mt-auto bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 block text-sm font-bold text-center">
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
