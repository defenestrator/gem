<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AnimalInventoryService
{
    private const TTL = 1800; // 30 minutes
    private const JSON_PATH = 'app/public/animals.json';

    public function getActiveAnimals(string $sort = 'recent'): array
    {
        $file = storage_path(self::JSON_PATH);

        if (! file_exists($file)) {
            return [];
        }

        $mtime    = filemtime($file);
        $cacheKey = "animals:inventory:{$mtime}:{$sort}";

        return Cache::remember($cacheKey, self::TTL, function () use ($file, $sort) {
            $all = $this->loadJson($file);
            $active = array_values(array_filter($all, fn ($a) =>
                ($a['State'] ?? '') === 'For Sale' && ($a['Enabled'] ?? '') === 'Active'
            ));
            return $this->sort($active, $sort);
        });
    }

    public function getAllAnimals(): array
    {
        $file = storage_path(self::JSON_PATH);

        if (! file_exists($file)) {
            return [];
        }

        $mtime    = filemtime($file);
        $cacheKey = "animals:all:{$mtime}";

        return Cache::remember($cacheKey, self::TTL, fn () => $this->loadJson($file));
    }

    private function loadJson(string $file): array
    {
        $decoded = json_decode(@file_get_contents($file), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function sort(array $animals, string $sort): array
    {
        match ($sort) {
            'price-low'     => usort($animals, fn ($a, $b) => ($a['Price'] ?? 0) <=> ($b['Price'] ?? 0)),
            'price-high'    => usort($animals, fn ($a, $b) => ($b['Price'] ?? 0) <=> ($a['Price'] ?? 0)),
            'date-new'      => usort($animals, fn ($a, $b) => strtotime($b['Dob'] ?? 0) <=> strtotime($a['Dob'] ?? 0)),
            'category'      => usort($animals, fn ($a, $b) => ($a['Category*'] ?? '') <=> ($b['Category*'] ?? '')),
            'category-desc' => usort($animals, fn ($a, $b) => ($b['Category*'] ?? '') <=> ($a['Category*'] ?? '')),
            default         => usort($animals, fn ($a, $b) => strtotime($b['Last_Update**'] ?? 0) <=> strtotime($a['Last_Update**'] ?? 0)),
        };

        return $animals;
    }
}
