<x-mail::layout>
    <div style="margin-bottom: 24px;">
        <h2 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 12px 0;">Inquiry Confirmation</h2>
        <p style="color: #6b7280; margin: 0;">Your inquiry has been sent successfully!</p>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

    <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 24px 0 16px 0;">Inquiry Details</h3>

    <table style="width: 100%; margin-bottom: 24px;">
        <tr>
            <td style="padding: 8px 0; color: #555;">
                <strong>Classified:</strong>
            </td>
            <td style="padding: 8px 0; color: #111827; text-align: right;">
                {{ $classified->title }}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #555;">
                <strong>Your Name:</strong>
            </td>
            <td style="padding: 8px 0; color: #111827; text-align: right;">
                {{ $inquiry->name }}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #555;">
                <strong>Your Email:</strong>
            </td>
            <td style="padding: 8px 0; color: #111827; text-align: right;">
                {{ $inquiry->email }}
            </td>
        </tr>
        @if($inquiry->phone)
        <tr>
            <td style="padding: 8px 0; color: #555;">
                <strong>Your Phone:</strong>
            </td>
            <td style="padding: 8px 0; color: #111827; text-align: right;">
                {{ $inquiry->phone }}
            </td>
        </tr>
        @endif
    </table>

    <div style="background-color: #f9fafb; border-left: 4px solid #f97316; padding: 12px 16px; margin-bottom: 24px;">
        <strong style="color: #555;">Your Message:</strong>
        <div style="margin-top: 8px; color: #111827; white-space: pre-wrap;">{{ $inquiry->message }}</div>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

    <p style="color: #6b7280; font-size: 14px; margin: 16px 0;">
        The seller will respond to your inquiry as soon as possible. You can also view the classified listing at:
    </p>
    <p style="margin: 0;">
        <a href="{{ route('classifieds.show', $classified) }}" style="color: #f97316; font-weight: 600;">
            View Classified Listing
        </a>
    </p>
</x-mail::layout>

        <div class="text-center mt-8">
            <p class="text-sm text-gray-500">
                Thank you for your interest in our reptiles!<br>
                <strong>Gem Reptiles</strong>
            </p>
        </div>
    </div>
</x-guest-layout>