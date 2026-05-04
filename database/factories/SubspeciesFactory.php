<?php

namespace Database\Factories;

use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubspeciesFactory extends Factory
{
    public function definition(): array
    {
        $genus   = fake()->word();
        $species = fake()->word();

        return [
            'species_id' => Species::factory(),
            'genus'      => ucfirst($genus),
            'species'    => $species,
            'subspecies' => fake()->word(),
            'author'     => strtoupper(fake()->lastName()) . ' ' . fake()->year(),
        ];
    }
}
