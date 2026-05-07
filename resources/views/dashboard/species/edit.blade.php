<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('species.show', $species) }}"
               class="text-gray-400 hover:text-orange-500 transition text-sm italic">← {{ $species->species }}</a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Species</h2>
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

                <form method="POST" action="{{ route('dashboard.species.update', $species) }}">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Species <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="species" value="{{ old('species', $species->species) }}" required
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm italic">
                            @error('species')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Common Name</label>
                            <input type="text" name="common_name" value="{{ old('common_name', $species->common_name) }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm">
                            @error('common_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Author</label>
                            <input type="text" name="author" value="{{ old('author', $species->author) }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm">
                            @error('author')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Higher Taxa</label>
                            <input type="text" name="higher_taxa" value="{{ old('higher_taxa', $species->higher_taxa) }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm">
                            @error('higher_taxa')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Species #</label>
                                <input type="text" name="species_number" value="{{ old('species_number', $species->species_number) }}"
                                    class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm">
                                @error('species_number')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Changes</label>
                                <input type="text" name="changes" value="{{ old('changes', $species->changes) }}"
                                    class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm">
                                @error('changes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description (Markdown)</label>
                            <textarea name="description" rows="12"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm font-mono">{{ old('description', $species->description) }}</textarea>
                            @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg transition text-sm">
                            Save Changes
                        </button>
                        <a href="{{ route('species.show', $species) }}"
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
                                <img src="{{ $photo->url }}" alt="{{ $species->species }}"
                                     class="w-full aspect-square object-cover">
                                <div class="absolute top-1 left-1">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded
                                        {{ $photo->moderation_status === 'approved' ? 'bg-green-500 text-white' : ($photo->moderation_status === 'pending' ? 'bg-yellow-400 text-yellow-900' : 'bg-red-500 text-white') }}">
                                        {{ strtoupper($photo->moderation_status) }}
                                    </span>
                                </div>
                                <form method="POST"
                                      action="{{ route('dashboard.species.media.detach', [$species, $photo]) }}"
                                      class="absolute inset-0 flex items-end justify-center opacity-0 group-hover:opacity-100 transition bg-black/40"
                                      onsubmit="return confirm('Detach this photo from {{ addslashes($species->species) }}?')">
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
                    <form method="POST" action="{{ route('species.media.store', $species) }}" enctype="multipart/form-data">
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
