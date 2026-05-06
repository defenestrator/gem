<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Gem Reptiles') }}</title>
        <style>
            body {
                font-family: 'Montserrat', sans-serif;
                color: #111827;
                line-height: 1.6;
                background-color: #f3f4f6;
                margin: 0;
                padding: 0;
            }
            .email-container {
                max-width: 600px;
                margin: 20px auto;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            .email-header {
                background-color: #f97316;
                color: #ffffff;
                padding: 24px;
                text-align: center;
                border-radius: 8px 8px 0 0;
            }
            .email-body {
                padding: 32px 24px;
            }
            .email-footer {
                border-top: 1px solid #e5e7eb;
                padding: 16px 24px;
                text-align: center;
                font-size: 0.875rem;
                color: #6b7280;
                border-radius: 0 0 8px 8px;
            }
            a {
                color: #f97316;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
            .button {
                display: inline-block;
                background-color: #f97316;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 600;
                margin: 16px 0;
            }
            .button:hover {
                background-color: #ea580c;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-body">
                {{ $slot }}
            </div>
            <div class="email-footer">
                <p>{{ config('app.name') }} &copy; {{ date('Y') }} · All rights reserved</p>
            </div>
        </div>
    </body>
</html>
