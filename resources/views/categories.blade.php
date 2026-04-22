<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Available Animals By Category') }}
        </h2>
    </x-slot>


    <main class="mx-auto max-w-7xl sm:px-6 lg:px-8 pb-12">
        <div id="main-tile" class="text-left min-h-[70vh] bg-gray-800 text-gray-200 p-6 rounded-xl shadow-l2xl shadow-inner pb-12">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @if(isset($categories['Corn Snakes']))
            <div class="bg-gray-700 rounded-lg shadow-md overflow-hidden flex flex-col text-white">
                <a href="{{ route('categories.corn-snakes') }}">
                    <img src="/Corns.jpeg" alt="Corn Snakes" class="w-full aspect-square object-cover"/>
                </a>
                <div class="p-6 flex flex-col flex-1">
                    <h2 class="text-xl font-medium text-white">Corn Snakes</h2>
                    <p class="mt-2 text-white">
                        A colubrid native to the eastern US and one of the most beginner-friendly reptiles in the hobby. Decades of captive breeding have produced hundreds of stunning color morphs.
                    </p>
                    <div class="mt-auto pt-4">
                        <a href="{{ route('categories.corn-snakes') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                            View Corn Snakes
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @if(isset($categories['Carpet Pythons']) && $categories['Carpet Pythons'] > 0)
            <div class="bg-gray-700 rounded-lg shadow-md overflow-hidden flex flex-col text-white">
                <a href="{{ route('categories.carpet-pythons') }}">
                    <img src="/Carpets.jpeg" alt="Carpet Pythons" class="w-full aspect-square object-cover"/>
                </a>
                <div class="p-6 flex flex-col flex-1">
                    <h2 class="text-xl font-medium text-white">Carpet Pythons</h2>
                    <p class="mt-2 text-white">
                        Description of carpet python species. This species is known for its unique patterns and calm demeanor.
                    </p>
                    <div class="mt-auto pt-4">
                        <a href="{{ route('categories.carpet-pythons') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                            View Carpet Pythons
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @if(isset($categories['Ball Pythons']) && $categories['Ball Pythons'] > 0)
            <div class="bg-gray-700 rounded-lg shadow-md overflow-hidden flex flex-col text-white">
                <a href="{{ route('categories.ball-pythons') }}">
                    <img src="/Balls.jpeg" alt="Ball Pythons" class="w-full aspect-square object-cover"/>
                </a>
                <div class="p-6 flex flex-col flex-1">
                    <h2 class="text-xl font-medium text-white">Ball Pythons</h2>
                    <p class="mt-2 text-white">
                        The world's most popular pet python, native to West African savannas and available in more captive morphs than any other snake species on earth.
                    </p>
                    <div class="mt-auto pt-4">
                        <a href="{{ route('categories.ball-pythons') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                            View Ball Pythons
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @if(isset($categories['Reticulated Pythons']) && $categories['Reticulated Pythons'] > 0)
            <div class="bg-gray-700 rounded-lg shadow-md overflow-hidden flex flex-col text-white">
                <a href="{{ route('categories.reticulated-pythons') }}">
                    <img src="/Retics.jpeg" alt="Reticulated Pythons" class="w-full aspect-square object-cover"/>
                </a>
                <div class="p-6 flex flex-col flex-1">
                    <h2 class="text-xl font-medium text-white">Reticulated Pythons</h2>
                    <p class="mt-2 text-white">
                        The world's longest snake, native across Southeast Asia. Captive-bred dwarf and super-dwarf locality animals bring this stunning species within reach of serious keepers.
                    </p>
                    <div class="mt-auto pt-4">
                        <a href="{{ route('categories.reticulated-pythons') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                            View Reticulated Pythons
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @if(isset($categories['Western Hognose']))
            <div class="bg-gray-700 rounded-lg shadow-md overflow-hidden flex flex-col text-white">
                <a href="{{ route('categories.western-hognose') }}">
                    <img src="/Hognose.jpeg" alt="Western Hognose" class="w-full aspect-square object-cover"/>
                </a>
                <div class="p-6 flex flex-col flex-1">
                    <h2 class="text-xl font-medium text-white">Western Hognose</h2>
                    <p class="mt-2 text-white">
                        A charismatic Plains colubrid famous for its upturned snout and dramatic death-feigning display. Compact, diurnal, and available in a rapidly expanding range of captive morphs.
                    </p>
                    <div class="mt-auto pt-4">
                        <a href="{{ route('categories.western-hognose') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 w-full inline-block text-center">
                            View Western Hognose
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
        </div>
    </main>
</x-guest-layout>
