<footer class="bg-gray-800 dark:bg-gray-950 text-gray-300 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">

            {{-- Brand --}}
            <div>
                <a href="{{ route('welcome') }}" class="flex items-center gap-2 mb-3">
                    <x-application-logo style="width:2em; filter:brightness(0) invert(1);" />
                    <span class="text-white font-semibold text-sm">{{ config('app.name') }}</span>
                </a>
                <p class="text-xs text-gray-400 leading-relaxed">
                    &copy; {{ date('Y') }} Gem Reptiles. All rights reserved.
                </p>
                <p class="text-[10px] text-gray-300 leading-relaxed mt-1">
                    Original content, images, video &amp; logos are protected by copyright, except where otherwise attributed.
                </p>
            </div>

            {{-- Legal --}}
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Legal</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('legal.privacy') }}" class="hover:text-white transition-colors">Privacy Policy</a>
                    </li>
                    <li>
                        <a href="{{ route('legal.terms') }}" class="hover:text-white transition-colors">Terms of Service</a>
                    </li>
                </ul>
            </div>

            {{-- Account --}}
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Account</h3>
                <ul class="space-y-2 text-sm">
                    @auth
                        <li>
                            <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="hover:text-white transition-colors">Log Out</button>
                            </form>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" class="hover:text-white transition-colors">Log In</a>
                        </li>
                        @if (Route::has('register'))
                        <li>
                            <a href="{{ route('register') }}" class="hover:text-white transition-colors">Create Account</a>
                        </li>
                        @endif
                    @endauth
                </ul>
            </div>

        </div>
    </div>
</footer>
