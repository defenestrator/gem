<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Species Database
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"
            x-data="speciesSearch"
            x-init="init('{{ route('species.search') }}', '{{ url('/species') }}')">

            {{-- Search input --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Search species
                </label>
                <div class="relative mb-3 flex gap-2">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            x-model="query"
                            @input.debounce.300ms="doSearch(true)"
                            placeholder="Scientific name, common name, or family…"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400"
                            autocomplete="off"
                            spellcheck="false"
                        >
                        {{-- spinner --}}
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none"
                             x-show="loading" x-transition>
                            <svg class="animate-spin h-4 w-4 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>
                    <button type="button" @click="clearSearch()"
                            x-show="query.length > 0" x-transition
                            class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Clear
                    </button>
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-2 mt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" x-model="hasMedia" @change="doSearch(true)"
                               class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-400">
                        Has photos
                    </label>
                    <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">|</span>
                    @foreach ([
                        'lizards'      => 'Lizards',
                        'snakes'       => 'Snakes',
                        'geckos'       => 'Geckos',
                        'turtles'      => 'Turtles &amp; Tortoises',
                        'amphisbaenia' => 'Amphisbaenia',
                        'crocodilians' => 'Crocodilians',
                    ] as $key => $label)
                    <button type="button"
                            @click="taxon = (taxon === '{{ $key }}' ? '' : '{{ $key }}'); doSearch(true)"
                            :class="taxon === '{{ $key }}'
                                ? 'bg-orange-500 text-white border-orange-500'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-orange-400'"
                            class="px-3 py-1 text-sm rounded-full border transition select-none">
                        {!! $label !!}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Results --}}
            <template x-if="searched && results.length === 0 && !loading">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center text-gray-500 dark:text-gray-400">
                    <template x-if="lastQuery">
                        <span>No species found for "<span x-text="lastQuery"></span>".</span>
                    </template>
                    <template x-if="!lastQuery">
                        <span>No species found.</span>
                    </template>
                </div>
            </template>

            <template x-if="results.length > 0">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            <template x-if="meta">
                                <span>
                                    <span x-text="meta.total.toLocaleString()"></span> result<span x-show="meta.total !== 1">s</span>
                                    <span x-show="lastQuery"> for "<span class="font-medium text-gray-700 dark:text-gray-300" x-text="lastQuery"></span>"</span>
                                    &mdash; page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
                                </span>
                            </template>
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-2 py-3 text-center font-semibold w-14">Photo</th>
                                    <th class="px-4 py-3 text-left font-semibold">Scientific Name</th>
                                    <th class="px-4 py-3 text-left font-semibold">Common Name</th>
                                    <th class="px-4 py-3 text-left font-semibold hidden md:table-cell">Family / Taxon</th>
                                    <th class="px-4 py-3 text-left font-semibold hidden lg:table-cell">Author</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="row in results" :key="row.id">
                                    <tr class="hover:bg-orange-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-2 py-2 text-center">
                                            <template x-if="row.thumbnail">
                                                <a :href="`${showBase}/${row.id}`">
                                                    <img :src="row.thumbnail" :alt="row.species"
                                                         class="h-10 w-10 object-cover rounded-md mx-auto ring-1 ring-gray-200 dark:ring-gray-600">
                                                </a>
                                            </template>
                                            <template x-if="!row.thumbnail">
                                                <span class="inline-block h-10 w-10 rounded-md bg-gray-100 dark:bg-gray-700 mx-auto"></span>
                                            </template>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a :href="`${showBase}/${row.id}`"
                                               class="italic font-medium text-orange-600 dark:text-orange-400 hover:underline"
                                               x-text="row.species"></a>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-normal break-words"
                                            x-text="row.common_name || '—'"></td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 hidden md:table-cell text-xs"
                                            x-text="row.higher_taxa || '—'"></td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 hidden lg:table-cell text-xs"
                                            x-text="row.author || '—'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <template x-if="meta && meta.last_page > 1">
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2 flex-wrap">
                            <button @click="goToPage(meta.current_page - 1)"
                                    :disabled="meta.current_page <= 1"
                                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300
                                           hover:bg-orange-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                &larr; Prev
                            </button>

                            <div class="flex items-center gap-1 flex-wrap justify-center">
                                <template x-for="p in meta.last_page" :key="p">
                                    <button @click="goToPage(p)"
                                            :class="p === meta.current_page
                                                ? 'bg-orange-500 text-white border-orange-500'
                                                : 'text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-orange-50 dark:hover:bg-gray-700'"
                                            x-show="p === 1 || p === meta.last_page || Math.abs(p - meta.current_page) <= 2"
                                            class="px-3 py-1.5 text-sm rounded-lg border transition"
                                            x-text="p">
                                    </button>
                                </template>
                            </div>

                            <button @click="goToPage(meta.current_page + 1)"
                                    :disabled="meta.current_page >= meta.last_page"
                                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300
                                           hover:bg-orange-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                Next &rarr;
                            </button>
                        </div>
                    </template>
                </div>
            </template>


        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('speciesSearch', () => ({
            endpoint:  '',
            showBase:  '',
            query:       '',
            lastQuery:   '',
            results:     [],
            loading:     false,
            searched:    false,
            hasMedia:    false,
            taxon:       '',
            page:        1,
            meta:        null,
            cache:       {},

            init(endpoint, showBase) {
                this.endpoint = endpoint;
                this.showBase = showBase;
                this.hasMedia = sessionStorage.getItem('species_has_media') === '1';
                this.taxon    = sessionStorage.getItem('species_taxon') || '';
                this.query    = sessionStorage.getItem('species_search_query') || '';
                this.doSearch();
            },

            clearSearch() {
                this.query = '';
                this.taxon = '';
                this.page  = 1;
                sessionStorage.removeItem('species_search_query');
                sessionStorage.removeItem('species_taxon');
                this.doSearch();
            },

            goToPage(p) {
                this.page = p;
                this.doSearch();
                this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
            },

            async doSearch(resetPage = false) {
                const q = this.query.trim();

                if (resetPage) this.page = 1;

                sessionStorage.setItem('species_has_media', this.hasMedia ? '1' : '0');
                sessionStorage.setItem('species_taxon', this.taxon);
                sessionStorage.setItem('species_search_query', q);

                const key = q.toLowerCase() + (this.hasMedia ? ':media' : '') + (this.taxon ? ':' + this.taxon : '') + ':p' + this.page;
                if (this.cache[key] !== undefined) {
                    this.results   = this.cache[key].results;
                    this.meta      = this.cache[key].meta;
                    this.lastQuery = q;
                    this.searched  = true;
                    return;
                }

                this.loading = true;

                try {
                    const params = new URLSearchParams({ q, page: this.page });
                    if (this.hasMedia) params.set('has_media', '1');
                    if (this.taxon) params.set('taxon', this.taxon);
                    const res = await fetch(`${this.endpoint}?${params}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const data = await res.json();
                    this.cache[key] = { results: data.results, meta: data.meta };
                    this.results    = data.results;
                    this.meta       = data.meta;
                    this.lastQuery  = q;
                    this.searched   = true;
                } catch (e) {
                    console.error('Species search failed:', e);
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
