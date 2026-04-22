<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $classified->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Status Badge -->
                <div class="mb-6">
                    <span class="inline-block px-3 py-1 text-sm rounded-full
                        @if ($classified->status === 'published')
                            bg-green-100 text-green-800
                        @elseif ($classified->status === 'draft')
                            bg-gray-100 text-gray-800
                        @else
                            bg-red-100 text-red-800
                        @endif
                    ">
                        {{ ucfirst($classified->status) }}
                    </span>
                </div>

                <!-- Photo Gallery -->
                @if ($classified->media->isNotEmpty())
                    @php $photos = $classified->media; @endphp
                    <div class="mb-6" x-data="{ active: '{{ $photos->first()->url }}' }">
                        <div class="w-full bg-black rounded-lg overflow-hidden" style="max-height:480px;">
                            <img :src="active" alt="{{ $classified->title }}"
                                class="w-full object-contain mx-auto" style="max-height:480px;">
                        </div>
                        @if ($photos->count() > 1)
                            <div class="flex gap-2 mt-2 overflow-x-auto">
                                @foreach ($photos as $photo)
                                    <button type="button"
                                        @click="active = '{{ $photo->url }}'"
                                        :class="active === '{{ $photo->url }}' ? 'ring-2 ring-orange-500' : 'opacity-70 hover:opacity-100'"
                                        class="flex-shrink-0 rounded overflow-hidden transition">
                                        <img src="{{ $photo->url }}" alt="{{ $classified->title }}"
                                            class="h-16 w-16 object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Title, Price, and Inquire -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-2">
                        {{ $classified->title }}
                    </h1>
                    <div class="flex items-center gap-6">
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                            ${{ number_format($classified->price, 2) }}
                        </p>
                        @if ($classified->status === 'published')
                            @if (session('inquiry_sent'))
                                <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 px-4 py-2 rounded-lg font-semibold">
                                    Inquiry sent — we'll be in touch!
                                </span>
                            @else
                                <a href="{{ route('classifieds.inquiries.create', $classified) }}"
                                    class="bg-orange-500 hover:bg-orange-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                                    Inquire
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Seller Info -->
                @if ($classified->user)
                    <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300">
                            <span class="font-semibold">Seller:</span> {{ $classified->user->name }}
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            <span class="font-semibold">Posted:</span> {{ $classified->created_at->format('M d, Y') }}
                        </p>
                    </div>
                @endif

                <!-- Description -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Description</h3>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        {{ $classified->description }}
                    </p>
                </div>

                <!-- Back Link -->
                <div>
                    <a href="{{ route('classifieds.index') }}" class="text-orange-600 dark:text-orange-400 hover:underline font-semibold">
                        ← Back to All Classifieds
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
