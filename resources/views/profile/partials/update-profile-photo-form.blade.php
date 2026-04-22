<section
    x-data="{
        preview: '{{ $user->profile_photo_path ? $user->profile_photo_url : '' }}',
        hasPhoto: {{ $user->profile_photo_path ? 'true' : 'false' }},
        onFile(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.preview = URL.createObjectURL(file);
        }
    }"
>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Photo') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Upload a photo so others can recognise you.') }}
        </p>
    </header>

    <div class="mt-6 flex items-start gap-6">
        {{-- Avatar preview --}}
        <div class="shrink-0">
            <template x-if="preview">
                <img :src="preview" alt="{{ $user->name }}"
                    class="w-20 h-20 rounded-full object-cover ring-2 ring-orange-400 ring-offset-2 dark:ring-offset-gray-800">
            </template>
            <template x-if="!preview">
                <div class="w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-900/30 ring-2 ring-orange-300 ring-offset-2 dark:ring-offset-gray-800 flex items-center justify-center">
                    <span class="text-2xl font-bold text-orange-600 dark:text-orange-400 select-none">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>
            </template>
        </div>

        {{-- Upload form --}}
        <div class="flex-1 space-y-4">
            <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
                @csrf

                <div>
                    <label for="photo"
                        class="inline-flex items-center gap-2 cursor-pointer px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M19.479 10.092c-.212-3.951-3.473-7.092-7.479-7.092-4.005 0-7.267 3.141-7.479 7.092-2.57.463-4.521 2.706-4.521 5.408 0 3.037 2.463 5.5 5.5 5.5h13c3.037 0 5.5-2.463 5.5-5.5 0-2.702-1.951-4.945-4.521-5.408zm-7.479-1.092l4 4h-3v4h-2v-4h-3l4-4z"/>
                        </svg>
                        {{ __('Choose photo') }}
                    </label>
                    <input id="photo" name="photo" type="file" class="hidden"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        @change="onFile($event)">
                    <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                </div>

                <div class="flex items-center gap-3 mt-3">
                    <x-primary-button>{{ __('Save Photo') }}</x-primary-button>

                    @if (session('status') === 'photo-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition
                            x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Saved.') }}
                        </p>
                    @endif
                    @if (session('status') === 'photo-removed')
                        <p x-data="{ show: true }" x-show="show" x-transition
                            x-init="setTimeout(() => show = false, 2000)"
                            class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Photo removed.') }}
                        </p>
                    @endif
                </div>
            </form>

            @if ($user->profile_photo_path)
                <form method="POST" action="{{ route('profile.photo.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Remove your profile photo?')"
                        class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400 underline">
                        {{ __('Remove photo') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</section>
