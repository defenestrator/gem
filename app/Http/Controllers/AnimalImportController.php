<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class AnimalImportController extends Controller
{
    public function showForm()
    {
        $this->authorize('create', Animal::class);

        return view('dashboard.import-animals');
    }

    public function upload(Request $request)
    {
        $this->authorize('create', Animal::class);
        $request->validate([
            'animals_json' => 'required|file|mimes:json',
        ]);

        $json = file_get_contents($request->file('animals_json')->getRealPath());

        if (! is_array(json_decode($json, true))) {
            return back()->withErrors(['animals_json' => 'Invalid JSON structure.']);
        }

        Storage::disk('public')->put('animals.json', $json);

        Artisan::call('animals:sync');

        return redirect()->route('dashboard')->with('import_success', 'Animals synced successfully.');
    }
}
