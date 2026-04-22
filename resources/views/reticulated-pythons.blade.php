<x-guest-layout>
    <div class="w-full min-h-screen flex justify-center items-center">
        <div id="main-tile" class="text-left min-h-[70vh] bg-gray-800 text-gray-200 p-12 rounded-xl shadow-l2xl shadow-inner">
            <h1 class="mt-4 text-3xl text-orange-600">Reticulated Pythons</h1>
            <p class="mt-2 text-gray-500 italic text-sm">Malayopython reticulatus</p>

            <div class="mt-10 mx-auto flex justify-left">
                <h2 class="text-xl">Available Reticulated Pythons:</h2>
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
                    The reticulated python (<em>Malayopython reticulatus</em>) holds the distinction of being the world's longest snake species, with verified records exceeding twenty-five feet and reliable reports of animals surpassing twenty feet relatively common in the wild. Its range spans Southeast Asia from Bangladesh and northeastern India through Myanmar, Thailand, Malaysia, the Philippines, and across the Indonesian archipelago — one of the broadest distributions of any large constrictor, encompassing tropical rainforest, mangrove swamp, agricultural land, and even the margins of urban centers. The species is strongly associated with water and is a capable swimmer; island colonization across the Indonesian chain has occurred naturally and repeatedly throughout its evolutionary history. Wild reticulated pythons are opportunistic predators that take a wide variety of prey, from small rodents and birds to deer, pigs, and — in exceptional cases — large primates.
                </p>
                <p>
                    The common name derives from the snake's instantly recognizable dorsal pattern: an intricate geometric network of tan, gold, black, and white markings that covers the entire body in an interlocking mosaic. The Latin <em>reticulatus</em> means net-like, and no description is more apt. The scales carry a pronounced iridescent sheen in direct light, producing a shifting blue-green luminescence that makes even wild-type animals among the most visually spectacular of all snakes. Females grow substantially larger than males — a pattern consistent with many pythons — and large females are among the most massive snakes by weight as well as length, though the green anaconda (<em>Eunectes murinus</em>) holds the record for overall body mass.
                </p>
                <p>
                    A critical development in making reticulated pythons accessible to a broader range of keepers has been the recognition and selective breeding of dwarf and super-dwarf localities. Several small Indonesian islands — among them Kayuadi, Kalatoa, Jampea, and Madu — are home to insular populations that have evolved reduced adult size in response to limited prey availability, a well-documented phenomenon in island biogeography. Dwarf locality animals typically cap out at eight to twelve feet, and true super-dwarfs from the most isolated populations may mature at six to eight feet with a correspondingly finer build. Selective pairing of these locality animals, and careful outcrossing to introduce morph genetics while preserving the size reduction, has produced a generation of captive-bred reticulated pythons that are genuinely manageable for experienced keepers who lack the space or infrastructure for a full-sized mainland animal.
                </p>
                <p>
                    Captive morph development in reticulated pythons has accelerated dramatically since the early 2000s. Bob Clark's albino line — first produced in the mid-1990s — established the proof of concept, and subsequent work has added tiger, sunfire, motley, genetic stripe, ivory, and lavender mutations, among many others, to the breeders' toolkit. The interaction of these traits with locality-derived size genetics has produced animals that combine extraordinary coloration with a manageable adult size, a combination that has driven significant growth in the species' hobby presence. Reticulated pythons are not a beginner species — their intelligence, strength, and size demand experienced, confident handling and purpose-built secure enclosures — but captive-bred animals, particularly those with dwarf locality influence, can become remarkably tractable with consistent, respectful interaction from a young age. For the keeper prepared to meet their requirements, few reptiles offer a comparable combination of scale, beauty, and presence.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
