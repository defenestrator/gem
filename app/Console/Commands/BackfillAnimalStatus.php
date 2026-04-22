<?php

namespace App\Console\Commands;

use App\Enums\AnimalAvailability;
use App\Models\Animal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillAnimalStatus extends Command
{
    protected $signature = 'animals:backfill-status';
    protected $description = 'Backfill animal status and availability from the stored animals.json';

    public function handle(): int
    {
        $path = 'animals.json';

        if (!Storage::disk('public')->exists($path)) {
            $this->error('No animals.json found in public storage. Upload one via the import page first.');
            return self::FAILURE;
        }

        $data = json_decode(Storage::disk('public')->get($path), true);

        if (!is_array($data)) {
            $this->error('animals.json is not valid JSON.');
            return self::FAILURE;
        }

        $updated = 0;

        foreach ($data as $item) {
            $id = $item['Animal_Id*'] ?? null;
            if (!$id) continue;

            $animal = Animal::query()->where('slug', $id)->first();
            if (!$animal) continue;

            $status = (($item['Enabled'] ?? '') === 'Active' && ($item['Visibility'] ?? '') === 'Public')
                ? 'published'
                : 'draft';

            $animal->status       = $status;
            $animal->availability = AnimalAvailability::fromJsonState($item['State'] ?? '');
            $animal->price        = isset($item['Price']) && $item['Price'] > 0 ? $item['Price'] : null;
            $animal->save();

            $updated++;
        }

        $this->info("Backfilled {$updated} animal records.");

        return self::SUCCESS;
    }
}
