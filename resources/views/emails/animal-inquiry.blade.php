<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inquiry about {{ $animal->pet_name }}</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .label { font-weight: bold; color: #555; }
        .message-box { background: #f5f5f5; border-left: 4px solid #f97316; padding: 12px 16px; margin: 16px 0; white-space: pre-wrap; }
        .footer { margin-top: 24px; font-size: 0.875rem; color: #888; }
    </style>
</head>
<body>
<div class="container">
    <h2>New inquiry about <strong>{{ $animal->pet_name }}</strong></h2>

    <p><span class="label">From:</span> {{ $inquiry->name }} &lt;{{ $inquiry->email }}&gt;</p>
    @if ($inquiry->phone)
        <p><span class="label">Phone:</span> {{ $inquiry->phone }}</p>
    @endif

    <div class="message-box">{{ $inquiry->message }}</div>

    <p>
        <a href="{{ route('dashboard.inquiries.show', $inquiry) }}" style="background:#f97316;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;font-weight:bold;">
            View &amp; Reply in Dashboard
        </a>
    </p>
    <p style="font-size:0.875rem;color:#888;">Or reply directly to this email to respond outside the dashboard.</p>

    <div class="footer">
        This inquiry was submitted via {{ config('app.name') }}.
    </div>
</div>
</body>
</html>
