<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Re: Your inquiry about {{ $inquiry->animal?->pet_name }}</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .reply-box { background: #fff7ed; border-left: 4px solid #f97316; padding: 12px 16px; margin: 16px 0; white-space: pre-wrap; }
        .original-box { background: #f5f5f5; border-left: 4px solid #9ca3af; padding: 12px 16px; margin: 16px 0; white-space: pre-wrap; font-size: 0.875rem; color: #555; }
        .footer { margin-top: 24px; font-size: 0.875rem; color: #888; border-top: 1px solid #eee; padding-top: 16px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Reply from <strong>{{ config('app.name') }}</strong></h2>
    <p>Hi {{ $inquiry->name }}, you received a reply regarding your inquiry about <strong>{{ $inquiry->animal?->pet_name }}</strong>.</p>

    <div class="reply-box">{{ $reply->body }}</div>

    <p>You can reply directly to this email to continue the conversation.</p>

    <div class="original-box">
        <strong>Your original message:</strong><br><br>
        {{ $inquiry->message }}
    </div>

    <div class="footer">
        This is a reply to an inquiry submitted via {{ config('app.name') }} ({{ config('app.url') }}).
    </div>
</div>
</body>
</html>
