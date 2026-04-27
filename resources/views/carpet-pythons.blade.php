<x-guest-layout>
    <div class="w-full min-h-screen flex justify-center items-center">
        <div id="main-tile" class="text-left min-h-[70vh] bg-gray-800 text-gray-200 p-12 rounded-xl shadow-l2xl shadow-inner">
            <h1 class="mt-4 text-3xl text-orange-600">Carpet Pythons</h1>
            <h2 class="mt-8 text-xl" >Captive-bred Carpet Pythons</h2>
            <div class="mx-auto flex justify-left">
                <h2 href="/available">
                    Available Carpet Pythons:
                </h2>
                
            </div>
            
            @if(empty($animals))
                <p class="mt-8 text-gray-300">No animals are currently available in this category.</p>
            @else
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($animals as $animal)
                        <div class="bg-gray-700 rounded-lg shadow-md overflow-hidden">
                            @if($animal['Photo_Urls'])
                                @php
                                    $photos = explode(' ', $animal['Photo_Urls']);
                                    $firstPhoto = $photos[0];
                                @endphp
                                <img src="{{ $firstPhoto }}" alt="{{ $animal['Title*'] }}" class="w-full aspect-square object-cover">
                            @endif
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-orange-400 mb-2">{{ $animal['Title*'] }}</h3>
                                <p class="text-sm text-gray-300 mb-1"><strong>Traits:</strong> {{ $animal['Traits'] }}</p>
                                <p class="text-sm text-gray-300 mb-1"><strong>Maturity:</strong> {{ $animal['Maturity'] }}</p>
                                <p class="text-sm text-gray-300 mb-1"><strong>Sex:</strong> {{ $animal['Sex'] }}</p>
                                <p class="text-lg font-bold text-green-400 mb-4">${{ $animal['Price'] }}</p>
                                <div class="flex gap-2 flex-wrap">
                                    <a href="{{ $animal['Mm_Url**'] }}" target="_blank" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block text-sm">
                                        View on MorphMarket
                                    </a>
                                    <a href="{{ route('animals.inquiries.create', ['animal' => $animal['Animal_Id*']]) }}" class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-800 inline-block text-sm font-semibold">
                                        Inquire
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>