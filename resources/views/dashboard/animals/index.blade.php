<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            My Animals
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6">
                <a href="{{ route('dashboard.animals.create') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block font-semibold">
                    Add Animal
                </a>
            </div>

            @if ($animals->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($animals as $animal)
                        @php $thumb = $animal->media->first(); @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                            @if ($thumb)
                                <a href="{{ route('dashboard.animals.show', $animal) }}">
                                    <img src="{{ $thumb->url }}" alt="{{ $animal->pet_name }}"
                                        class="w-full aspect-square object-cover">
                                </a>
                            @else
                                <div class="w-full aspect-square bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">No photo</span>
                                </div>
                            @endif

                            <div class="p-4">
                                <div class="mb-3 flex items-center justify-between flex-wrap gap-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if ($animal->availability)
                                            <span class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $animal->availability->badgeClasses() }}">
                                                {{ $animal->availability->label() }}
                                            </span>
                                        @endif
                                        <span class="inline-block px-2.5 py-0.5 text-xs rounded-full
                                            {{ $animal->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ ucfirst($animal->status) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $animal->female ? 'Female' : 'Male' }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-orange-600 dark:text-orange-400 mb-2">
                                    {{ $animal->pet_name }}
                                </h3>
                                @if ($animal->date_of_birth)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        <span class="font-semibold">DOB:</span> {{ $animal->date_of_birth->format('M d, Y') }}
                                    </p>
                                @endif
                                @if ($animal->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                                        {{ Str::limit($animal->description, 80) }}
                                    </p>
                                @endif
                                <div class="flex gap-2">
                                    <a href="{{ route('dashboard.animals.show', $animal) }}" class="bg-blue-500 text-white py-1 px-3 rounded text-sm hover:bg-blue-700">
                                        View
                                    </a>
                                    <a href="{{ route('dashboard.animals.edit', $animal) }}" class="bg-yellow-500 text-white py-1 px-3 rounded text-sm hover:bg-yellow-700">
                                        Edit
                                    </a>
                                    <form action="{{ route('dashboard.animals.destroy', $animal) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this animal?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded text-sm hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $animals->links() }}</div>
            @else
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                    <p class="text-gray-600 dark:text-gray-300 mb-4">You haven't added any animals yet.</p>
                    <a href="{{ route('dashboard.animals.create') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block font-semibold">
                        Add Your First Animal
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
