<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Add Animal
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('dashboard.animals.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Pet Name -->
                        <div class="mb-6">
                            <label for="pet_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="pet_name" name="pet_name" value="{{ old('pet_name') }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                placeholder="e.g., Sable" required>
                            @error('pet_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Unique ID / Slug -->
                        <div class="mb-6">
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Unique ID <span class="text-red-500">*</span> <span class="text-xs text-gray-400 dark:text-gray-500">(letters, numbers, hyphens, underscores)</span>
                            </label>
                            <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                placeholder="e.g. Longhorn-Thatcher" required>
                            @error('slug')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sex -->
                        <div class="mb-6">
                            <label for="female" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sex</label>
                            <select id="female" name="female"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                <option value="0" {{ old('female', '0') == '0' ? 'selected' : '' }}>Male</option>
                                <option value="1" {{ old('female') == '1' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        <!-- Date of Birth -->
                        <div class="mb-6">
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                            @error('date_of_birth')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea id="description" name="description" rows="4"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Morph, lineage, notes...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Acquisition Date -->
                        <div class="mb-6">
                            <label for="acquisition_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Acquisition Date</label>
                            <input type="date" id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date') }}"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                            @error('acquisition_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Acquisition Cost -->
                        <div class="mb-6">
                            <label for="acquisition_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Acquisition Cost ($)</label>
                            <input type="number" id="acquisition_cost" name="acquisition_cost" value="{{ old('acquisition_cost') }}"
                                min="0"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                placeholder="0">
                            @error('acquisition_cost')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Proven Breeder -->
                        <div class="mb-6 flex items-center gap-3">
                            <input type="hidden" name="proven_breeder" value="0">
                            <input type="checkbox" id="proven_breeder" name="proven_breeder" value="1"
                                {{ old('proven_breeder') ? 'checked' : '' }}
                                class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                            <label for="proven_breeder" class="text-sm font-medium text-gray-700 dark:text-gray-300">Proven Breeder</label>
                        </div>

                        <!-- Availability -->
                        <div class="mb-6">
                            <label for="availability" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Availability</label>
                            <select id="availability" name="availability"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                <option value="" {{ old('availability', '') === '' ? 'selected' : '' }}>— Not set —</option>
                                <option value="for_sale" {{ old('availability') === 'for_sale' ? 'selected' : '' }}>For Sale</option>
                                <option value="breeder" {{ old('availability') === 'breeder' ? 'selected' : '' }}>Breeder</option>
                                <option value="sold" {{ old('availability') === 'sold' ? 'selected' : '' }}>Sold</option>
                                <option value="not_for_sale" {{ old('availability') === 'not_for_sale' ? 'selected' : '' }}>Not For Sale</option>
                            </select>
                            @error('availability')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visibility</label>
                            <select id="status" name="status"
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft (only visible to you)</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published (visible to everyone)</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Photos -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Photos</label>
                            @error('images.*')
                                <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
                            @enderror
                            <x-model-media-uploader name="images" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <button type="submit" class="bg-orange-500 text-white py-2 px-6 rounded-lg hover:bg-orange-700 font-semibold">
                                Add Animal
                            </button>
                            <a href="{{ route('dashboard.animals.index') }}" class="bg-gray-500 text-white py-2 px-6 rounded-lg hover:bg-gray-700 font-semibold">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
