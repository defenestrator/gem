<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit: {{ $animal->pet_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('dashboard.animals.update', $animal) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- Pet Name -->
                        <div class="mb-6">
                            <label for="pet_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="pet_name" name="pet_name" value="{{ old('pet_name', $animal->pet_name) }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                required>
                            @error('pet_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sex -->
                        <div class="mb-6">
                            <label for="female" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sex</label>
                            <select id="female" name="female"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                <option value="0" {{ old('female', $animal->female ? '1' : '0') == '0' ? 'selected' : '' }}>Male</option>
                                <option value="1" {{ old('female', $animal->female ? '1' : '0') == '1' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <!-- Date of Birth -->
                        <div class="mb-6">
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth"
                                value="{{ old('date_of_birth', $animal->date_of_birth?->format('Y-m-d')) }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                            @error('date_of_birth')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea id="description" name="description" rows="4"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">{{ old('description', $animal->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Acquisition Date -->
                        <div class="mb-6">
                            <label for="acquisition_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Acquisition Date</label>
                            <input type="date" id="acquisition_date" name="acquisition_date"
                                value="{{ old('acquisition_date', $animal->acquisition_date?->format('Y-m-d')) }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                            @error('acquisition_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Acquisition Cost -->
                        <div class="mb-6">
                            <label for="acquisition_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Acquisition Cost ($)</label>
                            <input type="number" id="acquisition_cost" name="acquisition_cost"
                                value="{{ old('acquisition_cost', $animal->acquisition_cost) }}"
                                min="0"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                            @error('acquisition_cost')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Proven Breeder -->
                        <div class="mb-6 flex items-center gap-3">
                            <input type="hidden" name="proven_breeder" value="0">
                            <input type="checkbox" id="proven_breeder" name="proven_breeder" value="1"
                                {{ old('proven_breeder', $animal->proven_breeder) ? 'checked' : '' }}
                                class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                            <label for="proven_breeder" class="text-sm font-medium text-gray-700 dark:text-gray-300">Proven Breeder</label>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visibility</label>
                            <select id="status" name="status"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                <option value="draft" {{ old('status', $animal->status) === 'draft' ? 'selected' : '' }}>Draft (only visible to you)</option>
                                <option value="published" {{ old('status', $animal->status) === 'published' ? 'selected' : '' }}>Published (visible to everyone)</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Photos -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Photos</label>

                            @if ($animal->media->isNotEmpty())
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 mb-4">
                                    @foreach ($animal->media as $photo)
                                        <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shadow-sm">
                                            <img src="{{ $photo->url }}" alt="{{ $animal->pet_name }}" class="w-full h-full object-cover">
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
                                Save Changes
                            </button>
                            <a href="{{ route('dashboard.animals.show', $animal) }}" class="bg-gray-500 text-white py-2 px-6 rounded-lg hover:bg-gray-700 font-semibold">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
