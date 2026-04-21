<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import MorphMarket Animals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-lg">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('dashboard.animals.import.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <label for="animals_json" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                MorphMarket.com JSON File
                            </label>
                            <input type="file" id="animals_json" name="animals_json" accept="application/json" required class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                        </div>
                        <button type="submit" class="bg-orange-500 text-white py-2 px-6 rounded-lg hover:bg-orange-700 font-semibold">
                            Upload & Sync
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
