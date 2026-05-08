<x-app-layout>
@section('title', 'Animals for Sale')
    @push('meta')
    <meta name="description" content="Browse captive-bred reptiles for sale at Gem Reptiles. Filter by availability, species, and category.">
    @endpush
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Animals</h2>
    </x-slot>

    <div class="py-12"
         x-data="animalSearch"
         x-init="init('{{ route('animals.search') }}')">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Search input --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-6">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input type="text" x-model="query"
                        @input.debounce.300ms="doSearch(true)"
                        placeholder="Search by name, category, species…"
                        class="w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <button x-show="query" @click="query=''; doSearch(true)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        aria-label="Clear search">✕</button>
                </div>
            </div>

            {{-- Availability tabs --}}
            <div class="mb-4 flex flex-wrap gap-2">
                <button @click="availability=''; doSearch(true)"
                    :class="availability === '' ? 'bg-orange-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 hover:border-orange-400'"
                    class="px-3 py-1 rounded-full text-sm font-medium transition">All</button>
                @foreach ($availabilities as $av)
                <button @click="availability='{{ $av->value }}'; doSearch(true)"
                    :class="availability === '{{ $av->value }}'
                        ? '{{ $av->badgeClasses() }} ring-2 ring-offset-1 ring-current'
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 hover:border-orange-400'"
                    class="px-3 py-1 rounded-full text-sm font-medium transition">
                    {{ $av->label() }}
                </button>
                @endforeach
            </div>

            {{-- Sort pills --}}
            <div class="mb-6 flex flex-wrap gap-2 items-center">
                <span class="text-gray-600 dark:text-gray-400 font-semibold text-sm">Sort:</span>
                @foreach (['recent' => 'Newest', 'oldest' => 'Oldest', 'name-asc' => 'Name A–Z', 'name-desc' => 'Name Z–A'] as $val => $label)
                <button @click="sort='{{ $val }}'; doSearch(true)"
                    :class="sort === '{{ $val }}' ? 'bg-neutral-700 text-white' : 'bg-neutral-500 text-white hover:bg-neutral-600'"
                    class="px-3 py-1 rounded-lg text-sm font-medium transition">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Loading --}}
            <div x-show="loading" class="text-center py-12">
                <svg class="animate-spin h-8 w-8 text-orange-500 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </div>

            {{-- Results grid --}}
            <div x-show="!loading && searched">

                <template x-if="results.length === 0">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                        <p class="text-gray-600 dark:text-gray-300">No animals found.</p>
                    </div>
                </template>

                <template x-if="results.length > 0">
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <template x-for="(animal, idx) in results" :key="animal.id">
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition flex flex-col">
                                    <a :href="animal.show_url" class="relative block">
                                        <template x-if="animal.thumbnail">
                                            <img :src="animal.thumbnail" :alt="animal.pet_name"
                                                 :loading="idx === 0 ? 'eager' : 'lazy'"
                                                 :fetchpriority="idx === 0 ? 'high' : 'auto'"
                                                 class="w-full aspect-square object-cover">
                                        </template>
                                        <template x-if="!animal.thumbnail">
                                            <div class="w-full aspect-square bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <span class="text-gray-400 text-sm">No photo</span>
                                            </div>
                                        </template>
                                        <template x-if="animal.availability_label">
                                            <span class="absolute top-2 left-2 px-2.5 py-0.5 text-xs font-semibold rounded-full"
                                                  :class="animal.availability_badge"
                                                  x-text="animal.availability_label"></span>
                                        </template>
                                    </a>

                                    <div class="p-4 flex flex-col flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <h3 class="text-lg font-semibold text-orange-600 dark:text-orange-400 mr-2">
                                                <a :href="animal.show_url" class="hover:underline" x-text="animal.pet_name"></a>
                                            </h3>
                                            <span class="text-sm text-gray-500 dark:text-gray-400"
                                                  x-text="animal.female ? 'Female' : 'Male'"></span>
                                        </div>
                                        <template x-if="animal.species_name">
                                            <p class="text-xs italic text-gray-400 dark:text-gray-500 mb-2">
                                                <a :href="animal.species_url" class="hover:text-orange-500 hover:underline transition"
                                                   x-text="animal.species_name"></a>
                                            </p>
                                        </template>
                                        <template x-if="animal.category">
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                                <span class="font-semibold">Category:</span>
                                                <span x-text="animal.category"></span>
                                            </p>
                                        </template>
                                        <template x-if="animal.date_of_birth">
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                                                <span class="font-semibold">DOB:</span>
                                                <span x-text="animal.date_of_birth"></span>
                                            </p>
                                        </template>
                                        <template x-if="animal.availability === 'for_sale' && animal.price">
                                            <div class="flex items-center gap-3 mb-2">
                                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100"
                                                   x-text="'$' + animal.price"></p>
                                                <a :href="animal.inquire_url"
                                                   class="bg-orange-500 hover:bg-orange-700 text-white text-xs font-semibold py-1 px-3 rounded-lg transition">
                                                    Inquire
                                                </a>
                                            </div>
                                        </template>
                                        <template x-if="animal.description">
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3" x-text="animal.description"></p>
                                        </template>
                                        <a :href="animal.show_url"
                                           class="mt-auto bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 block text-sm font-bold text-center">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Pagination --}}
                        <template x-if="meta && meta.last_page > 1">
                            <div class="mt-6 flex justify-center gap-1 flex-wrap">
                                <button @click="goToPage(meta.current_page - 1)"
                                    :disabled="meta.current_page <= 1"
                                    class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700">&laquo;</button>
                                <template x-for="p in meta.last_page" :key="p">
                                    <button @click="goToPage(p)" x-text="p"
                                        :class="p === meta.current_page ? 'bg-orange-500 text-white border-orange-500' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                        class="px-3 py-1 rounded border text-sm"></button>
                                </template>
                                <button @click="goToPage(meta.current_page + 1)"
                                    :disabled="meta.current_page >= meta.last_page"
                                    class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700">&raquo;</button>
                            </div>
                        </template>

                        <template x-if="meta">
                            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-3"
                               x-text="`${meta.total} animal${meta.total === 1 ? '' : 's'}`"></p>
                        </template>
                    </div>
                </template>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('animalSearch', () => ({
            endpoint:     '',
            query:        '',
            sort:         'recent',
            availability: '',
            results:      [],
            loading:      false,
            searched:     false,
            page:         1,
            meta:         null,

            init(endpoint) {
                this.endpoint     = endpoint;
                this.query        = sessionStorage.getItem('animals_query') || '';
                this.sort         = sessionStorage.getItem('animals_sort') || 'recent';
                this.availability = sessionStorage.getItem('animals_availability') || '';
                this.page         = parseInt(sessionStorage.getItem('animals_page') || '1');
                this.doSearch();
            },

            goToPage(p) {
                this.page = p;
                this.doSearch();
                this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
            },

            async doSearch(resetPage = false) {
                if (resetPage) this.page = 1;

                sessionStorage.setItem('animals_query', this.query);
                sessionStorage.setItem('animals_sort', this.sort);
                sessionStorage.setItem('animals_availability', this.availability);
                sessionStorage.setItem('animals_page', this.page);

                this.loading = true;

                try {
                    const params = new URLSearchParams({
                        q:    this.query.trim(),
                        sort: this.sort,
                        page: this.page,
                    });
                    if (this.availability) params.set('availability', this.availability);

                    const res = await fetch(`${this.endpoint}?${params}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const data    = await res.json();
                    this.results  = data.results;
                    this.meta     = data.meta;
                    this.searched = true;

                    if (this.page === 1 && data.results[0]?.thumbnail) {
                        const link = document.createElement('link');
                        link.rel = 'preload'; link.as = 'image';
                        link.href = data.results[0].thumbnail;
                        link.setAttribute('fetchpriority', 'high');
                        document.head.appendChild(link);
                    }
                } catch (e) {
                    console.error('Animal search failed:', e);
                    this.results  = [];
                    this.searched = true;
                } finally {
                    this.loading = false;
                }
            },
        }));
    });
    </script>
    @endpush
</x-app-layout>
