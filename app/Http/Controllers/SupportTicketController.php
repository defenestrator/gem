<?php

namespace App\Http\Controllers;

use App\Mail\SupportTicketAdminMail;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function create(): View
    {
        return view('support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'type'    => ['required', 'in:bug,suggestion'],
            'message' => ['required', 'string', 'min:8', 'max:5000'],
        ]);

        $isNewUser = false;
        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Str::password(32),
            ]);
            event(new Registered($user));
            Password::sendResetLink(['email' => $user->email]);
            $isNewUser = true;
        }

        $ticket = SupportTicket::create([
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'type'    => $validated['type'],
            'message' => $validated['message'],
            'user_id' => $user->id,
        ]);

        $admins = User::where('is_admin', true)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new SupportTicketAdminMail($ticket, $isNewUser));
        }

        $message = $isNewUser
            ? 'Your support ticket has been submitted. We\'ve created an account for you — check your email to set your password, then verify your address.'
            : 'Your support ticket has been submitted. We\'ll be in touch soon.';

        return redirect()->route('support.create')->with('success', $message);
    }
}
