<?php

namespace App\Http\Controllers;

use App\Enums\InquiryStatus;
use App\Mail\InquiryReplyMail;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DashboardInquiryController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->is_admin, 403);

        $inquiries = Inquiry::query()
            ->with(['animal', 'replies'])
            ->orderByRaw("CASE status WHEN 'new' THEN 0 WHEN 'read' THEN 1 WHEN 'replied' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(25);

        return view('dashboard.inquiries.index', compact('inquiries'));
    }

    public function show(Inquiry $inquiry)
    {
        abort_unless(auth()->user()->is_admin, 403);

        if ($inquiry->status === InquiryStatus::New) {
            $inquiry->update(['status' => InquiryStatus::Read]);
        }

        $inquiry->load('animal.media', 'replies.admin');

        return view('dashboard.inquiries.show', compact('inquiry'));
    }

    public function reply(Request $request, Inquiry $inquiry)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $reply = $inquiry->replies()->create([
            'body'          => $validated['body'],
            'admin_user_id' => auth()->id(),
        ]);

        $inquiry->update(['status' => InquiryStatus::Replied]);

        Mail::to($inquiry->email)->queue(new InquiryReplyMail($inquiry->load('animal'), $reply));

        return back()->with('reply_sent', true);
    }

    public function close(Inquiry $inquiry)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $inquiry->update(['status' => InquiryStatus::Closed]);

        return back()->with('inquiry_closed', true);
    }
}
