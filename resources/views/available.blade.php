<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Available Animals') }}
        </h2>
    </x-slot>

    
    <main class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3 lg:grid-cols-4">
                <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                    <img
                        src="/favicon.png"
                        alt="Corn Snakes"
                        class="w-full h-48 object-cover rounded-t-lg mx-auto"
                    />
                    <h2 class="mt-4 text-xl font-medium text-white">Corn Snakes</h2>
                    <p class="mt-2 text-white p-2">
                        Description of corn snake species. This species is known for its vibrant colors and friendly nature.
                    </p>
                    <div class="mt-auto flex justify-end">
                        <button class="bg-orange-800 text-white py-2 px-4 rounded-lg hover:bg-orange-900">
                            View Details
                        </button>
                    </div>
                </div>
                <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                    <img
                        src="/favicon.png"
                        alt="Carpet Pythons"
                        class="w-full h-48 object-cover rounded-t-lg mx-auto"
                    />
                    <h2 class="mt-4 text-xl font-medium text-white">Carpet Pythons</h2>
                    <p class="mt-2 text-white p-2">
                        Description of carpet python species. This species is known for its unique patterns and calm demeanor.
                    </p>
                    <div class="mt-auto flex justify-end">
                        <button class="bg-orange-800 text-white py-2 px-4 rounded-lg hover:bg-orange-900">
                            View Details
                        </button>
                    </div>
                </div>
                <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                    <img
                        src="/favicon.png"
                        alt="Ball Pythons"
                        class="w-full h-48 object-cover rounded-t-lg mx-auto"
                    />
                    <h2 class="mt-4 text-xl font-medium text-white">Ball Pythons</h2>
                    <p class="mt-2 text-white p-2">
                        Description of ball python species. This species is known for its docile nature and variety of morphs.
                    </p>
                    <div class="mt-auto flex justify-end">
                        <button class="bg-orange-800 text-white py-2 px-4 rounded-lg hover:bg-orange-900">
                            View Details
                        </button>
                    </div>
                </div>
                <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                    <img
                        src="/favicon.png"
                        alt="Reticulated Pythons"
                        class="w-full h-48 object-cover rounded-t-lg mx-auto"
                    />
                    <h2 class="mt-4 text-xl font-medium text-white">Reticulated Pythons</h2>
                    <p class="mt-2 text-white p-2">
                        Description of reticulated python species. This species is known for its impressive size and striking patterns.
                    </p>
                    <div class="mt-auto flex justify-end">
                        <button class="bg-orange-800 text-white py-2 px-4 rounded-lg hover:bg-orange-900">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </main>
</x-guest-layout>
