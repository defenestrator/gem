<x-guest-layout>
    @php
        // Resolve first visible animal image for LCP preload hint in <head>
        $lcpImage = null;
        foreach ($animals as $_a) {
            if ($_a['State'] === 'For Sale' && $_a['Enabled'] === 'Active' && !empty($_a['Photo_Urls'])) {
                $lcpImage = explode(' ', $_a['Photo_Urls'])[0];
                break;
            }
        }
        unset($_a);
    @endphp
    @push('meta')
    <meta name="description" content="Shop captive-bred ball pythons, corn snakes, carpet pythons, reticulated pythons, and western hognose snakes from Gem Reptiles. Quality reptiles bred with care.">
    @if($lcpImage)
    <link rel="preload" as="image" href="{{ $lcpImage }}" fetchpriority="high">
    @endif
    @endpush
    <div class="w-full min-h-screen flex justify-center items-center pb-12">
        <div id="main-tile" class="text-left min-h-[70vh] bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 p-6 rounded-xl shadow-l2xl shadow-inner pb-12">
            <h1 class="my-2 text-3xl text-green-500 font-serif">The Wild Type</h1>
            <h2 class="my-2 text-l" >Captive-bred Pythons and Colubrids by <span class="font-medium text-green-400 font-serif">Gem Reptiles</span></h2>
            {{-- <h5 class="my-4">
                <a href="https://www.morphmarket.com/stores/gem" title="Gem Reptiles MorphMarket Store">
                    <button class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700">
                    Gem Reptiles on MorphMarket
                    </button>   
                </a>
            </h5> --}}
            <div class="mx-auto flex justify-left">
                <h2>Available Animals:</h2>
            </div>
            
            <div class="mt-6 flex flex-wrap gap-1 items-center">
                
                <a href="{{ route('welcome', ['sort' => 'recent']) }}" title="Sort by most recently updated" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'recent' ? 'bg-neutral-700' : 'bg-neutral-500 dark:bg-neutral-700 hover:bg-neutral-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Updated</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'price-low']) }}" title="Sort by lowest price first" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'price-low' ? 'bg-neutral-700' : 'bg-neutral-500 dark:bg-neutral-700 hover:bg-neutral-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Lowest Price</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'price-high']) }}" title="Sort by highest price first" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'price-high' ? 'bg-neutral-700' : 'bg-neutral-500 dark:bg-neutral-700 hover:bg-neutral-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Highest Price</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'date-new']) }}" title="Sort by newest hatch date first" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'date-new' ? 'bg-neutral-700' : 'bg-neutral-500 dark:bg-neutral-700 hover:bg-neutral-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Newest Hatched</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'category']) }}" title="Sort by category A to Z" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'category' ? 'bg-neutral-700' : 'bg-neutral-500 dark:bg-neutral-700 hover:bg-neutral-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Category (Asc)</span>
                </a>
                <a href="{{ route('welcome', ['sort' => 'category-desc']) }}" title="Sort by category Z to A" class="px-2 py-0.5 rounded-lg {{ $currentSort === 'category-desc' ? 'bg-neutral-700' : 'bg-neutral-500 dark:bg-neutral-700 hover:bg-neutral-600' }} text-white text-sm font-medium">
                    <span class="text-xs">Category (Desc)</span>
                </a>
            </div>
            
            @php $cardIndex = 0; @endphp
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                @foreach($animals as $animal)
                    @if($animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active')
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-md overflow-hidden flex flex-col">
                            @if($animal['Photo_Urls'])
                                @php
                                    $photos = explode(' ', $animal['Photo_Urls']);
                                    $firstPhoto = $photos[0];
                                    $isLcp = ($cardIndex === 0);
                                    $cardIndex++;
                                    $imgSrc = $isLcp ? $firstPhoto : ($animal['Thumbnail_Url'] ?? $firstPhoto);
                                    $altText = $animal['Title*'] . ' — ' . $animal['Category*']
                                        . (!empty($animal['Sex']) ? ', ' . $animal['Sex'] : '')
                                        . (!empty($animal['Traits']) ? ' (' . $animal['Traits'] . ')' : '')
                                        . ', captive-bred by Gem Reptiles';
                                @endphp
                                <img src="{{ $imgSrc }}"
                                     alt="{{ $altText }}"
                                     width="800" height="800"
                                     @if($isLcp) fetchpriority="high" @else loading="lazy" decoding="async" @endif
                                     class="w-full aspect-square object-cover">
                            @endif
                            <div class="p-4 flex flex-col flex-1">
                            <h3 class="text-lg font-semibold font-serif text-orange-600 dark:text-orange-400 mb-1">{{ $animal['Title*'] }}</h3>
                            @php $species = $speciesMap[$animal['Animal_Id*']] ?? null; @endphp
                            @if ($species)
                                <p class="text-xs italic text-gray-500 dark:text-gray-400 mb-2">
                                    <a href="{{ route('species.show', $species) }}"
                                       title="View {{ $species->species }} species information"
                                       class="hover:text-orange-400 hover:underline transition">
                                        {{ $species->species }}
                                    </a>
                                </p>
                            @endif
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Category:</span> {{ $animal['Category*'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Traits:</span> {{ $animal['Traits'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Maturity:</span> {{ $animal['Maturity'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Sex:</span> {{ $animal['Sex'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Origin:</span> {{ $animal['Origin'] }}</p>
                            @if($animal['Desc'])
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2"><span class="font-serif font-bold">Description:</span> {{ Str::limit($animal['Desc'], 150) }}</p>
                            @endif
                            @if($animal['Diet'])
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Diet:</span> {{ $animal['Diet'] }}</p>
                            @endif
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Shipping:</span> ${{ $animal['Min_Shipping'] }} - ${{ $animal['Max_Shipping'] }}</p>
                            @if($animal['Is_Negotiable'] === 'Will Consider')
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Negotiable:</span> Yes</p>
                            @endif
                            @if($animal['Is_For_Trade'] === 'Will Consider')
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><span class="font-serif font-bold">Trades:</span> Considered</p>
                            @endif
                            <p class="text-lg font-serif font-black text-green-600 dark:text-green-200 mb-4 mt-auto">${{ $animal['Price'] }}</p>
                            <div class="flex gap-2">
                                <a href="{{ $animal['Mm_Url**'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   title="View {{ $animal['Title*'] }} on MorphMarket"
                                   class="flex-1 bg-orange-500 text-white py-2 px-2 rounded-lg hover:bg-orange-700 text-sm text-center font-semibold">
                                    MorphMarket
                                </a>
                                <a href="{{ route('animals.inquiries.create', ['animal' => $animal['Animal_Id*']]) }}"
                                   title="Send an inquiry about {{ $animal['Title*'] }}"
                                   class="flex-1 bg-green-600 text-white py-2 px-2 rounded-lg hover:bg-green-800 text-sm font-semibold text-center">
                                    Inquire
                                </a>
                            </div>
                            </div>
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

