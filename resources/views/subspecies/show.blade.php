<x-app-layout>
    @push('meta')
    @php
        $parentCommon = $subspecies->parentSpecies->common_name ? " ({$subspecies->parentSpecies->common_name} subspecies)" : ' subspecies';
        $metaDesc = $subspecies->description
            ? $subspecies->full_name . $parentCommon . ' — ' . \Illuminate\Support\Str::limit(strip_tags($subspecies->description), 120)
            : $subspecies->full_name . $parentCommon . ' — taxonomy, classification, and photos.';
    @endphp
    <meta name="description" content="{{ $metaDesc }}">
    @endpush
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
                <div class="flex flex-wrap items-start justify-between gap-4">
                <h1 class="text-3xl font-bold italic text-gray-900 dark:text-gray-100 mb-1">
                    {{ $subspecies->full_name }}
                </h1>
                @if ($isAdmin)
                    <a href="{{ route('dashboard.subspecies.edit', $subspecies) }}"
                       class="shrink-0 text-xs font-semibold bg-gray-100 dark:bg-gray-700 hover:bg-orange-100 dark:hover:bg-orange-900/40 text-gray-700 dark:text-gray-200 hover:text-orange-700 dark:hover:text-orange-300 px-3 py-1.5 rounded-lg transition">
                        Edit Subspecies
                    </a>
                @endif
                </div>

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
                @php
                $mediaData = $media->map(fn($m) => [
                    'id'          => $m->id,
                    'url'         => $m->url,
                    'author'      => $m->author,
                    'license'     => $m->license,
                    'license_url' => $m->license_url,
                    'source_url'  => $m->source_url,
                    'title'       => $m->title,
                    'attr_url'    => ($m->source_url || $m->author || $m->license) ? route('media.attribution', $m) : null,
                    'status'      => $m->moderation_status,
                ])->values()->all();
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden"
                     x-data="{
                         photos: {{ Js::from($mediaData) }},
                         activeMedia: null,
                         init() {
                             this.activeMedia = this.photos.find(p => p.status === 'approved') ?? this.photos[0];
                         }
                     }">

                    <div class="w-full aspect-video bg-black overflow-hidden">
                        <img :src="activeMedia?.url" alt="{{ $subspecies->full_name }}"
                             class="w-full h-full object-contain">
                    </div>

                    <div class="flex gap-2 p-3 overflow-x-auto bg-gray-50 dark:bg-gray-900 flex-wrap">
                        @foreach ($media as $photo)
                            <div class="relative flex-shrink-0 group">
                                <button type="button"
                                    @click="activeMedia = photos.find(p => p.id === {{ $photo->id }})"
                                    :class="activeMedia?.id === {{ $photo->id }} ? 'ring-2 ring-orange-500' : 'opacity-70 hover:opacity-100'"
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
                                @if ($isAdmin)
                                    <form method="POST"
                                          action="{{ route('dashboard.subspecies.media.detach', [$subspecies, $photo]) }}"
                                          class="absolute bottom-0 inset-x-0 hidden group-hover:flex justify-center bg-black/50 py-0.5"
                                          onsubmit="return confirm('Detach this photo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[10px] text-white font-bold hover:text-red-300 transition">
                                            Detach
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Reactive attribution for active image --}}
                    <template x-if="activeMedia && (activeMedia.author || activeMedia.license || activeMedia.source_url || activeMedia.title)">
                        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                            <template x-if="activeMedia.title">
                                <p x-text="activeMedia.title" class="italic"></p>
                            </template>
                            <div class="flex flex-wrap gap-x-4 gap-y-1">
                                <template x-if="activeMedia.author">
                                    <span>© <span x-text="activeMedia.author"></span></span>
                                </template>
                                <template x-if="activeMedia.license">
                                    <span>
                                        <template x-if="activeMedia.license_url">
                                            <a :href="activeMedia.license_url" target="_blank" rel="noopener noreferrer"
                                               x-text="activeMedia.license"
                                               class="text-orange-500 hover:underline"></a>
                                        </template>
                                        <template x-if="!activeMedia.license_url">
                                            <span x-text="activeMedia.license"></span>
                                        </template>
                                    </span>
                                </template>
                                <template x-if="activeMedia.source_url">
                                    <a :href="activeMedia.source_url" target="_blank" rel="noopener noreferrer"
                                       class="text-orange-500 hover:underline">Source</a>
                                </template>
                                <template x-if="activeMedia.attr_url">
                                    <a :href="activeMedia.attr_url" class="text-orange-500 hover:underline">Full attribution</a>
                                </template>
                            </div>
                        </div>
                    </template>
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

            {{-- Biography --}}
            @if ($subspecies->description)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Subspecies Profile</h2>
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! Str::markdown(e($subspecies->description)) !!}
                    </div>
                </div>
            @endif

            {{-- Description submission --}}
            @auth
                @unless ($isAdmin)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            {{ $subspecies->description ? 'Suggest a description update' : 'Submit a description' }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Submissions are reviewed before publication. Markdown supported.
                        </p>
                        <form method="POST" action="{{ route('subspecies.submissions.store', $subspecies) }}">
                            @csrf
                            <textarea name="proposed_value" rows="8" required minlength="50"
                                placeholder="Write a subspecies description in Markdown..."
                                class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 text-sm font-mono">{{ old('proposed_value') }}</textarea>
                            @error('proposed_value')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <div class="mt-4">
                                <button type="submit"
                                    class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-5 rounded-lg transition text-sm">
                                    Submit for review
                                </button>
                            </div>
                        </form>
                    </div>
                @endunless
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5 text-sm text-gray-500 dark:text-gray-400 text-center">
                    <a href="{{ route('login') }}" class="text-orange-500 hover:underline font-medium">Log in</a>
                    to suggest a description update.
                </div>
            @endauth

        </div>
    </div>
</x-app-layout>
