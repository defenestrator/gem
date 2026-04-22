<x-guest-layout>
    <div class="w-full min-h-screen flex justify-center items-center">
        <div id="main-tile" class="text-left min-h-[70vh] bg-gray-800 text-gray-200 p-12 rounded-xl shadow-l2xl shadow-inner">
            <h1 class="mt-4 text-3xl text-orange-600">Reticulated Pythons</h1>
            <h2 class="mt-8 text-xl" >Captive-bred Reticulated Pythons</h2>
            <div class="mx-auto flex justify-left">
                <h2 href="/available">
                    Available Reticulated Pythons:
                </h2>
                
            </div>
            
            @if(empty($animals))
                <p class="mt-8 text-gray-300 text-3xl"><em>Sorry, no retics are currently available!</em></p>
            @else
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($animals as $animal)
                        <div class="bg-gray-700 p-4 rounded-lg shadow-md">
                            @if($animal['Photo_Urls'])
                                @php
                                    $photos = explode(' ', $animal['Photo_Urls']);
                                    $firstPhoto = $photos[0];
                                @endphp
                                <img src="{{ $firstPhoto }}" alt="{{ $animal['Title*'] }}" class="w-full h-48 object-cover rounded-md mb-4">
                            @endif
                            <h3 class="text-lg font-semibold text-orange-400 mb-2">{{ $animal['Title*'] }}</h3>
                            <p class="text-sm text-gray-300 mb-1"><strong>Traits:</strong> {{ $animal['Traits'] }}</p>
                            <p class="text-sm text-gray-300 mb-1"><strong>Maturity:</strong> {{ $animal['Maturity'] }}</p>
                            <p class="text-sm text-gray-300 mb-1"><strong>Sex:</strong> {{ $animal['Sex'] }}</p>
                            <p class="text-lg font-bold text-green-400 mb-4">${{ $animal['Price'] }}</p>
                            <a href="{{ $animal['Mm_Url**'] }}" target="_blank" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block">
                                View on MorphMarket
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>