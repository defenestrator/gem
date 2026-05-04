<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Species Database
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"
            x-data="speciesSearch"
            x-init="init('{{ route('species.search') }}', '{{ url('/species') }}', '{{ \App\Support\SpeciesSearchTerms::random() }}')">

            {{-- Search input --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Search species
                </label>
                <div class="relative">
                    <input
                        type="text"
                        x-model="query"
                        @input.debounce.300ms="doSearch()"
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
            </div>

            {{-- Results --}}
            <template x-if="searched && results.length === 0 && !loading">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center text-gray-500 dark:text-gray-400">
                    No species found for "<span x-text="lastQuery"></span>".
                </div>
            </template>

            <template x-if="results.length > 0">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            <span x-text="results.length"></span> result<span x-show="results.length !== 1">s</span>
                            for "<span class="font-medium text-gray-700 dark:text-gray-300" x-text="lastQuery"></span>"
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Scientific Name</th>
                                    <th class="px-4 py-3 text-left font-semibold">Common Name</th>
                                    <th class="px-4 py-3 text-left font-semibold hidden md:table-cell">Family / Taxon</th>
                                    <th class="px-4 py-3 text-left font-semibold hidden lg:table-cell">Author</th>
                                    <th class="px-4 py-3 text-center font-semibold hidden lg:table-cell w-16">Photo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="row in results" :key="row.id">
                                    <tr class="hover:bg-orange-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3">
                                            <a :href="`${showBase}/${row.id}`"
                                               class="italic font-medium text-orange-600 dark:text-orange-400 hover:underline"
                                               x-text="row.species"></a>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300"
                                            x-text="row.common_name || '—'"></td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 hidden md:table-cell text-xs"
                                            x-text="row.higher_taxa || '—'"></td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 hidden lg:table-cell text-xs"
                                            x-text="row.author || '—'"></td>
                                        <td class="px-4 py-2 text-center hidden lg:table-cell">
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
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            {{-- Empty state before any search --}}
            <template x-if="!searched && !loading">
                <div class="bg-white dark:bg-gray-800 p-10 rounded-lg shadow-md text-center text-gray-400 dark:text-gray-500">
                    <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                    <p class="text-sm">Type a name to search 11,000+ reptile species.</p>
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
            query:     '',
            lastQuery: '',
            results:   [],
            loading:   false,
            searched:  false,
            cache:     {},

            init(endpoint, showBase, randomSeed) {
                this.endpoint = endpoint;
                this.showBase = showBase;
                const saved = sessionStorage.getItem('species_search_query');
                this.query = (saved !== null && saved !== '') ? saved : randomSeed;
                this.doSearch();
            },

            async doSearch() {
                const q = this.query.trim();

                if (q.length < 2) {
                    this.results  = [];
                    this.searched = false;
                    sessionStorage.removeItem('species_search_query');
                    return;
                }

                sessionStorage.setItem('species_search_query', q);

                const key = q.toLowerCase();
                if (this.cache[key] !== undefined) {
                    this.results   = this.cache[key];
                    this.lastQuery = q;
                    this.searched  = true;
                    return;
                }

                this.loading = true;

                try {
                    const res = await fetch(`${this.endpoint}?q=${encodeURIComponent(q)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const data = await res.json();
                    this.cache[key] = data.results;
                    this.results    = data.results;
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
