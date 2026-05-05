<?php

namespace App\Http\Controllers;

use App\Mail\ForwardedInboundEmailMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InboundEmailController extends Controller
{
    public function handle(Request $request)
    {
        try {
            // Extract email data from SendGrid webhook
            $from = $request->input('from');
            $to = $request->input('to');
            $subject = $request->input('subject');
            $text = $request->input('text');
            $html = $request->input('html');
            $attachments = [];

            // Handle attachments
            for ($i = 0; $i < 10; $i++) {
                if ($request->hasFile("attachment{$i}")) {
                    $attachments[] = $request->file("attachment{$i}");
                }
            }

            // Forward email to admin
            $adminEmail = 'jeremyblc@gmail.com';
            Mail::to($adminEmail)->queue(new ForwardedInboundEmailMail(
                from: $from,
                to: $to,
                subject: $subject,
                text: $text,
                html: $html,
                attachments: $attachments,
            ));

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            \Log::error('Inbound email webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
