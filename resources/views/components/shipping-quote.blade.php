@props(['compact' => false])

<div
    x-data="{
        zip: '',
        loading: false,
        rates: [],
        error: '',
        async getQuote() {
            if (!/^\d{5}$/.test(this.zip)) {
                this.error = 'Enter a valid 5-digit ZIP code.';
                return;
            }
            this.loading = true;
            this.rates   = [];
            this.error   = '';
            try {
                const res = await fetch('{{ route('shipping.quote') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ zip_code: this.zip }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.error = data.error ?? 'Unable to get quote.';
                } else {
                    this.rates = data.rates;
                }
            } catch {
                this.error = 'Unable to get quote. Try again.';
            } finally {
                this.loading = false;
            }
        }
    }"
    @class([
        'mt-3',
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
            @click="getQuote()"
            :disabled="loading"
            class="bg-orange-500 hover:bg-orange-700 disabled:opacity-50 text-white text-sm font-semibold py-1.5 px-3 rounded-lg transition whitespace-nowrap"
        >
            <span x-show="!loading">Get Quote</span>
            <span x-show="loading" aria-label="Loading">…</span>
        </button>
    </div>

    <p x-show="error" x-text="error" class="mt-2 text-xs text-red-600 dark:text-red-400"></p>

    <div x-show="rates.length" class="mt-3 space-y-1.5">
        <template x-for="rate in rates" :key="rate.service">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400" x-text="rate.label"></span>
                <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="'$' + rate.price"></span>
            </div>
        </template>
        <p class="text-xs text-gray-400 dark:text-gray-500 pt-1">Pickup at your nearest FedEx Ship Center. Rates are estimates.</p>
    </div>
</div>
