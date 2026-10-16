<?php

namespace App\Http\Controllers;

use App\Jobs\SyncAnimalsJob;
use App\Models\Animal;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
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

        $job = new SyncAnimalsJob;
        $lock = new UniqueLock(app(CacheRepository::class));
        if (! $lock->acquire($job)) {
            return back()->withErrors(['animals_json' => 'An animal import is already pending or running. Try again after it finishes.']);
        }

        try {
            if (! Storage::disk('public')->put('animals.json', $json)) {
                throw new \RuntimeException('Could not store the animal import.');
            }
            // Ownership is already acquired before changing the shared inventory.
            Bus::dispatch($job);
        } catch (\Throwable $exception) {
            $lock->release($job);
            throw $exception;
        }

        return redirect()->route('dashboard')->with('import_success', 'Import queued — animals will sync in the background.');
    }
}
