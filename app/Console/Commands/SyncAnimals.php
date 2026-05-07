<?php

namespace App\Console\Commands;

use App\Enums\AnimalAvailability;
use App\Models\Animal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncAnimals extends Command
{
    protected $signature = 'animals:sync
                            {--dry-run : Count records without writing to the database}';

    protected $description = 'Sync the animals table and animals.json from the stored MorphMarket export';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        if (! $disk->exists('animals.json')) {
            $this->warn('animals.json not found on public disk. Upload a MorphMarket export first.');
            return self::FAILURE;
        }

        $data = json_decode($disk->get('animals.json'), true);

        if (! is_array($data)) {
            $this->error('animals.json contains invalid JSON.');
            return self::FAILURE;
        }

        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('[DRY RUN] No data will be written.');
            $count = collect($data)->filter(fn ($i) => ! empty($i['Animal_Id*']))->count();
            $this->info("Would sync {$count} animals.");
            return self::SUCCESS;
        }

        $userId = User::where('is_admin', true)->value('id');

        if (! $userId) {
            $this->error('No admin user found. Cannot assign animal ownership.');
            return self::FAILURE;
        }

        $synced = 0;

        foreach ($data as $item) {
            if (empty($item['Animal_Id*'])) {
                continue;
            }

            $status = (($item['Enabled'] ?? '') === 'Active' && ($item['Visibility'] ?? '') === 'Public')
                ? 'published'
                : 'draft';

            $animal = Animal::query()->updateOrCreate(
                ['slug' => $item['Animal_Id*']],
                [
                    'pet_name'        => $item['Title*'] ?? 'Unknown',
                    'description'     => $item['Desc'] ?? null,
                    'date_of_birth'   => $this->parseDob($item['Dob'] ?? null),
                    'female'          => isset($item['Sex']) ? strtolower($item['Sex']) === 'female' : false,
                    'mm_url'          => $item['Mm_Url**'] ?? null,
                    'category'        => $item['Category*'] ?? null,
                    'user_id'         => $userId,
                    'status'          => $status,
                    'availability'    => AnimalAvailability::fromJsonState($item['State'] ?? ''),
                    'price'           => isset($item['Price']) && $item['Price'] > 0 ? $item['Price'] : null,
                ]
            );

            $urls = array_filter(explode(' ', $item['Photo_Urls'] ?? ''));
            if (! empty($urls)) {
                $animal->media()->delete();
                foreach ($urls as $url) {
                    $animal->media()->create(['url' => $url, 'user_id' => $userId]);
                }
            }

            $synced++;
        }

        $this->info("Synced {$synced} animals.");

        $this->call('animals:mirror-media');

        $this->call('media:process-animals');

        // Rewrite Photo_Urls and Thumbnail_Url in animals.json from mirrored+processed DB media.
        $this->info('Rewriting Photo_Urls and Thumbnail_Url in animals.json from DB media...');

        $mediaBySlug = Animal::query()
            ->with(['media' => fn ($q) => $q->select('id', 'mediable_id', 'mediable_type', 'url', 'thumbnail_url')])
            ->get(['id', 'slug'])
            ->mapWithKeys(fn ($a) => [
                $a->slug => [
                    'urls'      => $a->media->pluck('url')->filter()->values()->all(),
                    'thumbnail' => $a->media->first()?->thumbnail_url,
                ],
            ]);

        $updated = 0;
        foreach ($data as &$item) {
            $slug = $item['Animal_Id*'] ?? null;
            if ($slug && ! empty($mediaBySlug[$slug]['urls'])) {
                $item['Photo_Urls']    = implode(' ', $mediaBySlug[$slug]['urls']);
                $item['Thumbnail_Url'] = $mediaBySlug[$slug]['thumbnail'] ?? null;
                $updated++;
            }
        }
        unset($item);

        $disk->put('animals.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->info("Rewrote Photo_Urls for {$updated} animals.");

        return self::SUCCESS;
    }

    private function parseDob(?string $dob): ?string
    {
        if (empty($dob)) {
            return null;
        }

        foreach (['n/j/Y', 'n/Y', 'Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $dob)->toDateString();
            } catch (\Exception) {
                continue;
            }
        }

        return null;
    }
}
