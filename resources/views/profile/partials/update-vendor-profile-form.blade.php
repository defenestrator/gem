<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Vendor Profile') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Set up your public seller profile so buyers can find and contact you.') }}
        </p>
    </header>

    @php $seller = $user->seller; @endphp

    <form method="POST" action="{{ route('profile.seller.save') }}" class="mt-6 space-y-6">
        @csrf
        @method('PATCH')

        {{-- Shop / Display Name --}}
        <div>
            <x-input-label for="seller_name" :value="__('Shop / Display Name')" />
            <x-text-input id="seller_name" name="name" type="text" class="mt-1 block w-full"
                :value="old('name', $seller?->name)"
                placeholder="e.g. Riverside Reptiles"
                required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Bio / Description --}}
        <div>
            <x-input-label for="seller_description" :value="__('Bio / Description')" />
            <textarea id="seller_description" name="description" rows="4"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                placeholder="Tell buyers about your breeding programme, experience and specialties…">{{ old('description', $seller?->description) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        {{-- Contact info --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="seller_email" :value="__('Contact Email')" />
                <x-text-input id="seller_email" name="email" type="email" class="mt-1 block w-full"
                    :value="old('email', $seller?->email)"
                    placeholder="shop@example.com" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label for="seller_phone" :value="__('Phone')" />
                <x-text-input id="seller_phone" name="phone" type="text" class="mt-1 block w-full"
                    :value="old('phone', $seller?->phone)"
                    placeholder="+1 555 000 1234" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        </div>

        {{-- Online presence --}}
        <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('Online Presence') }}</p>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-28 shrink-0 text-sm text-gray-500 dark:text-gray-400">Website</span>
                    <x-text-input name="website" type="url" class="block w-full"
                        :value="old('website', $seller?->website)"
                        placeholder="https://yoursite.com" />
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('website')" />

                <div class="flex items-center gap-3">
                    <span class="w-28 shrink-0 text-sm text-gray-500 dark:text-gray-400">Instagram</span>
                    <x-text-input name="instagram" type="text" class="block w-full"
                        :value="old('instagram', $seller?->instagram)"
                        placeholder="@youraccount" />
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('instagram')" />

                <div class="flex items-center gap-3">
                    <span class="w-28 shrink-0 text-sm text-gray-500 dark:text-gray-400">YouTube</span>
                    <x-text-input name="youtube" type="url" class="block w-full"
                        :value="old('youtube', $seller?->youtube)"
                        placeholder="https://youtube.com/@yourchannel" />
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('youtube')" />

                <div class="flex items-center gap-3">
                    <span class="w-28 shrink-0 text-sm text-gray-500 dark:text-gray-400">Facebook</span>
                    <x-text-input name="facebook" type="url" class="block w-full"
                        :value="old('facebook', $seller?->facebook)"
                        placeholder="https://facebook.com/yourpage" />
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('facebook')" />

                <div class="flex items-center gap-3">
                    <span class="w-28 shrink-0 text-sm text-gray-500 dark:text-gray-400">MorphMarket</span>
                    <x-text-input name="morph_market" type="url" class="block w-full"
                        :value="old('morph_market', $seller?->morph_market)"
                        placeholder="https://morphmarket.com/stores/yourstore" />
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('morph_market')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Vendor Profile') }}</x-primary-button>

            @if (session('status') === 'seller-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
