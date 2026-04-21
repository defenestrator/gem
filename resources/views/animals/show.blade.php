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
                        {{-- Hero image --}}
                        <div class="w-full bg-black" style="max-height:520px;">
                            <img :src="active" alt="{{ $animal->pet_name }}"
                                class="w-full object-contain mx-auto" style="max-height:520px;">
                        </div>

                        {{-- Thumbnail strip --}}
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

                    {{-- Name --}}
                    <h1 class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-6">
                        {{ $animal->pet_name }}
                    </h1>

                    {{-- Details grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        @if ($animal->date_of_birth)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold">Date of Birth</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $animal->date_of_birth->format('M d, Y') }}</p>
                            </div>
                        @endif
                        @if ($animal->acquisition_date)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold">Acquired</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $animal->acquisition_date->format('M d, Y') }}</p>
                            </div>
                        @endif
                        @if ($animal->acquisition_cost !== null)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold">Acquisition Cost</p>
                                <p class="text-gray-800 dark:text-gray-200">${{ number_format($animal->acquisition_cost) }}</p>
                            </div>
                        @endif
                        @if ($animal->user)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold">Owner</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $animal->user->name }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if ($animal->description)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Description</h3>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $animal->description }}</p>
                        </div>
                    @endif

                    {{-- Owner actions --}}
                    @auth
                        @if (auth()->id() === $animal->user_id)
                            <div class="flex gap-4 mb-6">
                                <a href="{{ route('dashboard.animals.edit', $animal) }}"
                                    class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-700 font-semibold">
                                    Edit
                                </a>
                                <form action="{{ route('dashboard.animals.destroy', $animal) }}" method="POST"
                                    class="inline-block" onsubmit="return confirm('Delete this animal?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-700 font-semibold">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth

                    {{-- Back link --}}
                    <div class="mt-4">
                        @if (request()->is('dashboard/*'))
                            <a href="{{ route('dashboard.animals.index') }}"
                                class="text-orange-600 dark:text-orange-400 hover:underline font-semibold">
                                ← Back to My Animals
                            </a>
                        @else
                            <a href="{{ route('animals.index') }}"
                                class="text-orange-600 dark:text-orange-400 hover:underline font-semibold">
                                ← Back to Animals
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
