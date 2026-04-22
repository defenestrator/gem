<x-guest-layout>
    <div class="w-full min-h-screen flex justify-center items-center">
        <div id="main-tile" class="text-left min-h-[70vh] bg-gray-800 text-gray-200 p-12 rounded-xl shadow-l2xl shadow-inner">
            <h1 class="mt-4 text-3xl text-orange-600">Ball Pythons</h1>
            <p class="mt-2 text-gray-500 italic text-sm">Python regius</p>

            <div class="mt-10 mx-auto flex justify-left">
                <h2 class="text-xl">Available Ball Pythons:</h2>
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
                                <a href="{{ $animal['Mm_Url**'] }}" target="_blank" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block">
                                    View on MorphMarket
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-10 max-w-3xl space-y-4 text-gray-300 leading-relaxed">
                <p>
                    The ball python (<em>Python regius</em>) is a compact, ground-dwelling python native to the grasslands, savannas, and sparse woodlands of west and central sub-Saharan Africa, ranging from Senegal and Guinea-Bissau in the west through Nigeria, Ghana, Togo, and Benin, and into Uganda and Sudan in the east. Wild animals are largely nocturnal and spend the heat of the day sheltering in mammal burrows, termite mounds, and hollow logs. They feed primarily on small mammals — predominantly rats and shrews — supplemented with birds, particularly as juveniles. Adults are stocky and muscular, typically reaching three to five feet in length, with females substantially outweighing males of the same age. When threatened, the species curls itself into a tight ball with its head tucked at the center, a defensive posture so characteristic that it gave the snake its common name and its genus its original epithet.
                </p>
                <p>
                    Ball pythons were exported from West Africa in significant numbers beginning in the 1970s and 1980s, and for many years the hobby was sustained largely by wild-caught and farmed animals imported from Ghana, Togo, and Benin. That era left a complicated legacy: while it introduced the species to millions of keepers worldwide, it also placed pressure on wild populations and produced animals that were often heavily parasitized and prone to feeding refusals. The shift toward purpose-bred captive animals accelerated through the 1990s and transformed the ball python's standing in the hobby entirely. Today the overwhelming majority of animals offered for sale are multiple generations removed from the wild, and the species is one of the most thoroughly domesticated reptiles in captivity.
                </p>
                <p>
                    The pivotal event in the modern ball python hobby was the production of the first captive-bred albino in 1992 by breeder Bob Clark — an animal that sold for $15,000 and demonstrated that the species harbored extraordinary genetic potential. That proof of concept opened a floodgate. Over the following decades, breeders identified, isolated, and combined an ever-growing library of color and pattern mutations: spider, pinstripe, clown, axanthic, piebald, lesser, butter, yellowbelly, enchi, mojave, and hundreds of others, spanning dominant, co-dominant, and recessive modes of inheritance. Designer combinations of three, four, and five traits became routine, and the ball python ultimately became the most genetically complex domesticated reptile in existence, with more documented heritable mutations than any other snake species.
                </p>
                <p>
                    As a captive animal, the ball python's appeal rests on its manageable adult size, its exceptionally docile temperament, and a longevity that routinely exceeds twenty-five to thirty years in well-maintained collections. It is, however, a species that rewards attentive husbandry: ball pythons are famously prone to voluntary feeding refusals, particularly during the winter months or following any disruption to their environment, and keepers who are unprepared for this quirk sometimes mistake normal fasting behavior for illness. Appropriate humidity — typically 60 to 80 percent — is essential for successful sheds, and a well-furnished enclosure with ample hides is critical to the low-stress conditions that produce the most tractable animals. These requirements are modest by the standards of exotic reptiles, and the ball python remains, by a wide margin, the most popular python species kept in captivity worldwide.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
