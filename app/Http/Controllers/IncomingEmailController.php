<?php

namespace App\Http\Controllers;

use App\Mail\ForwardedInboundEmailMail;
use App\Models\EmailConversation;
use App\Models\EmailMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class IncomingEmailController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $from    = $request->input('from', '');
            $to      = $request->input('to', '');
            $subject = $request->input('subject', '(no subject)');
            $text    = $request->input('text');
            $html    = $request->input('html');
            $headers = $request->input('headers', '');

            // Prefer envelope for reliable from/to addresses
            $envelope = json_decode($request->input('envelope', '{}'), true);
            $fromRaw  = $envelope['from'] ?? $from;
            $toRaw    = is_array($envelope['to'] ?? null)
                ? ($envelope['to'][0] ?? $to)
                : $to;

            [$fromName, $fromEmail] = $this->parseAddress($fromRaw);
            $messageId = $this->extractHeader($headers, 'Message-ID');

            // Skip duplicate messages
            if ($messageId && EmailMessage::where('sendgrid_message_id', $messageId)->exists()) {
                return response()->json(['status' => 'duplicate'], 200);
            }

            // Thread by contact email + base subject
            $baseSubject  = $this->stripReplyPrefix($subject);
            $conversation = EmailConversation::where('contact_email', strtolower($fromEmail))
                ->where('subject', $baseSubject)
                ->latest()
                ->first();

            if ($conversation) {
                $conversation->update([
                    'status'          => 'open',
                    'last_message_at' => now(),
                    'contact_name'    => $conversation->contact_name ?? ($fromName ?: null),
                ]);
            } else {
                $conversation = EmailConversation::create([
                    'contact_email'   => strtolower($fromEmail),
                    'contact_name'    => $fromName ?: null,
                    'subject'         => $baseSubject,
                    'status'          => 'open',
                    'last_message_at' => now(),
                ]);
            }

            EmailMessage::create([
                'conversation_id'    => $conversation->id,
                'direction'          => 'inbound',
                'from_email'         => strtolower($fromEmail),
                'from_name'          => $fromName ?: null,
                'to_email'           => strtolower($toRaw),
                'body_text'          => $text,
                'body_html'          => $html,
                'sendgrid_message_id' => $messageId,
            ]);

            // Forward to all admin users
            $attachments = [];
            for ($i = 1; $i <= 10; $i++) {
                if ($request->hasFile("attachment{$i}")) {
                    $attachments[] = $request->file("attachment{$i}");
                }
            }

            User::where('is_admin', true)->pluck('email')->each(function ($adminEmail) use ($from, $to, $subject, $text, $html, $attachments) {
                Mail::to($adminEmail)->queue(new ForwardedInboundEmailMail(
                    from: $from,
                    to: $to,
                    subject: $subject,
                    text: $text,
                    html: $html,
                    attachments: $attachments,
                ));
            });

            Log::info('inbound_email.received', [
                'from'            => $fromEmail,
                'subject'         => $subject,
                'conversation_id' => $conversation->id,
            ]);

            return response()->json(['status' => 'ok'], 200);
        } catch (\Throwable $e) {
            Log::error('inbound_email.error', ['message' => $e->getMessage()]);
            // Always 200 to prevent SendGrid retries
            return response()->json(['status' => 'error'], 200);
        }
    }

    private function parseAddress(string $address): array
    {
        if (preg_match('/^(.+?)\s*<(.+?)>$/', trim($address), $m)) {
            return [trim($m[1], ' "\''), trim($m[2])];
        }
        return ['', trim($address)];
    }

    private function stripReplyPrefix(string $subject): string
    {
        return trim(preg_replace('/^(re|fwd?|fw)\s*:\s*/i', '', trim($subject)));
    }

    private function extractHeader(string $headers, string $name): ?string
    {
        if (preg_match('/^' . preg_quote($name, '/') . ':\s*(.+)$/im', $headers, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
