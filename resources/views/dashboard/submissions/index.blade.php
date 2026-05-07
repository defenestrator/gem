<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Description Submissions
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

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
                    <p class="text-sm">No pending submissions.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($pending as $submission)
                        @php
                            $submittable  = $submission->submittable;
                            $typeLabel    = $submission->submittable_type === \App\Models\Species::class ? 'Species' : 'Subspecies';
                            $displayName  = $submission->submittable_type === \App\Models\Species::class
                                ? $submittable?->species
                                : $submittable?->full_name;
                            $detailUrl    = $submission->submittable_type === \App\Models\Species::class
                                ? ($submittable ? route('species.show', $submittable) : null)
                                : ($submittable ? route('subspecies.show', $submittable) : null);
                        @endphp

                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                        {{ $typeLabel }}
                                    </span>
                                    @if ($detailUrl && $displayName)
                                        <a href="{{ $detailUrl }}"
                                           class="block text-sm font-semibold italic text-orange-600 dark:text-orange-400 hover:underline">
                                            {{ $displayName }}
                                        </a>
                                    @endif
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                        by {{ $submission->user?->name ?? 'Unknown' }}
                                        · {{ $submission->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <div class="flex gap-2 shrink-0">
                                    <form method="POST" action="{{ route('dashboard.submissions.approve', $submission) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-1.5 px-4 rounded transition">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.submissions.reject', $submission) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold py-1.5 px-4 rounded transition">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Current description --}}
                            @if ($submittable?->description)
                                <details class="mb-3">
                                    <summary class="text-xs font-medium text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                                        Current description
                                    </summary>
                                    <div class="mt-2 prose prose-sm dark:prose-invert max-w-none text-xs bg-gray-50 dark:bg-gray-900 rounded p-3 max-h-40 overflow-y-auto">
                                        {!! Str::markdown(e($submittable->description)) !!}
                                    </div>
                                </details>
                            @endif

                            {{-- Proposed value --}}
                            <div class="prose prose-sm dark:prose-invert max-w-none text-sm bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 rounded p-4 max-h-64 overflow-y-auto">
                                {!! Str::markdown(e($submission->proposed_value)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $pending->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>
