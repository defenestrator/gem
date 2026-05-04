<?php

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('inserts one row per subspecies line', function () {
    Species::factory()->create([
        'subspecies' => "Python regius regius SHAW 1802\nPython regius thomasi BELL 1825",
    ]);

    $this->artisan('subspecies:extract')->assertExitCode(0);

    expect(Subspecies::count())->toBe(2);
});

it('parses genus species subspecies and author correctly', function () {
    Species::factory()->create([
        'subspecies' => 'Ablepharus budaki anatolicus SCHMIDTLER 1997',
    ]);

    $this->artisan('subspecies:extract')->assertExitCode(0);

    $sub = Subspecies::first();
    expect($sub->genus)->toBe('Ablepharus')
        ->and($sub->species)->toBe('budaki')
        ->and($sub->subspecies)->toBe('anatolicus')
        ->and($sub->author)->toBe('SCHMIDTLER 1997');
});

it('handles multi-author lines', function () {
    Species::factory()->create([
        'subspecies' => 'Iguana iguana insularis BREUIL, VUILLAUME & GRANDJEAN 2019',
    ]);

    $this->artisan('subspecies:extract')->assertExitCode(0);

    expect(Subspecies::first()->author)->toBe('BREUIL, VUILLAUME & GRANDJEAN 2019');
});

it('handles parenthesized authors', function () {
    Species::factory()->create([
        'subspecies' => 'Elgaria multicarinata multicarinata (BLAINVILLE,1835)',
    ]);

    $this->artisan('subspecies:extract')->assertExitCode(0);

    expect(Subspecies::first()->author)->toBe('(BLAINVILLE,1835)');
});

it('skips blank lines', function () {
    Species::factory()->create([
        'subspecies' => "Python regius regius SHAW 1802\n\nPython regius thomasi BELL 1825\n",
    ]);

    $this->artisan('subspecies:extract')->assertExitCode(0);

    expect(Subspecies::count())->toBe(2);
});

it('skips malformed lines with fewer than three words', function () {
    Species::factory()->create([
        'subspecies' => "Valid line here AUTHOR 2000\nbadline",
    ]);

    $this->artisan('subspecies:extract')->assertExitCode(0);

    expect(Subspecies::count())->toBe(1);
});

it('dry run does not write to database', function () {
    Species::factory()->create([
        'subspecies' => 'Python regius regius SHAW 1802',
    ]);

    $this->artisan('subspecies:extract --dry-run')->assertExitCode(0);

    expect(Subspecies::count())->toBe(0);
});

it('skips species that already have subspecies rows without --force', function () {
    $species = Species::factory()->create([
        'subspecies' => 'Python regius regius SHAW 1802',
    ]);

    Subspecies::factory()->create(['species_id' => $species->id]);

    $this->artisan('subspecies:extract')->assertExitCode(0);

    expect(Subspecies::count())->toBe(1);
});

it('re-extracts with --force, replacing existing rows', function () {
    $species = Species::factory()->create([
        'subspecies' => 'Python regius regius SHAW 1802',
    ]);

    Subspecies::factory()->create(['species_id' => $species->id]);

    $this->artisan('subspecies:extract --force')->assertExitCode(0);

    expect(Subspecies::count())->toBe(1)
        ->and(Subspecies::first()->genus)->toBe('Python');
});

it('links subspecies to correct species_id', function () {
    $species = Species::factory()->create([
        'subspecies' => 'Python regius regius SHAW 1802',
    ]);

    $this->artisan('subspecies:extract')->assertExitCode(0);

    expect(Subspecies::first()->species_id)->toBe($species->id);
});

it('reports nothing to extract when table is already populated', function () {
    $species = Species::factory()->create([
        'subspecies' => 'Python regius regius SHAW 1802',
    ]);

    Subspecies::factory()->create(['species_id' => $species->id]);

    $this->artisan('subspecies:extract')
        ->expectsOutputToContain('Nothing to extract')
        ->assertExitCode(0);
});
