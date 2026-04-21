<x-guest-layout>
    <x-slot name="background">
    </x-slot> 
    <div class="w-full min-h-screen flex justify-center items-center pb-12">
        <div id="main-tile" class="text-left min-h-[70vh] bg-opacity-90 bg-gray-700 text-gray-200 p-6 rounded-xl shadow-l2xl shadow-inner pb-12">
            <h1 class="my-2 text-3xl text-orange-500 font-serif">The Wild Type</h1>
            <h2 class="my-2 text-l" >Captive-bred Pythons and Colubrids by <span class="text-orange-400">Gem Reptiles</span></h2>
            {{-- <h5 class="my-4">
                <a href="https://www.morphmarket.com/stores/gem" title="Gem Reptiles MorphMarket Store">
                    <button class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700">
                    Gem Reptiles on MorphMarket
                    </button>   
                </a>
            </h5> --}}
            <div class="mx-auto flex justify-left">
                <h2 href="/available">
                    Available Animals:
                </h2>                
            </div>
            
            <div class="mt-6 flex flex-wrap gap-1 items-center">
                <span class="text-gray-300 font-semibold">Sort by:</span>
                <a href="{{ route('welcome', ['sort' => 'recent']) }}" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'recent' ? 'bg-orange-600' : 'bg-orange-500 hover:bg-orange-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Updated</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'price-low']) }}" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'price-low' ? 'bg-orange-600' : 'bg-orange-500 hover:bg-orange-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Lowest Price</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'price-high']) }}" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'price-high' ? 'bg-orange-600' : 'bg-orange-500 hover:bg-orange-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Highest Price</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'date-new']) }}" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'date-new' ? 'bg-orange-600' : 'bg-orange-500 hover:bg-orange-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Newest Hatched</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'category']) }}" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'category' ? 'bg-orange-600' : 'bg-orange-500 hover:bg-orange-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Category (Asc)</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'category-desc']) }}" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'category-desc' ? 'bg-orange-600' : 'bg-orange-500 hover:bg-orange-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Category (Desc)</span>
                </a>
            </div>
            
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($animals as $animal)
                    @if($animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active')
                        <div class="bg-gray-700 p-4 rounded-lg shadow-md">
                            @if($animal['Photo_Urls'])
                                @php
                                    $photos = explode(' ', $animal['Photo_Urls']);
                                    $firstPhoto = $photos[0];
                                @endphp
                                <img src="{{ $firstPhoto }}" alt="{{ $animal['Title*'] }}" class="w-full h-48 object-cover rounded-md mb-4">
                            @endif
                            <h3 class="text-lg font-semibold text-orange-400 mb-2">{{ $animal['Title*'] }}</h3>
                            <p class="text-sm text-gray-300 mb-1"><strong>Category:</strong> {{ $animal['Category*'] }}</p>
                            <p class="text-sm text-gray-300 mb-1"><strong>Traits:</strong> {{ $animal['Traits'] }}</p>
                            <p class="text-sm text-gray-300 mb-1"><strong>Maturity:</strong> {{ $animal['Maturity'] }}</p>
                            <p class="text-sm text-gray-300 mb-1"><strong>Sex:</strong> {{ $animal['Sex'] }}</p>
                            <p class="text-lg font-bold text-green-400 mb-4">${{ $animal['Price'] }}</p>
                            <a href="{{ $animal['Mm_Url**'] }}" target="_blank" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block">
                                View on MorphMarket
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
            <h4 class="mt-4 font-bold text-2xl"><a href="https://www.morphmarket.com/stores/gem" title="Gem Reptiles on MorphMarket">Visit our MorphMarket Store!</a></h4>
            <h5 class="my-4">
                <a href="https://www.morphmarket.com/stores/gem" title="Gem Reptiles MorphMarket Store">
                    <button class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700">
                    Gem Reptiles on MorphMarket
                    </button>   
                </a>
            </h5>
        </div>
    </div>
</x-guest-layout>

