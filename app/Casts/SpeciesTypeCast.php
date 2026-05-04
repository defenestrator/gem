<?php

namespace App\Casts;

use App\Enums\SpeciesType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Parses space-delimited type tokens (e.g. "x h") into SpeciesType[].
 * Returns the string "null" when the column value is empty or null.
 */
class SpeciesTypeCast implements CastsAttributes
{
    /** @return SpeciesType[]|string */
    public function get(Model $model, string $key, mixed $value, array $attributes): array|string
    {
        if ($value === null || trim($value) === '') {
            return 'null';
        }

        return collect(explode(' ', trim($value)))
            ->filter()
            ->map(fn (string $token) => SpeciesType::tryFrom($token))
            ->filter()
            ->values()
            ->all();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string|null
    {
        if ($value === null || $value === 'null' || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return implode(' ', array_map(
                fn (SpeciesType $t) => $t->value,
                $value
            ));
        }

        return (string) $value;
    }
}
