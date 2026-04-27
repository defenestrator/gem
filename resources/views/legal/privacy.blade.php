<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Privacy Policy
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-200 shadow-sm rounded-lg p-8 max-w-none prose prose-headings:font-serif">

                <p class="text-sm text-gray-500 dark:text-gray-400">Effective date: January 1, 2024 &mdash; Last updated: {{ date('F j, Y') }}</p>

                <h2 class="">Who We Are</h2>
                <p>Gem Reptiles (<strong>gemreptiles.com</strong>) is a private reptile breeding operation. This policy describes how we collect, use, and protect your personally identifiable information (PII) when you use our website.</p>

                <h2>Information We Collect</h2>
                <p>We collect the following information when you create an account or submit an inquiry:</p>
                <ul>
                    <li><strong>Account registration:</strong> name and email address</li>
                    <li><strong>Animal or classified inquiries:</strong> name, email address, phone number (optional), and message content</li>
                    <li><strong>Profile information:</strong> any additional details you voluntarily provide</li>
                </ul>
                <p>We do not collect payment information. We do not use tracking pixels, third-party advertising networks, or behavioral analytics.</p>

                <h2>How We Use Your Information</h2>
                <p>Your information is used solely to:</p>
                <ul>
                    <li>Operate your account and authenticate your identity</li>
                    <li>Forward inquiry messages</li>
                    <li>Send transactional email related to your account (password reset, email verification)</li>
                </ul>

                <h2>Storage and Security</h2>
                <p>Your PII is stored securely in an encrypted database. Passwords are encrypted by our services and are never stored or transmitted in plain text. Access to personal data is restricted to authenticated, authorized user sessions and system administrators.</p>
                <p>We take reasonable technical and organizational measures to protect your data from unauthorized access, disclosure, or loss.</p>

                <h2>Sharing Your Information</h2>
                <p>We do not sell, rent, or share your personal information with third parties except as required by law.</p>
                <p>When you submit an inquiry about an animal your name, email, phone number, and message may forwarded to the owner/administrators (of which, they are currently the same person, hi!) for the purpose of facilitating that transaction. No other sharing occurs.</p>
                <p><strong>At this time, there are no opt-in options for additional sharing or marketing communications.</strong> If such options are introduced in the future, this policy will be updated and your consent will be obtained before any new sharing takes place.</p>

                <h2>Your Rights</h2>
                <p>You may request access to, correction of, or deletion of your personal data at any time by contacting us. Account deletion removes your profile and associated data from our systems, subject to any legal retention obligations.</p>

                <h2>Contact</h2>
                <p>Questions about this policy may be directed to the site administrator via the inquiry system.</p>

            </div>
        </div>
    </div>
</x-app-layout>
