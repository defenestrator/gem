<x-guest-layout>
    @push('meta')
    <meta name="description" content="Captive-bred western hognose snakes (Heterodon nasicus) for sale from Gem Reptiles. Browse available morphs and contact us to inquire.">
    @endpush
    <div class="w-full min-h-screen flex justify-center items-center">
        <div id="main-tile" class="text-left min-h-[70vh] bg-gray-800 text-gray-200 p-12 rounded-xl shadow-l2xl shadow-inner">
            <h1 class="mt-4 text-3xl text-orange-600">Western Hognose Snakes</h1>
            <p class="mt-2 text-gray-500 italic text-sm">Heterodon nasicus</p>

            <div class="mt-10 mx-auto flex justify-left">
                <h2 class="text-xl">Available Western Hognose:</h2>
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

            <div class="mt-10 max-w-3xl space-y-4 text-gray-300 leading-relaxed">
                <p>
                    The western hognose snake (<em>Heterodon nasicus</em>) is a stout-bodied colubrid of the North American interior, native to the Great Plains from southern Canada through the central United States and into northern Mexico. It favors open, arid to semi-arid terrain with loose, sandy or gravelly substrate — shortgrass prairie, scrubland, dry riverbeds, and agricultural margins — where its most distinctive anatomical feature earns its keep: a sharply upturned rostral scale adapted for digging. Hognose snakes use this keratinous snout to excavate soil in search of prey and to unearth overwintering toads, which form the cornerstone of their diet in the wild. Lizards, small mammals, and the eggs of ground-nesting birds round out the menu.
                </p>
                <p>
                    Western hognose snakes are sexually dimorphic to a degree unusual among North American colubrids. Males are notably smaller than females, typically maturing at twelve to eighteen inches, while females commonly reach two to three feet and carry substantially more mass. Both sexes are diurnal hunters, active primarily in the morning hours, and this daytime activity pattern makes them unusually observable as captives. The dorsal patterning of wild-type animals is highly variable — brown, tan, or olive ground color overlaid with rows of darker blotches — and individuals from different parts of the range can differ enough in appearance to suggest distinct populations, a richness of variation that translated naturally into captive morph development.
                </p>
                <p>
                    No discussion of <em>Heterodon nasicus</em> is complete without its extraordinary defensive repertoire. When threatened, a western hognose will first attempt bluff: it spreads and flattens its neck into a cobra-like hood, hisses loudly, and may strike with a closed mouth. If the performance fails to deter a predator, the snake escalates to one of the most convincing acts in the reptile world — it rolls onto its back, writhes, voids its musk glands, and goes completely limp with its mouth agape, mimicking a freshly dead animal with unsettling commitment. Should the predator nudge it back upright, the snake will promptly roll over again, as if determined to maintain the fiction. This behavior, called thanatosis, is largely absent in well-acclimated captives, though the initial hissing display can persist even in otherwise docile animals.
                </p>
                <p>
                    Western hognose snakes entered herpetoculture steadily from the 1980s onward, but the pace of morph development accelerated sharply in the 2000s and 2010s as breeders recognized the species' genetic potential. The albino and axanthic mutations were among the earliest recessive traits documented and combined, producing the striking "snow" morph that helped establish the species as a serious hobby subject. Since then, breeders have identified and worked with a diverse array of additional mutations — anaconda, super conda, toxic, and lavender among them — generating a morph palette that rivals the corn snake's in breadth while the species' compact size and manageable husbandry requirements make it accessible to keepers with limited space. Today the western hognose is firmly established as one of the premier small colubrids in the hobby, prized equally for its personality, its theatrical tendencies, and the extraordinary range of colors and patterns available in captive-bred animals.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
