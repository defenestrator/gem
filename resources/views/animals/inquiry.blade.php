<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Inquire about {{ $animal->pet_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    {{-- Animal summary --}}
                    <div class="flex gap-4 mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        @php $thumb = $animal->media->first(); @endphp
                        @if ($thumb)
                            <img src="{{ $thumb->url }}" alt="{{ $animal->pet_name }}"
                                class="h-20 w-20 rounded-lg object-cover flex-shrink-0">
                        @endif
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ $animal->pet_name }}</h3>
                            @if ($animal->price)
                                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">${{ number_format($animal->price, 2) }}</p>
                            @endif
                            @if ($animal->category)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $animal->category }}</p>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('animals.inquiries.store', $animal) }}"
                          @submit.prevent="submitWithTurnstile($el)">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Name</label>
                            <input type="text" id="name" name="name"
                                value="{{ old('name', auth()->user()?->name) }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-orange-500 focus:border-orange-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', auth()->user()?->email) }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-orange-500 focus:border-orange-500 @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone <span class="text-gray-400">(optional)</span></label>
                            <input type="tel" id="phone" name="phone"
                                value="{{ old('phone') }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-orange-500 focus:border-orange-500 @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                            <textarea id="message" name="message" rows="5" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-orange-500 focus:border-orange-500 @error('message') border-red-500 @enderror"
                                placeholder="I'm interested in this animal. Is it still available?">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(config('features.easyship'))
                        <x-shipping-quote />
                        @endif

                        <x-turnstile />

                        <div class="flex gap-4 items-center mt-6">
                            <button type="submit"
                                class="bg-orange-500 text-white py-2 px-6 rounded-lg hover:bg-orange-700 font-semibold">
                                Send Inquiry
                            </button>
                            <a href="{{ route('animals.show', $animal) }}"
                                class="text-gray-600 dark:text-gray-400 hover:underline">
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
