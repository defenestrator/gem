<?php

namespace App\Http\Controllers;

use App\Mail\AnimalInquiryMail;
use App\Models\Animal;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{
    public function create(Animal $animal)
    {
        abort_unless($animal->status === 'published', 404);

        return view('animals.inquiry', ['animal' => $animal->load('media')]);
    }

    public function store(Request $request, Animal $animal)
    {
        abort_unless($animal->status === 'published', 404);

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
        ]);

        if ($animal->user?->email) {
            Mail::to($animal->user->email)->queue(new AnimalInquiryMail($inquiry, $animal));
        }

        return redirect()
            ->route('animals.show', $animal)
            ->with('inquiry_sent', true);
    }
}
