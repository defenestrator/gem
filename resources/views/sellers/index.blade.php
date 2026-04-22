<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Sellers
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Search --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm mb-6">
                <form method="GET" action="{{ route('sellers.index') }}" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Search sellers by name or description…"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="submit"
                            class="bg-orange-500 text-white py-2 px-5 rounded-lg hover:bg-orange-700 font-semibold text-sm">
                            Search
                        </button>
                        @if ($search)
                            <a href="{{ route('sellers.index') }}"
                                class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 py-2 px-5 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold text-sm">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            @if ($sellers->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($sellers as $seller)
                        @php
                            $user       = $seller->user;
                            $photoUrl   = $user?->profile_photo_url ?? '';
                            $initial    = strtoupper(substr($seller->name, 0, 1));
                        @endphp

                        <a href="{{ route('sellers.show', $seller) }}"
                            class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">

                            {{-- Header strip --}}
                            <div class="h-20 bg-gradient-to-r from-orange-400 to-orange-600 relative">
                                {{-- Avatar --}}
                                <div class="absolute -bottom-8 left-5">
                                    @if ($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="{{ $seller->name }}"
                                            class="w-16 h-16 rounded-full object-cover ring-4 ring-white dark:ring-gray-800">
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-orange-100 dark:bg-orange-900/40 ring-4 ring-white dark:ring-gray-800 flex items-center justify-center">
                                            <span class="text-2xl font-bold text-orange-600 dark:text-orange-400 select-none">
                                                {{ $initial }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="pt-10 px-5 pb-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition truncate">
                                    {{ $seller->name }}
                                </h3>

                                @if ($seller->description)
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2 flex-1">
                                        {{ $seller->description }}
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-gray-400 dark:text-gray-500 italic flex-1">No description yet.</p>
                                @endif

                                <div class="mt-4 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 px-2.5 py-1 rounded-full">
                                        {{ $seller->classifieds_count ?? 0 }}
                                        {{ Str::plural('listing', $seller->classifieds_count ?? 0) }}
                                    </span>

                                    {{-- Social icons --}}
                                    <div class="flex items-center gap-2">
                                        @if ($seller->instagram)
                                            <span title="Instagram" class="text-gray-400 dark:text-gray-500">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                </svg>
                                            </span>
                                        @endif
                                        @if ($seller->youtube)
                                            <span title="YouTube" class="text-gray-400 dark:text-gray-500">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <path d="M19.59 6.69a4.83 4.83 0 01-3.77-2.75 12.76 12.76 0 00-8.62 0 4.83 4.83 0 01-3.77 2.75A12.86 12.86 0 002 12a12.86 12.86 0 001.43 5.31 4.83 4.83 0 013.77 2.75 12.76 12.76 0 008.62 0 4.83 4.83 0 013.77-2.75A12.86 12.86 0 0022 12a12.86 12.86 0 00-2.41-5.31zM10 15V9l5 3z"/>
                                                </svg>
                                            </span>
                                        @endif
                                        @if ($seller->morph_market)
                                            <span title="MorphMarket" class="text-gray-400 dark:text-gray-500">
                                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $sellers->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 fill-current text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM10 4h4v3h-4V4zm10 16H4V9h16v11z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">
                        @if ($search)
                            No sellers found matching <span class="font-semibold text-gray-700 dark:text-gray-300">"{{ $search }}"</span>.
                        @else
                            No sellers yet.
                        @endif
                    </p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
