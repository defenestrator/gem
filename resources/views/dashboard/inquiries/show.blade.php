<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.inquiries.index') }}" class="text-orange-500 hover:underline text-sm font-semibold">
                    ← Inquiries
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $inquiry->animal?->pet_name ?? 'Inquiry' }}
                </h2>
                <span class="inline-block px-2 py-0.5 text-xs rounded-full {{ $inquiry->status->badgeClasses() }}">
                    {{ $inquiry->status->label() }}
                </span>
            </div>
            @if ($inquiry->status !== \App\Enums\InquiryStatus::Closed)
                <form method="POST" action="{{ route('dashboard.inquiries.close', $inquiry) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-xs text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600 px-3 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        Mark Closed
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if (session('reply_sent'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                    Reply sent successfully.
                </div>
            @endif
            @if (session('inquiry_closed'))
                <div class="bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-3 rounded-lg">
                    Inquiry marked as closed.
                </div>
            @endif

            {{-- Animal card --}}
            @if ($inquiry->animal)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 flex gap-4 items-center">
                    @php $thumb = $inquiry->animal->media->first(); @endphp
                    @if ($thumb)
                        <img src="{{ $thumb->url }}" alt="{{ $inquiry->animal->pet_name }}"
                            class="h-16 w-16 rounded-lg object-cover flex-shrink-0">
                    @endif
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Animal</p>
                        <a href="{{ route('animals.show', $inquiry->animal) }}" class="text-orange-600 dark:text-orange-400 font-semibold hover:underline">
                            {{ $inquiry->animal->pet_name }}
                        </a>
                        <p class="text-xs text-gray-400">ID: {{ $inquiry->animal->slug }}</p>
                        @if ($inquiry->animal->price)
                            <p class="text-sm font-bold text-green-600 dark:text-green-400">${{ number_format($inquiry->animal->price, 2) }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Visitor contact info --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Inquiry From</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Name</p>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $inquiry->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                        <a href="mailto:{{ $inquiry->email }}" class="text-sm text-blue-500 hover:underline break-all">{{ $inquiry->email }}</a>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Phone</p>
                        <p class="text-sm text-gray-800 dark:text-gray-200">{{ $inquiry->phone ?: '—' }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Received {{ $inquiry->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>

            {{-- Conversation thread --}}
            <div class="space-y-3">

                {{-- Original message --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border-l-4 border-gray-300 dark:border-gray-600">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $inquiry->name }}</p>
                        <p class="text-xs text-gray-400">{{ $inquiry->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap text-sm">{{ $inquiry->message }}</p>
                </div>

                {{-- Replies --}}
                @foreach ($inquiry->replies as $reply)
                    <div class="bg-orange-50 dark:bg-gray-700 rounded-lg shadow-sm p-5 border-l-4 border-orange-400">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-orange-700 dark:text-orange-400">
                                {{ $reply->admin?->name ?? 'Gem Reptiles' }} <span class="font-normal text-xs text-gray-400">(admin)</span>
                            </p>
                            <p class="text-xs text-gray-400">{{ $reply->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap text-sm">{{ $reply->body }}</p>
                    </div>
                @endforeach

            </div>

            {{-- Reply form --}}
            @if ($inquiry->status !== \App\Enums\InquiryStatus::Closed)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Send Reply to {{ $inquiry->name }}</h3>
                    <form method="POST" action="{{ route('dashboard.inquiries.reply', $inquiry) }}">
                        @csrf
                        <textarea
                            name="body"
                            rows="5"
                            required
                            placeholder="Type your reply..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-orange-500 focus:border-orange-500 @error('body') border-red-500 @enderror"
                        >{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 flex items-center gap-3">
                            <button type="submit"
                                class="bg-orange-500 text-white py-2 px-6 rounded-lg hover:bg-orange-700 font-semibold text-sm">
                                Send Reply
                            </button>
                            <p class="text-xs text-gray-400">Reply will be emailed to {{ $inquiry->email }}</p>
                        </div>
                    </form>
                </div>
            @else
                <div class="text-center py-4 text-sm text-gray-400">This inquiry is closed. Reopen by setting a new status.</div>
            @endif

        </div>
    </div>
</x-app-layout>
