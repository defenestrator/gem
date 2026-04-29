<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Classifieds') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6">
                <a href="{{ route('dashboard.classifieds.create') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block">
                    Create New Classified
                </a>
            </div>

            @if ($classifieds->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($classifieds as $classified)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                            @if ($classified->media->isNotEmpty())
                                <a href="{{ route('dashboard.classifieds.show', $classified) }}">
                                    <img src="{{ $classified->media->first()->url }}" alt="{{ $classified->title }}"
                                        class="w-full aspect-square object-cover">
                                </a>
                            @endif
                            <div class="p-4">
                            <div class="mb-3">
                                <span class="inline-block px-3 py-1 text-sm rounded-full
                                    @if ($classified->status === 'published')
                                        bg-green-100 text-green-800
                                    @elseif ($classified->status === 'draft')
                                        bg-gray-100 text-gray-800
                                    @else
                                        bg-red-100 text-red-800
                                    @endif
                                ">
                                    {{ ucfirst($classified->status) }}
                                </span>
                            </div>
                            <h3 class="text-lg font-semibold text-orange-600 dark:text-orange-400 mr-2 mb-2">
                                {{ $classified->title }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                {{ Str::limit($classified->description, 100) }}
                            </p>
                            <p class="text-lg font-bold text-green-600 dark:text-green-400 mb-4">
                                ${{ number_format($classified->price, 2) }}
                            </p>
                            <div class="flex gap-2">
                                <a href="{{ route('dashboard.classifieds.show', $classified) }}" class="bg-blue-500 text-white py-1 px-3 rounded text-sm hover:bg-blue-700">
                                    View
                                </a>
                                <a href="{{ route('dashboard.classifieds.edit', $classified) }}" class="bg-yellow-500 text-white py-1 px-3 rounded text-sm hover:bg-yellow-700">
                                    Edit
                                </a>
                                <form action="{{ route('dashboard.classifieds.destroy', $classified) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white py-1 px-3 rounded text-sm hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $classifieds->links() }}
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                    <p class="text-gray-600 dark:text-gray-300 mb-4">You haven't created any classifieds yet.</p>
                    <a href="{{ route('dashboard.classifieds.create') }}" class="bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-700 inline-block">
                        Create Your First Classified
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
