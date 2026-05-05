<x-guest-layout>
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-orange-600">New Inquiry Received</h1>
            <p class="text-gray-600 mt-2">Someone has inquired about a classified listing</p>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Inquiry Details</h2>

            <div class="space-y-3">
                <div>
                    <span class="font-medium text-gray-700">Classified:</span>
                    <span class="text-gray-900">{{ $classified->title }}</span>
                </div>

                <div>
                    <span class="font-medium text-gray-700">Inquirer Name:</span>
                    <span class="text-gray-900">{{ $inquiry->name }}</span>
                </div>

                <div>
                    <span class="font-medium text-gray-700">Inquirer Email:</span>
                    <span class="text-gray-900">{{ $inquiry->email }}</span>
                </div>

                @if($inquiry->phone)
                <div>
                    <span class="font-medium text-gray-700">Inquirer Phone:</span>
                    <span class="text-gray-900">{{ $inquiry->phone }}</span>
                </div>
                @endif

                <div>
                    <span class="font-medium text-gray-700">Message:</span>
                    <div class="mt-1 p-3 bg-gray-50 rounded text-gray-900 whitespace-pre-wrap">
                        {{ $inquiry->message }}
                    </div>
                </div>

                <div>
                    <span class="font-medium text-gray-700">Submitted:</span>
                    <span class="text-gray-900">{{ $inquiry->created_at->format('M j, Y g:i A') }}</span>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 mt-6">
            <div class="flex space-x-4">
                <a href="{{ route('classifieds.show', $classified) }}" class="flex-1 bg-orange-500 text-white text-center py-2 px-4 rounded-lg hover:bg-orange-600 transition">
                    View Classified Listing
                </a>
                <a href="mailto:{{ $inquiry->email }}?subject=Re: Inquiry about {{ $classified->title }}" class="flex-1 bg-green-500 text-white text-center py-2 px-4 rounded-lg hover:bg-green-600 transition">
                    Reply to Inquirer
                </a>
            </div>
        </div>

        <div class="text-center mt-8">
            <p class="text-sm text-gray-500">
                <strong>Gem Reptiles Admin Notification</strong>
            </p>
        </div>
    </div>
</x-guest-layout>