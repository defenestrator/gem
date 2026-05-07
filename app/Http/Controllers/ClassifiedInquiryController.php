<?php

namespace App\Http\Controllers;

use App\Mail\ClassifiedInquiryMail;
use App\Mail\ClassifiedInquiryConfirmationMail;
use App\Mail\ClassifiedInquiryAdminNotificationMail;
use App\Models\Classified;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClassifiedInquiryController extends Controller
{
    public function create(Classified $classified)
    {
        abort_unless($classified->status === 'published', 404);

        return view('classifieds.inquiry', ['classified' => $classified->load('media')]);
    }

    public function store(Request $request, Classified $classified)
    {
        abort_unless($classified->status === 'published', 404);

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

        // Send confirmation email to the inquirer
        Mail::to($inquiry->email)->queue(new ClassifiedInquiryConfirmationMail($inquiry, $classified));

        // Send admin notification
        Mail::to('jeremyblc@gmail.com')->queue(new ClassifiedInquiryAdminNotificationMail($inquiry, $classified));

        return redirect()
            ->route('classifieds.show', $classified)
            ->with('inquiry_sent', true);
    }
}
