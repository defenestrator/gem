<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $seller->name }}
        </h2>
    </x-slot>

    @php
        $user     = $seller->user;
        $photoUrl = $user?->profile_photo_url ?? '';
        $initial  = strtoupper(substr($seller->name, 0, 1));
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Profile card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">

                {{-- Banner --}}
                <div class="h-32 bg-gradient-to-r from-orange-400 to-orange-600 relative">
                    <div class="absolute -bottom-10 left-6">
                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $seller->name }}"
                                class="w-20 h-20 rounded-full object-cover ring-4 ring-white dark:ring-gray-800">
                        @else
                            <div class="w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-900/40 ring-4 ring-white dark:ring-gray-800 flex items-center justify-center">
                                <span class="text-3xl font-bold text-orange-600 dark:text-orange-400 select-none">
                                    {{ $initial }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-14 pb-6 px-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $seller->name }}
                            </h1>
                            @if ($seller->description)
                                <p class="mt-2 text-gray-600 dark:text-gray-400 max-w-prose">
                                    {{ $seller->description }}
                                </p>
                            @endif
                        </div>

                        {{-- Contact --}}
                        @if ($seller->email || $seller->phone)
                            <div class="shrink-0 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                @if ($seller->email)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 fill-current text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <path d="M0 3v18h24v-18h-24zm21.518 2l-9.518 7.713-9.518-7.713h19.036zm-19.518 14v-11.817l10 8.104 10-8.104v11.817h-20z"/>
                                        </svg>
                                        <a href="mailto:{{ $seller->email }}"
                                            class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                            {{ $seller->email }}
                                        </a>
                                    </div>
                                @endif
                                @if ($seller->phone)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 fill-current text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/>
                                        </svg>
                                        <span>{{ $seller->phone }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Social / external links --}}
                    @php
                        $links = array_filter([
                            'Website'     => ['url' => $seller->website,      'icon' => 'M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16zm-1-11h2v2h2v2h-2v2h-2v-2H9v-2h2v-2z'],
                            'Instagram'   => ['url' => $seller->instagram ? ('https://instagram.com/' . ltrim($seller->instagram, '@')) : null, 'icon' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'],
                            'YouTube'     => ['url' => $seller->youtube,      'icon' => 'M19.59 6.69a4.83 4.83 0 01-3.77-2.75 12.76 12.76 0 00-8.62 0 4.83 4.83 0 01-3.77 2.75A12.86 12.86 0 002 12a12.86 12.86 0 001.43 5.31 4.83 4.83 0 013.77 2.75 12.76 12.76 0 008.62 0 4.83 4.83 0 013.77-2.75A12.86 12.86 0 0022 12a12.86 12.86 0 00-2.41-5.31zM10 15V9l5 3z'],
                            'Facebook'    => ['url' => $seller->facebook,     'icon' => 'M22.676 0H1.324C.593 0 0 .593 0 1.324v21.352C0 23.408.593 24 1.324 24h11.494v-9.294H9.689v-3.621h3.129V8.41c0-3.099 1.894-4.785 4.659-4.785 1.325 0 2.464.097 2.796.141v3.24h-1.921c-1.5 0-1.792.721-1.792 1.771v2.311h3.584l-.465 3.63H16.56V24h6.115c.733 0 1.325-.592 1.325-1.324V1.324C24 .593 23.408 0 22.676 0'],
                            'MorphMarket'=> ['url' => $seller->morph_market,  'icon' => 'M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16zm-1-13h2v6h-2zm0 8h2v2h-2z'],
                        ]);
                    @endphp

                    @if (count($links))
                        <div class="mt-5 flex flex-wrap items-center gap-3">
                            @foreach ($links as $label => $link)
                                @if ($link['url'])
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <path d="{{ $link['icon'] }}"/>
                                        </svg>
                                        {{ $label }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if(config('features.classifieds'))
            {{-- Active listings --}}
            <div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">
                    Active Listings
                    @if ($classifieds instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <span class="text-base font-normal text-gray-500 dark:text-gray-400">({{ $classifieds->total() }})</span>
                    @endif
                </h2>

                @if ($classifieds instanceof \Illuminate\Pagination\LengthAwarePaginator && $classifieds->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($classifieds as $classified)
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                                @if ($classified->media->isNotEmpty())
                                    <a href="{{ route('classifieds.show', $classified) }}">
                                        <img src="{{ $classified->media->first()->url }}"
                                            alt="{{ $classified->title }}"
                                            class="w-full aspect-square object-cover">
                                    </a>
                                @endif
                                <div class="p-4 flex flex-col flex-1">
                                    <h3 class="font-semibold text-orange-600 dark:text-orange-400 hover:underline">
                                        <a href="{{ route('classifieds.show', $classified) }}">
                                            {{ $classified->title }}
                                        </a>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2 flex-1">
                                        {{ $classified->description }}
                                    </p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                            ${{ number_format($classified->price, 2) }}
                                        </span>
                                        <a href="{{ route('classifieds.show', $classified) }}"
                                            class="text-sm bg-orange-500 text-white px-3 py-1 rounded-lg hover:bg-orange-700 font-semibold">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $classifieds->links() }}
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
                        <p class="text-gray-500 dark:text-gray-400">This seller has no active listings at the moment.</p>
                    </div>
                @endif
            </div>
            @endif

            {{-- Back --}}
            <div>
                <a href="{{ route('sellers.index') }}"
                    class="text-orange-600 dark:text-orange-400 hover:underline font-semibold text-sm">
                    ← Back to All Sellers
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
