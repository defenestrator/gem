<div
    x-data="{ featured: @entangle('featuredId') }"
    class="flex gap-2 px-3 pb-2 overflow-x-auto bg-gray-100 dark:bg-gray-900">
    @foreach ($media as $item)
        <div class="flex flex-col items-center flex-shrink-0" style="width: 64px">
            <button
                type="button"
                wire:click="setFeatured({{ $item->id }})"
                x-on:click="featured = {{ $item->id }}"
                class="transition-colors focus:outline-none"
                title="{{ $featuredId === $item->id ? 'Featured image' : 'Set as featured image' }}"
                aria-label="{{ $featuredId === $item->id ? 'Featured image' : 'Set as featured image' }}"
            >
                <svg x-show="featured === {{ $item->id }}"
                     class="w-5 h-5 text-orange-500 fill-current" viewBox="0 0 24 24">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                <svg x-show="featured !== {{ $item->id }}"
                     class="w-5 h-5 text-gray-400 hover:text-orange-400 transition-colors" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
            </button>
        </div>
    @endforeach
</div>
