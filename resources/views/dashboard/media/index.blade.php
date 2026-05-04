<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Photo Moderation
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($pending->isEmpty())
                <div class="bg-white dark:bg-gray-800 p-10 rounded-lg shadow-md text-center text-gray-400 dark:text-gray-500">
                    <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm">No photos pending review.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($pending as $photo)
                        @php
                            $mediable  = $photo->mediable;
                            $typeLabel = match ($photo->mediable_type) {
                                \App\Models\Species::class    => 'Species',
                                \App\Models\Subspecies::class => 'Subspecies',
                                default                       => class_basename($photo->mediable_type),
                            };
                            $detailUrl = match ($photo->mediable_type) {
                                \App\Models\Species::class    => $mediable ? route('species.show', $mediable) : null,
                                \App\Models\Subspecies::class => $mediable ? route('subspecies.show', $mediable) : null,
                                default                       => null,
                            };
                            $displayName = match ($photo->mediable_type) {
                                \App\Models\Species::class    => $mediable?->species,
                                \App\Models\Subspecies::class => $mediable?->full_name,
                                default                       => $mediable?->id,
                            };
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden flex flex-col">

                            <div class="aspect-square bg-black overflow-hidden">
                                <img src="{{ $photo->url }}"
                                     alt="{{ $displayName ?? 'Photo' }}"
                                     class="w-full h-full object-contain">
                            </div>

                            <div class="p-3 flex-1 flex flex-col gap-1">
                                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    {{ $typeLabel }}
                                </span>

                                @if ($detailUrl && $displayName)
                                    <a href="{{ $detailUrl }}"
                                       class="text-sm font-semibold italic text-orange-600 dark:text-orange-400 hover:underline leading-tight">
                                        {{ $displayName }}
                                    </a>
                                @endif

                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-auto pt-1">
                                    Submitted {{ $photo->created_at->diffForHumans() }}
                                </p>

                                <div class="flex gap-2 mt-1">
                                    <form method="POST" action="{{ route('dashboard.media.approve', $photo) }}" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-1.5 px-3 rounded transition">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.media.reject', $photo) }}" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full bg-red-500 hover:bg-red-600 text-white text-xs font-semibold py-1.5 px-3 rounded transition">
                                            Reject
                                        </button>
                                    </form>
                                </div>

                                <form method="POST" action="{{ route('dashboard.media.destroy', $photo) }}" class="mt-1"
                                      onsubmit="return confirm('Permanently delete this photo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full text-xs text-gray-400 hover:text-red-600 dark:hover:text-red-400 py-1 transition">
                                        Delete permanently
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $pending->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>
