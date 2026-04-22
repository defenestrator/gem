<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inquiry about {{ $classified->title }}</title>
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
    <h2>New inquiry about <strong>{{ $classified->title }}</strong></h2>

    <p><span class="label">From:</span> {{ $inquiry->name }} &lt;{{ $inquiry->email }}&gt;</p>
    @if ($inquiry->phone)
        <p><span class="label">Phone:</span> {{ $inquiry->phone }}</p>
    @endif

    <div class="message-box">{{ $inquiry->message }}</div>

    <p>Reply directly to this email to respond to the buyer.</p>

    <div class="footer">
        This inquiry was submitted via {{ config('app.name') }}.
    </div>
</div>
</body>
</html>
