<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ValidatesTurnstile;
use App\Mail\AnimalInquiryMail;
use App\Mail\InquiryConfirmationMail;
use App\Mail\InquiryAdminNotificationMail;
use App\Models\Animal;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{
    use ValidatesTurnstile;
    public function create(Animal $animal)
    {
        abort_unless($animal->status === 'published', 404);

        return view('animals.inquiry', ['animal' => $animal->load('media')]);
    }

    public function store(Request $request, Animal $animal)
    {
        abort_unless($animal->status === 'published', 404);

        $this->verifyTurnstile($request);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'message' => 'required|string|max:2000',
        ]);

        $inquiry = Inquiry::create([
            ...$validated,
            'animal_id' => $animal->id,
            'user_id'   => auth()->id(),
            'status'    => 'new',
        ]);

        i

        // Send confirmation email to the inquirer
        Mail::to($inquiry->email)->queue(new InquiryConfirmationMail($inquiry, $animal));

        // Send admin notification
        Mail::to('jeremyblc@gmail.com')->queue(new InquiryAdminNotificationMail($inquiry, $animal));f ($animal->user?->email) {
            Mail::to($animal->user->email)->queue(new AnimalInquiryMail($inquiry, $animal));
        }

        return redirect()
            ->route('animals.show', $animal)
            ->with('inquiry_sent', true);
    }
}
