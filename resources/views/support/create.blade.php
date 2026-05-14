<x-app-layout>
@section('title', 'Support')
    @push('meta')
    <meta name="description" content="Report a bug or suggest an improvement for Gem Reptiles.">
    @endpush
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Support</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 rounded-lg px-5 py-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Get in touch</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Found a bug or have a feature idea? Submit a ticket and we'll follow up.
                </p>

                <form method="POST" action="{{ route('support.store') }}"
                      onsubmit="return submitWithTurnstile(this)">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name', auth()->user()?->name) }}"
                               required autocomplete="name"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email address</label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email', auth()->user()?->email) }}"
                               required autocomplete="email"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="radio" name="type" value="bug"
                                       {{ old('type', 'bug') === 'bug' ? 'checked' : '' }}
                                       class="text-orange-500 focus:ring-orange-400">
                                Bug report
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="radio" name="type" value="suggestion"
                                       {{ old('type') === 'suggestion' ? 'checked' : '' }}
                                       class="text-orange-500 focus:ring-orange-400">
                                Feature suggestion
                            </label>
                        </div>
                        @error('type')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div class="mb-6">
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                        <textarea id="message" name="message" rows="5" required minlength="8" maxlength="5000"
                                  placeholder="Describe the bug or your idea…"
                                  class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-turnstile />

                    <button type="submit"
                            class="w-full bg-orange-500 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition text-sm">
                        Submit ticket
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
