<x-app-layout>
@section('title', 'Conversations')
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Conversations
            </h2>
            @if ($status === 'open')
                <span class="text-sm text-gray-400 dark:text-gray-500">{{ $conversations->total() }} open</span>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Flash --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Status tabs --}}
            <div class="flex gap-2 flex-wrap">
                @foreach (['open' => 'Open', 'closed' => 'Closed', 'spam' => 'Spam', 'all' => 'All'] as $tab => $label)
                    <a href="{{ route('dashboard.conversations.index', ['status' => $tab]) }}"
                        class="px-4 py-1.5 rounded-full text-sm font-semibold transition
                            {{ $status === $tab
                                ? 'bg-orange-500 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if ($conversations->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-10 text-center text-gray-400 dark:text-gray-500">
                    No {{ $status === 'all' ? '' : $status . ' ' }}conversations.
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">Subject</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden lg:table-cell">Messages</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden lg:table-cell">Last Activity</th>
                                <th class="px-4 py-3 w-px"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($conversations as $conversation)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 text-xs rounded-full {{ $conversation->statusBadgeClasses() }}">
                                            {{ $conversation->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 max-w-[160px]">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                                            {{ $conversation->contact_name ?: '(unnamed)' }}
                                        </p>
                                        <a href="mailto:{{ $conversation->contact_email }}"
                                            class="text-xs text-blue-500 hover:underline truncate block">
                                            {{ $conversation->contact_email }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 max-w-xs hidden md:table-cell">
                                        <p class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $conversation->subject }}</p>
                                    </td>
                                    <td class="px-4 py-3 hidden lg:table-cell whitespace-nowrap">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $conversation->messages_count }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 hidden lg:table-cell whitespace-nowrap">
                                        <p class="text-xs text-gray-400">{{ $conversation->last_message_at?->format('M j, Y') }}</p>
                                        <p class="text-xs text-gray-400">{{ $conversation->last_message_at?->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('dashboard.conversations.show', $conversation) }}"
                                                class="text-sm bg-orange-500 text-white px-3 py-1 rounded-lg hover:bg-orange-700 font-semibold">
                                                View
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('dashboard.conversations.destroy', $conversation) }}"
                                                  onsubmit="return confirm('Delete this conversation and all messages?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-400 hover:text-red-600 transition"
                                                    title="Delete conversation">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>{{ $conversations->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>
