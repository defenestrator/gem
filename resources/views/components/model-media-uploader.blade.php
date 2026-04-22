@props([
    'name'   => 'images',
    'accept' => 'image/jpeg,image/png,image/gif,image/webp',
])

<div
    x-data="{
        files: [],
        isDragging: false,
        addFiles(list) {
            Array.from(list).forEach(file => {
                if (!this.files.find(f => f.name === file.name && f.size === file.size)) {
                    this.files.push(file);
                }
            });
            this.$refs.fileInput.value = '';
            this.syncInput();
        },
        removeFile(index) {
            this.files.splice(index, 1);
            this.syncInput();
        },
        syncInput() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        },
        previewUrl(file) {
            return URL.createObjectURL(file);
        },
        formatSize(bytes) {
            if (bytes > 1048576) return Math.round(bytes / 1048576) + ' MB';
            if (bytes > 1024)    return Math.round(bytes / 1024)    + ' KB';
            return bytes + ' B';
        }
    }"
    @dragover.prevent="isDragging = true"
    @dragleave.prevent="isDragging = false"
    @drop.prevent="isDragging = false; addFiles($event.dataTransfer.files)"
>
    <input
        x-ref="fileInput"
        type="file"
        name="{{ $name }}[]"
        multiple
        accept="{{ $accept }}"
        class="hidden"
        @change="addFiles($event.target.files)"
    >

    {{-- Drop zone --}}
    <div
        class="border-2 border-dashed rounded-lg py-10 flex flex-col items-center justify-center text-center cursor-pointer transition-colors"
        :class="isDragging
            ? 'border-orange-400 bg-orange-50 dark:bg-orange-900/20'
            : 'border-gray-300 dark:border-gray-600 hover:border-orange-400'"
        @click="$refs.fileInput.click()"
    >
        <svg class="w-10 h-10 mb-3 fill-current transition-colors"
            :class="isDragging ? 'text-orange-400' : 'text-gray-400'"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M19.479 10.092c-.212-3.951-3.473-7.092-7.479-7.092-4.005 0-7.267 3.141-7.479 7.092-2.57.463-4.521 2.706-4.521 5.408 0 3.037 2.463 5.5 5.5 5.5h13c3.037 0 5.5-2.463 5.5-5.5 0-2.702-1.951-4.945-4.521-5.408zm-7.479-1.092l4 4h-3v4h-2v-4h-3l4-4z"/>
        </svg>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Drag &amp; drop images here, or
            <span class="text-orange-500 font-semibold">browse</span>
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">PNG, JPG, GIF, WEBP</p>
    </div>

    {{-- New file previews --}}
    <div x-show="files.length > 0" x-cloak class="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
        <template x-for="(file, index) in files" :key="index">
            <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shadow-sm">
                <img :src="previewUrl(file)" :alt="file.name" class="w-full h-full object-cover">
                <div class="absolute inset-0 flex flex-col justify-between p-1.5 opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 rounded-lg">
                    <button
                        type="button"
                        @click.stop="removeFile(index)"
                        class="self-end text-white hover:text-red-300 focus:outline-none"
                        title="Remove"
                    >
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M3 6l3 18h12l3-18h-18zm19-4v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.316c0 .901.73 2 1.631 2h5.711z"/>
                        </svg>
                    </button>
                    <p class="text-xs text-white truncate" x-text="formatSize(file.size)"></p>
                </div>
            </div>
        </template>
    </div>
</div>
