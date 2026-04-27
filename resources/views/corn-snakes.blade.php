<x-guest-layout>
    <div class="w-full min-h-screen flex justify-center items-center">
        <div id="main-tile" class="text-left min-h-[70vh] bg-gray-800 text-gray-200 p-12 rounded-xl shadow-l2xl shadow-inner">
            <h1 class="mt-4 text-3xl text-orange-600">Corn Snakes</h1>
            <p class="mt-10 text-gray-500 italic text-sm">Pantherophis guttatus</p>
            <div class="mt-10 mx-auto flex justify-left">
                <h2 class="text-xl">Available Corn Snakes:</h2>
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
                                        MorphMarket
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

            <div class="mt-8 max-w-3xl space-y-4 text-gray-300 leading-relaxed">
                <p>
                    The corn snake (<em>Pantherophis guttatus</em>) is a medium-sized colubrid native to the eastern United States, ranging from southern New Jersey south through Florida and west into Louisiana and parts of Kentucky. It inhabits a wide variety of environments — pine barrens, hardwood forests, overgrown farmland, and rocky hillsides — where it shelters beneath loose bark, rotting logs, and debris. Wild corn snakes are largely crepuscular and nocturnal hunters, feeding primarily on small rodents and, particularly as juveniles, lizards and tree frogs. Adults typically reach three to five feet in length, with the record approaching six. The common name is most likely a reference to their historical association with corn cribs, grain stores that attracted the rodent prey on which these snakes depended.
                </p>
                <p>
                    In the wild, corn snakes display a base coloration of orange or brownish-yellow marked with large red or reddish-brown blotches outlined in black, a pattern that provides effective camouflage in leaf litter and pine straw. The ventral surface is typically checkered black and white, a pattern so distinctively reminiscent of Indian corn that some herpetologists favor it as the true origin of the common name. A closely related form once classified separately, the Great Plains rat snake (<em>P. emoryi</em>), is now understood to intergrade broadly with corn snakes across the central part of their range, and the two are frequently treated as subspecies.
                </p>
                <p>
                    Corn snakes occupy a foundational place in the history of modern herpetoculture. Captive breeding programs began in earnest during the 1960s and 1970s, and pioneering breeders — most notably Don Soderberg and Bill and Kathy Love — documented and selectively propagated the first color and pattern mutations. The amelanistic (anerythristic) and albino lines established during this era became the genetic building blocks for the extraordinary diversity of captive morphs available today. Hundreds of distinct morphs have now been characterized, spanning the full spectrum from hypo-melanistic pastels and snow whites to lavenders, charcoals, and ultra-complex designer combinations. The corn snake's relatively short generation time and large clutch sizes make it an ideal subject for genetic projects, and it remains the species around which much of the foundational vocabulary of reptile genetics was developed in a hobbyist context.
                </p>
                <p>
                    As a captive animal, the corn snake is widely regarded as one of the best beginner reptiles available. It is hardy, tolerant of a broad range of husbandry conditions, and reliably docile — most individuals tame quickly with regular, gentle handling. Adults thrive in modestly sized enclosures, feed readily on commercially available frozen-thawed rodents, and are far less prone to the feeding refusals and stress-related illness that can complicate keeping more sensitive species. Lifespans of fifteen to twenty years are common in captivity, making the corn snake a genuine long-term companion. These qualities, combined with the unmatched diversity of available morphs, explain why corn snakes consistently rank among the most popular captive reptiles in North America and Europe.
                </p>
            </div>
           
        </div>
    </div>
</x-guest-layout>