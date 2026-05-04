<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ValidatesTurnstile;
use App\Mail\ClassifiedInquiryMail;
use App\Models\Classified;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClassifiedInquiryController extends Controller
{
    use ValidatesTurnstile;
    public function create(Classified $classified)
    {
        abort_unless($classified->status === 'published', 404);

        return view('classifieds.inquiry', ['classified' => $classified->load('media')]);
    }

    public function store(Request $request, Classified $classified)
    {
        abort_unless($classified->status === 'published', 404);

        $this->verifyTurnstile($request);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'message' => 'required|string|max:2000',
        ]);

        $inquiry = Inquiry::create([
            ...$validated,
            'classified_id' => $classified->id,
            'user_id'       => auth()->id(),
        ]);

        if ($classified->user?->email) {
            Mail::to($classified->user->email)->queue(new ClassifiedInquiryMail($inquiry, $classified));
        }

        return redirect()
            ->route('classifieds.show', $classified)
            ->with('inquiry_sent', true);
    }
}
