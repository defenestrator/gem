<x-app-layout>
@section('title', $conversation->subject . ' — Conversation')
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('dashboard.conversations.index') }}"
                    class="text-orange-500 hover:underline text-sm font-semibold shrink-0">
                    ← Conversations
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight truncate">
                    {{ $conversation->subject }}
                </h2>
                <span class="inline-block px-2 py-0.5 text-xs rounded-full shrink-0 {{ $conversation->statusBadgeClasses() }}">
                    {{ $conversation->statusLabel() }}
                </span>
            </div>

            {{-- Status actions --}}
            <div class="flex items-center gap-2 shrink-0" x-data="{ open: false }">
                <button @click="open = !open"
                    class="text-xs text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600 px-3 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    Set Status ▾
                </button>
                <div x-show="open" @click.outside="open = false"
                    class="absolute right-6 mt-24 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-10 min-w-[140px]">
                    @foreach (['open' => 'Open', 'closed' => 'Closed', 'spam' => 'Spam'] as $s => $label)
                        <form method="POST" action="{{ route('dashboard.conversations.status', $conversation) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $s }}">
                            <button type="submit"
                                class="block w-full text-left px-4 py-2 text-sm
                                    {{ $conversation->status === $s ? 'font-semibold text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300' }}
                                    hover:bg-gray-50 dark:hover:bg-gray-700">
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if (session('reply_sent'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                    Reply sent.
                </div>
            @endif
            @if (session('status_updated'))
                <div class="bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-3 rounded-lg">
                    Status updated to <strong>{{ session('status_updated') }}</strong>.
                </div>
            @endif

            {{-- Contact card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Contact</p>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $conversation->contact_name ?: '(no name)' }}
                        </p>
                        <a href="mailto:{{ $conversation->contact_email }}"
                            class="text-sm text-blue-500 hover:underline">
                            {{ $conversation->contact_email }}
                        </a>
                    </div>
                    <p class="text-xs text-gray-400 shrink-0">
                        Started {{ $conversation->created_at->format('M j, Y') }}
                    </p>
                </div>
            </div>

            {{-- Message thread --}}
            <div class="space-y-3">
                @foreach ($conversation->messages as $message)
                    @if ($message->isInbound())
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border-l-4 border-gray-300 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $message->from_name ?: $message->from_email }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                            @if ($message->body_text)
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $message->body_text }}</p>
                            @elseif ($message->body_html)
                                <div class="text-sm text-gray-700 dark:text-gray-300 prose dark:prose-invert max-w-none">
                                    {!! $message->body_html !!}
                                </div>
                            @else
                                <p class="text-sm text-gray-400 italic">(no body)</p>
                            @endif
                        </div>
                    @else
                        <div class="bg-orange-50 dark:bg-gray-700 rounded-lg shadow-sm p-5 border-l-4 border-orange-400">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold text-orange-700 dark:text-orange-400">
                                    {{ $message->from_name ?: config('app.name') }}
                                    <span class="font-normal text-xs text-gray-400">(admin)</span>
                                </p>
                                <p class="text-xs text-gray-400">{{ $message->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $message->body_text }}</p>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Reply form --}}
            @if ($conversation->status !== 'spam')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        Reply to {{ $conversation->contact_name ?: $conversation->contact_email }}
                    </h3>
                    <form method="POST" action="{{ route('dashboard.conversations.reply', $conversation) }}">
                        @csrf
                        <textarea
                            name="body"
                            rows="5"
                            required
                            placeholder="Type your reply…"
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
                            <p class="text-xs text-gray-400">
                                Emailed to {{ $conversation->contact_email }}
                            </p>
                        </div>
                    </form>
                </div>
            @else
                <div class="text-center py-4 text-sm text-gray-400">
                    Conversation marked as spam. Change status to reply.
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
