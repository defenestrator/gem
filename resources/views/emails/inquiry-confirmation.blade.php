<x-guest-layout>
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Inquiry Confirmation</h1>
            <p class="text-gray-600 mt-2">Your inquiry has been sent successfully!</p>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Inquiry Details</h2>

            <div class="space-y-3">
                <div>
                    <span class="font-medium text-gray-700">Animal:</span>
                    <span class="text-gray-900">{{ $animal->pet_name }}</span>
                </div>

                <div>
                    <span class="font-medium text-gray-700">Your Name:</span>
                    <span class="text-gray-900">{{ $inquiry->name }}</span>
                </div>

                <div>
                    <span class="font-medium text-gray-700">Your Email:</span>
                    <span class="text-gray-900">{{ $inquiry->email }}</span>
                </div>

                @if($inquiry->phone)
                <div>
                    <span class="font-medium text-gray-700">Your Phone:</span>
                    <span class="text-gray-900">{{ $inquiry->phone }}</span>
                </div>
                @endif

                <div>
                    <span class="font-medium text-gray-700">Your Message:</span>
                    <div class="mt-1 p-3 bg-gray-50 rounded text-gray-900 whitespace-pre-wrap">
                        {{ $inquiry->message }}
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 mt-6">
            <p class="text-sm text-gray-600">
                The seller will respond to your inquiry as soon as possible. You can also view the animal listing at:
                <a href="{{ route('animals.show', $animal) }}" class="text-orange-600 hover:text-orange-800">
                    {{ route('animals.show', $animal) }}
                </a>
            </p>
        </div>

        <div class="text-center mt-8">
            <p class="text-sm text-gray-500">
                Thank you for your interest in our reptiles!<br>
                <strong>Gem Reptiles</strong>
            </p>
        </div>
    </div>
</x-guest-layout>