<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('species.index') }}" class="text-gray-400 hover:text-orange-500 transition text-sm">← Species</a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <a href="{{ route('species.show', $subspecies->parentSpecies) }}"
               class="text-gray-400 hover:text-orange-500 transition text-sm italic">
                {{ $subspecies->parentSpecies->species }}
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight italic">
                {{ $subspecies->full_name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Taxonomy card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h1 class="text-3xl font-bold italic text-gray-900 dark:text-gray-100 mb-1">
                    {{ $subspecies->full_name }}
                </h1>

                <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-semibold text-gray-500 dark:text-gray-400">Genus</dt>
                        <dd class="text-gray-800 dark:text-gray-200 italic">{{ $subspecies->genus }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-500 dark:text-gray-400">Species</dt>
                        <dd class="text-gray-800 dark:text-gray-200 italic">{{ $subspecies->species }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-500 dark:text-gray-400">Subspecies Epithet</dt>
                        <dd class="text-gray-800 dark:text-gray-200 italic">{{ $subspecies->subspecies }}</dd>
                    </div>
                    @if ($subspecies->author)
                        <div>
                            <dt class="font-semibold text-gray-500 dark:text-gray-400">Author</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $subspecies->author }}</dd>
                        </div>
                    @endif
                    <div class="sm:col-span-2">
                        <dt class="font-semibold text-gray-500 dark:text-gray-400">Parent Species</dt>
                        <dd>
                            <a href="{{ route('species.show', $subspecies->parentSpecies) }}"
                               class="text-orange-600 dark:text-orange-400 hover:underline italic text-sm">
                                {{ $subspecies->parentSpecies->species }}
                                @if ($subspecies->parentSpecies->common_name)
                                    <span class="not-italic text-gray-500 dark:text-gray-400">({{ $subspecies->parentSpecies->common_name }})</span>
                                @endif
                            </a>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Photo gallery --}}
            @if ($media->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
                     x-data="{ active: '{{ $media->where('moderation_status', 'approved')->first()?->url ?? $media->first()->url }}' }">

                    <div class="w-full aspect-video bg-black overflow-hidden">
                        <img :src="active" alt="{{ $subspecies->full_name }}"
                             class="w-full h-full object-contain">
                    </div>

                    <div class="flex gap-2 p-3 overflow-x-auto bg-gray-50 dark:bg-gray-900 flex-wrap">
                        @foreach ($media as $photo)
                            <div class="relative flex-shrink-0 flex flex-col items-center gap-1">
                                <button type="button"
                                    @click="active = '{{ $photo->url }}'"
                                    :class="active === '{{ $photo->url }}' ? 'ring-2 ring-orange-500' : 'opacity-70 hover:opacity-100'"
                                    class="rounded overflow-hidden transition block">
                                    <img src="{{ $photo->url }}" alt="{{ $subspecies->full_name }}"
                                         class="h-16 w-16 object-cover">
                                </button>
                                @if ($isAdmin && $photo->moderation_status !== 'approved')
                                    <span class="absolute -top-1 -right-1 text-[10px] font-bold px-1 rounded
                                        {{ $photo->moderation_status === 'pending' ? 'bg-yellow-400 text-yellow-900' : 'bg-red-500 text-white' }}">
                                        {{ strtoupper($photo->moderation_status) }}
                                    </span>
                                @endif
                                @if ($photo->source_url || $photo->author || $photo->license)
                                    <a href="{{ route('media.attribution', $photo) }}"
                                       class="text-[10px] text-gray-400 hover:text-orange-500 transition leading-none"
                                       title="View attribution">© attr</a>
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

                    <form method="POST" action="{{ route('subspecies.media.store', $subspecies) }}" enctype="multipart/form-data">
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
