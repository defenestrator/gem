<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('species.show', $subspecies->parentSpecies) }}"
               class="text-gray-400 hover:text-orange-500 transition text-sm italic">← {{ $subspecies->parentSpecies->species }}</a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <a href="{{ route('subspecies.show', $subspecies) }}"
               class="text-gray-400 hover:text-orange-500 transition text-sm italic">{{ $subspecies->full_name }}</a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Subspecies</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Fields --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-5">Taxonomy & Content</h3>

                <form method="POST" action="{{ route('dashboard.subspecies.update', $subspecies) }}">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Genus <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="genus" value="{{ old('genus', $subspecies->genus) }}" required
                                    class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm italic">
                                @error('genus')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Species <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="species" value="{{ old('species', $subspecies->species) }}" required
                                    class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm italic">
                                @error('species')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Subspecies Epithet <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="subspecies" value="{{ old('subspecies', $subspecies->subspecies) }}" required
                                    class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm italic">
                                @error('subspecies')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Author</label>
                            <input type="text" name="author" value="{{ old('author', $subspecies->author) }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm">
                            @error('author')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (Markdown)</label>
                            <textarea name="description" rows="12"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm font-mono">{{ old('description', $subspecies->description) }}</textarea>
                            @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg transition text-sm">
                            Save Changes
                        </button>
                        <a href="{{ route('subspecies.show', $subspecies) }}"
                           class="py-2 px-5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            {{-- Media management --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">Photos</h3>

                @if ($media->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-gray-500">No photos attached.</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach ($media as $photo)
                            <div class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                <img src="{{ $photo->url }}" alt="{{ $subspecies->full_name }}"
                                     class="w-full aspect-square object-cover">
                                <div class="absolute top-1 left-1">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded
                                        {{ $photo->moderation_status === 'approved' ? 'bg-green-500 text-white' : ($photo->moderation_status === 'pending' ? 'bg-yellow-400 text-yellow-900' : 'bg-red-500 text-white') }}">
                                        {{ strtoupper($photo->moderation_status) }}
                                    </span>
                                </div>
                                <form method="POST"
                                      action="{{ route('dashboard.subspecies.media.detach', [$subspecies, $photo]) }}"
                                      class="absolute inset-0 flex items-end justify-center opacity-0 group-hover:opacity-100 transition bg-black/40"
                                      onsubmit="return confirm('Detach this photo from {{ addslashes($subspecies->full_name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="mb-2 text-xs bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1 rounded transition">
                                        Detach
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Upload new --}}
                <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Add photos</p>
                    <form method="POST" action="{{ route('subspecies.media.store', $subspecies) }}" enctype="multipart/form-data">
                        @csrf
                        <x-model-media-uploader name="images" />
                        @error('images.*')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="mt-4">
                            <button type="submit"
                                class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg transition text-sm">
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
