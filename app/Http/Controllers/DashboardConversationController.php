<?php

namespace App\Http\Controllers;

use App\Mail\ConversationReplyMail;
use App\Models\EmailConversation;
use App\Models\EmailMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DashboardConversationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $status = in_array($request->input('status'), ['open', 'closed', 'spam', 'all'])
            ? $request->input('status')
            : 'open';

        $conversations = EmailConversation::query()
            ->withCount('messages')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('last_message_at')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.conversations.index', compact('conversations', 'status'));
    }

    public function show(EmailConversation $conversation)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $conversation->load('messages');

        return view('dashboard.conversations.show', compact('conversation'));
    }

    public function reply(Request $request, EmailConversation $conversation)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        EmailMessage::create([
            'conversation_id' => $conversation->id,
            'direction'       => 'outbound',
            'from_email'      => config('mail.from.address'),
            'from_name'       => config('mail.from.name'),
            'to_email'        => $conversation->contact_email,
            'body_text'       => $validated['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        Mail::to($conversation->contact_email)
            ->queue(new ConversationReplyMail($conversation, $validated['body'], auth()->user()));

        return back()->with('reply_sent', true);
    }

    public function updateStatus(Request $request, EmailConversation $conversation)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:open,closed,spam'],
        ]);

        $conversation->update(['status' => $validated['status']]);

        return back()->with('status_updated', $validated['status']);
    }

    public function destroy(EmailConversation $conversation)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $conversation->delete();

        return redirect()
            ->route('dashboard.conversations.index')
            ->with('success', 'Conversation deleted.');
    }
}
