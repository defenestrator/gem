<x-mail-layout>
    <div style="margin-bottom: 24px;">
        <h2 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 8px 0;">
            New {{ $ticket->type === 'suggestion' ? 'Suggestion' : 'Bug Report' }} Received
        </h2>
        <p style="color: #6b7280; margin: 0;">A support ticket has been submitted via the website.</p>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

    <table style="width: 100%; margin-bottom: 24px;">
        <tr>
            <td style="padding: 8px 0; color: #555; width: 40%;"><strong>Name:</strong></td>
            <td style="padding: 8px 0; color: #111827;">{{ $ticket->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #555;"><strong>Email:</strong></td>
            <td style="padding: 8px 0; color: #111827;">{{ $ticket->email }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #555;"><strong>Type:</strong></td>
            <td style="padding: 8px 0; color: #111827;">
                {{ $ticket->type === 'suggestion' ? 'Feature Suggestion' : 'Bug Report' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #555;"><strong>Submitted:</strong></td>
            <td style="padding: 8px 0; color: #111827;">{{ $ticket->created_at->format('M j, Y g:i A') }}</td>
        </tr>
        @if($isNewUser)
        <tr>
            <td style="padding: 8px 0; color: #555;"><strong>Account:</strong></td>
            <td style="padding: 8px 0; color: #16a34a; font-weight: 600;">New account created — verification email sent</td>
        </tr>
        @endif
    </table>

    <div style="background-color: #f9fafb; border-left: 4px solid #f97316; padding: 12px 16px; margin-bottom: 24px;">
        <strong style="color: #555;">Message:</strong>
        <div style="margin-top: 8px; color: #111827; white-space: pre-wrap;">{{ $ticket->message }}</div>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

    <a href="mailto:{{ $ticket->email }}?subject=Re: Your support ticket"
       style="display: inline-block; background-color: #f97316; color: #ffffff; text-align: center; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">
        Reply to {{ $ticket->name }}
    </a>
</x-mail-layout>
