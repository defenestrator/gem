<?php

namespace App\Http\Controllers;

use App\Enums\AnimalAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Animal;

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

        Storage::disk('public')->put('animals.json', file_get_contents($request->file('animals_json')->getRealPath()));

        $json = Storage::disk('public')->get('animals.json');
        $data = json_decode($json, true);

        if (!\is_array($data)) {
            return back()->withErrors(['animals_json' => 'Invalid JSON structure.']);
        }

        $userId  = auth()->id();
        $status  = $request->boolean('publish') ? 'published' : 'draft';
        $synced  = 0;

        foreach ($data as $item) {
            if (empty($item['Animal_Id*'])) continue;

            $animal = Animal::query()->updateOrCreate(
                ['slug' => $item['Animal_Id*']],
                [
                    'pet_name'     => $item['Title*'] ?? 'Unknown',
                    'description'  => $item['Desc'] ?? null,
                    'date_of_birth'=> $this->parseDob($item['Dob'] ?? null),
                    'female'       => isset($item['Sex']) ? strtolower($item['Sex']) === 'female' : false,
                    'mm_url'       => $item['Mm_Url**'] ?? null,
                    'category'     => $item['Category*'] ?? null,
                    'user_id'      => $userId,
                    'status'       => $status,
                    'availability' => AnimalAvailability::fromJsonState($item['State'] ?? ''),
                ]
            );

            $urls = array_filter(explode(' ', $item['Photo_Urls'] ?? ''));
            if (!empty($urls)) {
                $animal->media()->delete();
                foreach ($urls as $url) {
                    $animal->media()->create(['url' => $url, 'user_id' => $userId]);
                }
            }

            $synced++;
        }

        return redirect()->route('dashboard')->with('import_success', "Synced {$synced} animals successfully.");
    }

    private function parseDob(?string $dob): ?string
    {
        if (empty($dob)) return null;

        $formats = ['n/j/Y', 'n/Y', 'Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dob)->toDateString();
            } catch (\Exception) {
                continue;
            }
        }

        return null;
    }
}
