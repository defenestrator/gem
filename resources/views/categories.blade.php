<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Available Animals By Category') }}
        </h2>
    </x-slot>

    
    <main class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @if(isset($categories['Corn Snakes']))
            <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                <img
                    src="/Corns.jpeg"
                    alt="Corn Snakes"
                    class="w-full h-48 object-cover rounded-t-lg mx-auto"
                />
                <h2 class="mt-4 text-xl font-medium text-white">Corn Snakes</h2>
                <p class="mt-2 text-white p-2">
                    Description of corn snake species. This species is known for its vibrant colors and friendly nature.
                </p>
                <div class="mt-auto">
                    <a href="{{ route('categories.corn-snakes') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                        View Corn Snakes
                    </a>
                </div>
            </div>
            @endif
            @if(isset($categories['Carpet Pythons']) && $categories['Carpet Pythons'] > 0)
            <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                <img
                    src="/Carpets.jpeg"
                    alt="Carpet Pythons"
                    class="w-full h-48 object-cover rounded-t-lg mx-auto"
                />
                <h2 class="mt-4 text-xl font-medium text-white">Carpet Pythons</h2>
                <p class="mt-2 text-white p-2">
                    Description of carpet python species. This species is known for its unique patterns and calm demeanor.
                </p>
                <div class="mt-auto">
                    <a href="{{ route('categories.carpet-pythons') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                        View Carpet Pythons
                    </a>
                </div>
            </div>
            @endif
            @if(isset($categories['Ball Pythons']) && $categories['Ball Pythons'] > 0)
            <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                <img
                    src="/Balls.jpeg"
                    alt="Ball Pythons"
                    class="w-full h-48 object-cover rounded-t-lg mx-auto"
                />
                <h2 class="mt-4 text-xl font-medium text-white">Ball Pythons</h2>
                <p class="mt-2 text-white p-2">
                    Description of ball python species. This species is known for its docile nature and variety of morphs.
                </p>
                <div class="mt-auto">
                    <a href="{{ route('categories.ball-pythons') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                        View Ball Pythons
                    </a>
                </div>
            </div>
            @endif
            @if(isset($categories['Reticulated Pythons']) && $categories['Reticulated Pythons'] > 0)
            <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                <img
                    src="/Retics.jpeg"
                    alt="Reticulated Pythons"
                    class="w-full h-48 object-cover rounded-t-lg mx-auto"
                />
                <h2 class="mt-4 text-xl font-medium text-white">Reticulated Pythons</h2>
                <p class="mt-2 text-white p-2">
                    Description of reticulated python species. This species is known for its impressive size and striking patterns.
                </p>
                <div class="mt-auto">
                    <a href="{{ route('categories.reticulated-pythons') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                        View Reticulated Pythons
                    </a>
                </div>
            </div>
            @endif
            @if(isset($categories['Western Hognose']))
            <div class="bg-gray-700 p-6 rounded-lg shadow-md flex flex-col text-white">
                <img
                    src="/Hognose.jpeg"
                    alt="Western Hognose"
                    class="w-full h-48 object-cover rounded-t-lg mx-auto"
                />
                <h2 class="mt-4 text-xl font-medium text-white">Western Hognose</h2>
                <p class="mt-2 text-white p-2">
                    Western Hognose Description
                </p>
                <div class="mt-auto">
                    <a href="{{ route('categories.western-hognose') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                        View Western Hognose
                    </a>
                </div>
            </div>
            @endif
        </div>
    </main>
</x-guest-layout>
