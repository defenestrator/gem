<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SpeciesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'species_number' => fake()->unique()->numerify('#####'),
            'type_species'   => null,
            'species'        => fake()->words(2, true),
            'author'         => fake()->lastName() . ' ' . fake()->year(),
            'subspecies'     => null,
            'common_name'    => fake()->words(3, true),
            'higher_taxa'    => 'Colubridae, Colubrinae, Colubroidea, Serpentes, Squamata (snakes)',
            'changes'        => null,
            'description'    => null,
        ];
    }

    public function syntype(): static
    {
        return $this->state(['type_species' => 'x']);
    }

    public function holotype(): static
    {
        return $this->state(['type_species' => 'h']);
    }

    public function multitype(string $types = 'x h'): static
    {
        return $this->state(['type_species' => $types]);
    }
}
