<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Animal;

class AnimalImportController extends Controller
{
    public function showForm()
    {
        return view('dashboard.import-animals');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'animals_json' => 'required|file|mimes:json',
        ]);

        Storage::disk('public')->put('animals.json', file_get_contents($request->file('animals_json')->getRealPath()));

        $json = Storage::disk('public')->get('animals.json');
        $data = json_decode($json, true);

        if (!\is_array($data)) {
            return back()->withErrors(['animals_json' => 'Invalid JSON structure.']);
        }

        $userId = auth()->id();
        $synced = 0;

        foreach ($data as $item) {
            if (empty($item['Animal_Id'])) continue;

            Animal::query()->updateOrCreate(
                ['slug' => $item['Animal_Id']],
                [
                    'pet_name'         => $item['Name'] ?? 'Unknown',
                    'description'      => $item['Description'] ?? null,
                    'date_of_birth'    => $item['DOB'] ?? null,
                    'female'           => isset($item['Sex']) ? strtolower($item['Sex']) === 'female' : false,
                    'user_id'          => $userId,
                ]
            );
            $synced++;
        }

        return redirect()->route('dashboard')->with('import_success', "Synced {$synced} animals successfully.");
    }
}
