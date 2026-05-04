<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if(auth()->user()?->is_admin)
                    <x-nav-link :href="route('dashboard.inquiries.index')" :active="request()->routeIs('dashboard.inquiries.*')">
                        <span class="relative inline-flex items-center gap-1">
                            {{ __('Inquiries') }}
                            @php $newInquiryCount = \App\Models\Inquiry::where('status','new')->count(); @endphp
                            @if($newInquiryCount)
                                <span class="bg-orange-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $newInquiryCount }}</span>
                            @endif
                        </span>
                    </x-nav-link>
                    <x-nav-link :href="route('dashboard.species.media.index')" :active="request()->routeIs('dashboard.species.*')">
                        <span class="relative inline-flex items-center gap-1">
                            {{ __('Photos') }}
                            @php $pendingPhotoCount = \App\Models\Media::where('mediable_type', \App\Models\Species::class)->where('moderation_status', 'pending')->count(); @endphp
                            @if($pendingPhotoCount)
                                <span class="bg-orange-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $pendingPhotoCount }}</span>
                            @endif
                        </span>
                    </x-nav-link>
                    @endif
                    <x-nav-link :href="route('animals.index')" :active="request()->routeIs('animals.*')">
                        {{ __('Animals') }}
                    </x-nav-link>
                    @if(config('features.classifieds'))
                    <x-nav-link :href="route('classifieds.index')" :active="request()->routeIs('classifieds.*')">
                        {{ __('Classifieds') }}
                    </x-nav-link>
                    @endif
                    <x-nav-link :href="route('sellers.index')" :active="request()->routeIs('sellers.*')">
                        {{ __('Sellers') }}
                    </x-nav-link>
                    <x-nav-link :href="route('species.index')" :active="request()->routeIs('species.*')">
                        {{ __('Species') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 gap-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition ease-in-out duration-150">
                        {{ __('Log Out') }}
                    </button>
                </form>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(auth()->user()?->is_admin)
            <x-responsive-nav-link :href="route('dashboard.inquiries.index')" :active="request()->routeIs('dashboard.inquiries.*')">
                {{ __('Inquiries') }}
                @if($newInquiryCount ?? \App\Models\Inquiry::where('status','new')->count())
                    <span class="ml-1 bg-orange-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">{{ \App\Models\Inquiry::where('status','new')->count() }}</span>
                @endif
            </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('animals.index')" :active="request()->routeIs('animals.*')">
                {{ __('Animals') }}
            </x-responsive-nav-link>
            @if(config('features.classifieds'))
            <x-responsive-nav-link :href="route('classifieds.index')" :active="request()->routeIs('classifieds.*')">
                {{ __('Classifieds') }}
            </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('sellers.index')" :active="request()->routeIs('sellers.*')">
                {{ __('Sellers') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('species.index')" :active="request()->routeIs('species.*')">
                {{ __('Species') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
