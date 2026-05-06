<x-mail-layout>
    <div style="margin-bottom: 24px;">
        <h2 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">New Inquiry Received</h2>
        <p style="color: #6b7280; margin: 0;">Someone has inquired about an animal listing.</p>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

    <table style="width: 100%; margin-bottom: 24px;">
        <tr>
            <td style="padding: 8px 0; color: #555; width: 40%;"><strong>Animal:</strong></td>
            <td style="padding: 8px 0; color: #111827;">{{ $animal->pet_name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #555;"><strong>Inquirer Name:</strong></td>
            <td style="padding: 8px 0; color: #111827;">{{ $inquiry->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #555;"><strong>Inquirer Email:</strong></td>
            <td style="padding: 8px 0; color: #111827;">{{ $inquiry->email }}</td>
        </tr>
        @if($inquiry->phone)
        <tr>
            <td style="padding: 8px 0; color: #555;"><strong>Inquirer Phone:</strong></td>
            <td style="padding: 8px 0; color: #111827;">{{ $inquiry->phone }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 8px 0; color: #555;"><strong>Submitted:</strong></td>
            <td style="padding: 8px 0; color: #111827;">{{ $inquiry->created_at->format('M j, Y g:i A') }}</td>
        </tr>
    </table>

    <div style="background-color: #f9fafb; border-left: 4px solid #f97316; padding: 12px 16px; margin-bottom: 24px;">
        <strong style="color: #555;">Message:</strong>
        <div style="margin-top: 8px; color: #111827; white-space: pre-wrap;">{{ $inquiry->message }}</div>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

    <table style="width: 100%;">
        <tr>
            <td style="padding-right: 8px;">
                <a href="{{ route('animals.show', $animal) }}"
                   style="display: block; background-color: #f97316; color: #ffffff; text-align: center; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-weight: 600;">
                    View Animal Listing
                </a>
            </td>
            <td style="padding-left: 8px;">
                <a href="mailto:{{ $inquiry->email }}?subject=Re: Inquiry about {{ $animal->pet_name }}"
                   style="display: block; background-color: #16a34a; color: #ffffff; text-align: center; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-weight: 600;">
                    Reply to Inquirer
                </a>
            </td>
        </tr>
    </table>
</x-mail-layout>
