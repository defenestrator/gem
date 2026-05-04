<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('species.index') }}" class="text-gray-400 hover:text-orange-500 transition text-sm">← Species</a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight italic">
                {{ $species->species }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Species info card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold italic text-gray-900 dark:text-gray-100 mb-1">
                            {{ $species->species }}
                        </h1>
                        @if ($species->common_name)
                            <p class="text-lg text-gray-500 dark:text-gray-400">{{ $species->common_name }}</p>
                        @endif
                    </div>
                    @if ($species->getRawOriginal('type_species'))
                        <div class="flex gap-1 flex-wrap">
                            @foreach (explode(' ', $species->getRawOriginal('type_species')) as $token)
                                <span title="{{ \App\Enums\SpeciesType::tryFrom($token)?->label() ?? $token }}"
                                      class="px-2 py-1 rounded text-xs font-bold bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300 uppercase">
                                    {{ $token }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @if ($species->author)
                        <div>
                            <dt class="font-semibold text-gray-500 dark:text-gray-400">Author</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $species->author }}</dd>
                        </div>
                    @endif
                    @if ($species->higher_taxa)
                        <div>
                            <dt class="font-semibold text-gray-500 dark:text-gray-400">Classification</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $species->higher_taxa }}</dd>
                        </div>
                    @endif
                    @if ($species->species_number)
                        <div>
                            <dt class="font-semibold text-gray-500 dark:text-gray-400">Species #</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $species->species_number }}</dd>
                        </div>
                    @endif
                    @if ($species->changes)
                        <div>
                            <dt class="font-semibold text-gray-500 dark:text-gray-400">Changes</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $species->changes }}</dd>
                        </div>
                    @endif
                    @if ($species->subspecies)
                        <div class="sm:col-span-2">
                            <dt class="font-semibold text-gray-500 dark:text-gray-400">Subspecies</dt>
                            <dd class="text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $species->subspecies }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Photo gallery --}}
            @if ($media->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
                     x-data="{ active: '{{ $media->where('moderation_status', 'approved')->first()?->url ?? $media->first()->url }}' }">

                    <div class="w-full aspect-video bg-black overflow-hidden">
                        <img :src="active" alt="{{ $species->species }}"
                             class="w-full h-full object-contain">
                    </div>

                    <div class="flex gap-2 p-3 overflow-x-auto bg-gray-50 dark:bg-gray-900 flex-wrap">
                        @foreach ($media as $photo)
                            <div class="relative flex-shrink-0">
                                <button type="button"
                                    @click="active = '{{ $photo->url }}'"
                                    :class="active === '{{ $photo->url }}' ? 'ring-2 ring-orange-500' : 'opacity-70 hover:opacity-100'"
                                    class="rounded overflow-hidden transition block">
                                    <img src="{{ $photo->url }}" alt="{{ $species->species }}"
                                         class="h-16 w-16 object-cover">
                                </button>
                                @if ($isAdmin && $photo->moderation_status !== 'approved')
                                    <span class="absolute -top-1 -right-1 text-[10px] font-bold px-1 rounded
                                        {{ $photo->moderation_status === 'pending' ? 'bg-yellow-400 text-yellow-900' : 'bg-red-500 text-white' }}">
                                        {{ strtoupper($photo->moderation_status) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 text-center text-gray-400 dark:text-gray-500">
                    <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm">No photos yet.</p>
                </div>
            @endif

            {{-- Flash message --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Upload form (auth only; admin uploads skip moderation) --}}
            @auth
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">Submit a photo</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Photos are reviewed before appearing on this page.</p>

                    <form method="POST" action="{{ route('species.media.store', $species) }}" enctype="multipart/form-data">
                        @csrf
                        <x-model-media-uploader name="images" />
                        @error('images.*')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="mt-4">
                            <button type="submit"
                                class="bg-orange-500 hover:bg-orange-700 text-white font-semibold py-2 px-5 rounded-lg transition text-sm">
                                Submit for review
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5 text-sm text-gray-500 dark:text-gray-400 text-center">
                    <a href="{{ route('login') }}" class="text-orange-500 hover:underline font-medium">Log in</a> to submit photos.
                </div>
            @endauth

        </div>
    </div>
</x-app-layout>
