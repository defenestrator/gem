<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Inquiries
            </h2>
            @php $newCount = $inquiries->where('status', \App\Enums\InquiryStatus::New)->count(); @endphp
            @if ($newCount)
                <span class="bg-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                    {{ $newCount }} new
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if ($inquiries->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-10 text-center text-gray-400 dark:text-gray-500">
                    No inquiries yet.
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Animal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">From</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">Message</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden lg:table-cell">Date</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($inquiries as $inquiry)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $inquiry->status === \App\Enums\InquiryStatus::New ? 'font-semibold' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 text-xs rounded-full {{ $inquiry->status->badgeClasses() }}">
                                            {{ $inquiry->status->label() }}
                                        </span>
                                        @if ($inquiry->replies->count())
                                            <span class="ml-1 text-xs text-gray-400">{{ $inquiry->replies->count() }} repl{{ $inquiry->replies->count() === 1 ? 'y' : 'ies' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($inquiry->animal)
                                            <a href="{{ route('animals.show', $inquiry->animal) }}" class="text-orange-600 dark:text-orange-400 hover:underline text-sm">
                                                {{ $inquiry->animal->pet_name }}
                                            </a>
                                            <p class="text-xs text-gray-400">{{ $inquiry->animal->slug }}</p>
                                        @else
                                            <span class="text-gray-400 text-sm italic">deleted</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm text-gray-800 dark:text-gray-200">{{ $inquiry->name }}</p>
                                        <a href="mailto:{{ $inquiry->email }}" class="text-xs text-blue-500 hover:underline">{{ $inquiry->email }}</a>
                                        @if ($inquiry->phone)
                                            <p class="text-xs text-gray-400">{{ $inquiry->phone }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 hidden md:table-cell max-w-xs">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ Str::limit($inquiry->message, 80) }}</p>
                                    </td>
                                    <td class="px-4 py-3 hidden lg:table-cell whitespace-nowrap">
                                        <p class="text-xs text-gray-400">{{ $inquiry->created_at->format('M j, Y') }}</p>
                                        <p class="text-xs text-gray-400">{{ $inquiry->created_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('dashboard.inquiries.show', $inquiry) }}"
                                            class="text-sm bg-orange-500 text-white px-3 py-1 rounded-lg hover:bg-orange-700 font-semibold">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>{{ $inquiries->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>
