<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 flex-wrap">
            @php
                $isSpecies    = $mediable instanceof \App\Models\Species;
                $isSubspecies = $mediable instanceof \App\Models\Subspecies;
                $backRoute    = $isSpecies
                    ? route('species.show', $mediable)
                    : ($isSubspecies ? route('subspecies.show', $mediable) : null);
                $backLabel    = $isSpecies
                    ? $mediable->species
                    : ($isSubspecies ? $mediable->full_name : 'Back');
            @endphp
            @if ($backRoute)
                <a href="{{ $backRoute }}" class="text-gray-400 hover:text-orange-500 transition text-sm">← {{ $backLabel }}</a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
            @endif
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Image Attribution
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Image --}}
            <div class="bg-black rounded-lg overflow-hidden shadow-md">
                <img src="{{ $media->url }}" alt="{{ $media->title ?? 'Species photo' }}"
                     class="w-full max-h-[60vh] object-contain mx-auto">
            </div>

            {{-- Attribution card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md divide-y divide-gray-100 dark:divide-gray-700">

                @if ($media->title)
                    <div class="px-6 py-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">Title</dt>
                        <dd class="text-gray-800 dark:text-gray-200 text-sm">{{ $media->title }}</dd>
                    </div>
                @endif

                @if ($media->author)
                    <div class="px-6 py-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">Author / Creator</dt>
                        <dd class="text-gray-800 dark:text-gray-200 text-sm">{{ $media->author }}</dd>
                    </div>
                @endif

                @if ($media->license)
                    <div class="px-6 py-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">License</dt>
                        <dd class="text-sm">
                            @if ($media->license_url)
                                <a href="{{ $media->license_url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-orange-500 hover:underline">{{ $media->license }}</a>
                            @else
                                <span class="text-gray-800 dark:text-gray-200">{{ $media->license }}</span>
                            @endif
                        </dd>
                    </div>
                @endif

                @if ($media->source_url)
                    <div class="px-6 py-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">Source</dt>
                        <dd class="text-sm">
                            <a href="{{ $media->source_url }}" target="_blank" rel="noopener noreferrer"
                               class="text-orange-500 hover:underline break-all">{{ $media->source_url }}</a>
                        </dd>
                    </div>
                @endif

                @if ($media->copyright)
                    <div class="px-6 py-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">Copyright</dt>
                        <dd class="text-gray-800 dark:text-gray-200 text-sm">{{ $media->copyright }}</dd>
                    </div>
                @endif

                {{-- Subject --}}
                <div class="px-6 py-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">Subject</dt>
                    <dd class="text-sm">
                        @if ($backRoute)
                            <a href="{{ $backRoute }}" class="italic text-orange-600 dark:text-orange-400 hover:underline">
                                {{ $backLabel }}
                            </a>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">Unknown</span>
                        @endif
                    </dd>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
