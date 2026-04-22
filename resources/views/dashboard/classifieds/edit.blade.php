<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Classified Ad
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('dashboard.classifieds.update', $classified) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="title" name="title" value="{{ old('title', $classified->title) }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                placeholder="e.g., Beautiful Ball Python - Yellow Belly Morph" required>
                            @error('title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea id="description" name="description" rows="5"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Describe the animal, its characteristics, health, feeding habits, etc." required>{{ old('description', $classified->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Animal Selection -->
                        <div class="mb-6">
                            <label for="animal_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Animal <span class="text-red-500">*</span>
                            </label>
                            <select id="animal_id" name="animal_id"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                required>
                                <option value="">-- Select an Animal --</option>
                                @foreach ($animals as $animal)
                                    <option value="{{ $animal->id }}" {{ old('animal_id', $classified->animal_id) == $animal->id ? 'selected' : '' }}>
                                        {{ $animal->pet_name ?? 'Unknown' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('animal_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="mb-6">
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Price ($) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="price" name="price" value="{{ old('price', $classified->price) }}"
                                step="0.01" min="0"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                placeholder="0.00" required>
                            @error('price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status
                            </label>
                            <select id="status" name="status"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                <option value="published" {{ old('status', $classified->status) === 'published' ? 'selected' : '' }}>Published (Visible to all)</option>
                                <option value="draft" {{ old('status', $classified->status) === 'draft' ? 'selected' : '' }}>Draft (Only visible to you)</option>
                                <option value="sold" {{ old('status', $classified->status) === 'sold' ? 'selected' : '' }}>Sold</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Photos -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Photos</label>

                            @if ($classified->media->isNotEmpty())
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 mb-4">
                                    @foreach ($classified->media as $photo)
                                        <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shadow-sm">
                                            <img src="{{ $photo->url }}" alt="{{ $classified->title }}" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 flex items-start justify-end p-1.5 opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 rounded-lg">
                                                <form action="{{ route('dashboard.media.destroy', $photo) }}" method="POST"
                                                    onsubmit="return confirm('Delete this image?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-white hover:text-red-300 focus:outline-none" title="Delete">
                                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                            <path d="M3 6l3 18h12l3-18h-18zm19-4v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.316c0 .901.73 2 1.631 2h5.711z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @error('images.*')
                                <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
                            @enderror
                            <x-model-media-uploader name="images" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <button type="submit" class="bg-orange-500 text-white py-2 px-6 rounded-lg hover:bg-orange-700 font-semibold">
                                Update Classified
                            </button>
                            <a href="{{ route('dashboard.classifieds.index') }}" class="bg-gray-500 text-white py-2 px-6 rounded-lg hover:bg-gray-700 font-semibold">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
