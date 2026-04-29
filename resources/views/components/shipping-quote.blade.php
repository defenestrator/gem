@props(['compact' => false])

<div
    x-data="{
        zip: '',
        loading: false,
        locating: false,
        rates: [],
        error: '',
        shipCenter: null,

        async useMyLocation() {
            if (!navigator.geolocation) {
                this.error = 'Geolocation is not supported by your browser.';
                return;
            }
            this.locating = true;
            this.error = '';
            this.shipCenter = null;
            this.rates = [];

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    try {
                        const res = await fetch('{{ route('shipping.location') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            }),
                        });
                        const data = await res.json();
                        if (!res.ok) {
                            this.error = data.error ?? 'Could not find a nearby FedEx Ship Center.';
                        } else {
                            this.zip = data.location.postal_code;
                            await this.getQuote();
                        }
                    } catch {
                        this.error = 'Could not find a nearby FedEx Ship Center.';
                    } finally {
                        this.locating = false;
                    }
                },
                () => {
                    this.error = 'Location access denied. Enter your ZIP code manually.';
                    this.locating = false;
                }
            );
        },

        async getQuote() {
            if (!/^\d{5}$/.test(this.zip)) {
                this.error = 'Enter a valid 5-digit ZIP code.';
                return;
            }
            this.loading = true;
            this.rates   = [];
            this.error   = '';
            this.shipCenter = null;
            try {
                const res = await fetch('{{ route('shipping.quote') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        zip_code: this.zip,
                    }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.error = data.error ?? 'Unable to get quote.';
                } else {
                    this.rates = data.rates;
                    this.shipCenter = data.ship_center;
                }
            } catch {
                this.error = 'Unable to get quote. Try again.';
            } finally {
                this.loading = false;
            }
        }
    }"
    @class([
        'mt-3 mb-6',
        'p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg mt-4' => ! $compact,
    ])
>
    @unless ($compact)
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Shipping Estimate</p>
    @endunless

    <div class="flex gap-2">
        <input
            type="text"
            x-model="zip"
            @keydown.enter.prevent="getQuote()"
            maxlength="5"
            placeholder="Your ZIP code"
            class="flex-1 min-w-0 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
        >
        <button
            type="button"
            @click="useMyLocation()"
            :disabled="locating || loading"
            title="Find nearest FedEx Ship Center"
            class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 disabled:opacity-50 text-gray-700 dark:text-gray-200 text-sm py-1.5 px-2.5 rounded-lg transition"
        >
            <span x-show="!locating">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </span>
            <span x-show="locating" aria-label="Locating">…</span>
        </button>
        <button
            type="button"
            @click="getQuote()"
            :disabled="loading || locating"
            class="bg-orange-500 hover:bg-orange-700 disabled:opacity-50 text-white text-sm font-semibold py-1.5 px-3 rounded-lg transition whitespace-nowrap"
        >
            <span x-show="!loading">Get Quote</span>
            <span x-show="loading" aria-label="Loading">…</span>
        </button>
    </div>

    <template x-if="shipCenter">
        <div class="mt-2 flex items-start gap-1.5 text-xs text-blue-700 dark:text-blue-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>
                <span class="font-semibold">Nearest FedEx Ship Center: </span>
                <span x-text="shipCenter.address + ', ' + shipCenter.city + ', ' + shipCenter.state + ' ' + shipCenter.postal_code"></span>
            </span>
        </div>
    </template>

    <p x-show="error" x-text="error" class="mt-2 text-xs text-red-600 dark:text-red-400"></p>

    <div x-show="rates.length" class="mt-3 space-y-1.5">
        <template x-for="rate in rates" :key="rate.service">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400" x-text="rate.label"></span>
                <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="'$' + rate.price"></span>
            </div>
        </template>
        <p class="text-xs text-gray-400 dark:text-gray-500 pt-1">Hold for pickup at your nearest FedEx Ship Center. Rates are estimates.</p>
    </div>
</div>
