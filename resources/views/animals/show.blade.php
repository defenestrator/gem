<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $animal->pet_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                {{-- Photo Gallery --}}
                @if ($animal->media->isNotEmpty())
                    @php $photos = $animal->media; @endphp
                    <div x-data="{ active: '{{ $photos->first()->url }}' }">
                        <div class="w-full bg-black" style="max-height:520px;">
                            <img :src="active" alt="{{ $animal->pet_name }}"
                                class="w-full object-contain mx-auto" style="max-height:520px;">
                        </div>

                        @if ($photos->count() > 1)
                            <div class="flex gap-2 p-3 overflow-x-auto bg-gray-100 dark:bg-gray-900">
                                @foreach ($photos as $photo)
                                    <button type="button"
                                        @click="active = '{{ $photo->url }}'"
                                        :class="active === '{{ $photo->url }}' ? 'ring-2 ring-orange-500' : 'opacity-70 hover:opacity-100'"
                                        class="flex-shrink-0 rounded overflow-hidden transition">
                                        <img src="{{ $photo->url }}" alt="{{ $animal->pet_name }}"
                                            class="h-16 w-16 object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <div class="p-6">
                    {{-- Status & Sex badges --}}
                    <div class="mb-4 flex gap-3 flex-wrap">
                        <span class="inline-block px-3 py-1 text-sm rounded-full
                            {{ $animal->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ ucfirst($animal->status) }}
                        </span>
                        <span class="inline-block px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">
                            {{ $animal->female ? 'Female' : 'Male' }}
                        </span>
                        @if ($animal->proven_breeder)
                            <span class="inline-block px-3 py-1 text-sm rounded-full bg-purple-100 text-purple-800">
                                Proven Breeder
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-6">
                        {{ $animal->pet_name }}
                    </h1>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        @if ($animal->category)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold">Category</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $animal->category }}</p>
                            </div>
                        @endif
                        @if ($animal->date_of_birth)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold">Date of Birth</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $animal->date_of_birth->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($animal->description)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Description</h3>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $animal->description }}</p>
                        </div>
                    @endif

                    <div>
                        <a href="{{ route('animals.index') }}"
                            class="text-orange-600 dark:text-orange-400 hover:underline font-semibold">
                            ← Back to Animals
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
