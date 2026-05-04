<?php

use App\Enums\SpeciesType;
use App\Models\Species;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// ── Helpers ───────────────────────────────────────────────────────────────────

function csvFixture(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'species_test_');
    $handle = fopen($path, 'w');

    fputcsv($handle, ['type_species', 'Species', 'Author', 'Subspecies', 'Common_name', 'Familyetc', 'sp#', 'changes']);

    foreach ($rows as $row) {
        fputcsv($handle, [
            $row['type_species'] ?? '',
            $row['Species']      ?? 'Testus reptilus',
            $row['Author']       ?? 'SMITH 1999',
            $row['Subspecies']   ?? '',
            $row['Common_name']  ?? 'Test Gecko',
            $row['Familyetc']    ?? 'Gekkonidae, Gekkota, Sauria, Squamata (lizards)',
            $row['sp#']          ?? '99001',
            $row['changes']      ?? '',
        ]);
    }

    fclose($handle);
    return $path;
}

// ── Import: basic insert ──────────────────────────────────────────────────────

test('imports new species rows from csv', function () {
    $csv = csvFixture([
        ['sp#' => '99001', 'Species' => 'Gecko testus'],
        ['sp#' => '99002', 'Species' => 'Agama fakus'],
    ]);

    $this->artisan('species:import', ['--csv' => $csv])
        ->assertExitCode(0);

    expect(DB::table('species')->count())->toBe(2);
    expect(DB::table('species')->where('species_number', '99001')->exists())->toBeTrue();
    expect(DB::table('species')->where('species_number', '99002')->exists())->toBeTrue();

    unlink($csv);
});

test('maps all csv columns to correct db columns', function () {
    $csv = csvFixture([[
        'sp#'          => '88001',
        'type_species' => 'x h',
        'Species'      => 'Python regius',
        'Author'       => 'SHAW 1802',
        'Subspecies'   => 'Python regius regius SHAW 1802',
        'Common_name'  => 'Ball Python',
        'Familyetc'    => 'Pythonidae, Pythoninae, Pythonoidea, Serpentes, Squamata (snakes)',
        'changes'      => 'moved 2010',
    ]]);

    $this->artisan('species:import', ['--csv' => $csv])->assertExitCode(0);

    $row = DB::table('species')->where('species_number', '88001')->first();

    expect($row->species)->toBe('Python regius')
        ->and($row->author)->toBe('SHAW 1802')
        ->and($row->subspecies)->toBe('Python regius regius SHAW 1802')
        ->and($row->common_name)->toBe('Ball Python')
        ->and($row->higher_taxa)->toBe('Pythonidae, Pythoninae, Pythonoidea, Serpentes, Squamata (snakes)')
        ->and($row->changes)->toBe('moved 2010')
        ->and($row->type_species)->toBe('x h');

    unlink($csv);
});

// ── Import: deduplication ─────────────────────────────────────────────────────

test('skips rows that already exist by species_number', function () {
    Species::factory()->create(['species_number' => '99001', 'species' => 'Original Name']);

    $csv = csvFixture([
        ['sp#' => '99001', 'Species' => 'Should Not Overwrite'],
        ['sp#' => '99002', 'Species' => 'New Entry'],
    ]);

    $this->artisan('species:import', ['--csv' => $csv])->assertExitCode(0);

    expect(DB::table('species')->count())->toBe(2);
    expect(DB::table('species')->where('species_number', '99001')->value('species'))
        ->toBe('Original Name');

    unlink($csv);
});

test('second import of same csv adds zero rows', function () {
    $csv = csvFixture([
        ['sp#' => '99001'],
        ['sp#' => '99002'],
    ]);

    $this->artisan('species:import', ['--csv' => $csv])->assertExitCode(0);
    $this->artisan('species:import', ['--csv' => $csv])->assertExitCode(0);

    expect(DB::table('species')->count())->toBe(2);

    unlink($csv);
});

// ── Import: dry-run ───────────────────────────────────────────────────────────

test('dry-run writes no rows to database', function () {
    $csv = csvFixture([
        ['sp#' => '99001'],
        ['sp#' => '99002'],
    ]);

    $this->artisan('species:import', ['--csv' => $csv, '--dry-run' => true])
        ->assertExitCode(0);

    expect(DB::table('species')->count())->toBe(0);

    unlink($csv);
});

test('dry-run reports correct would-import and skipped counts', function () {
    Species::factory()->create(['species_number' => '99001']);

    $csv = csvFixture([
        ['sp#' => '99001'],
        ['sp#' => '99002'],
        ['sp#' => '99003'],
    ]);

    $this->artisan('species:import', ['--csv' => $csv, '--dry-run' => true])
        ->expectsOutputToContain('2')  // would import 99002 + 99003
        ->expectsOutputToContain('1')  // skipped 99001
        ->assertExitCode(0);

    expect(DB::table('species')->count())->toBe(1); // pre-existing row unchanged

    unlink($csv);
});

// ── Import: error cases ───────────────────────────────────────────────────────

test('fails with error when csv path does not exist', function () {
    $this->artisan('species:import', ['--csv' => '/nonexistent/path/species.csv'])
        ->assertExitCode(1);
});

test('skips rows with missing species_number', function () {
    $csv = csvFixture([
        ['sp#' => ''],
        ['sp#' => '99001'],
    ]);

    $this->artisan('species:import', ['--csv' => $csv])->assertExitCode(0);

    expect(DB::table('species')->count())->toBe(1);

    unlink($csv);
});

// ── SpeciesTypeCast ───────────────────────────────────────────────────────────

test('cast returns null string when type_species column is empty', function () {
    $s = Species::factory()->create(['type_species' => null]);
    expect($s->fresh()->type_species)->toBe('null');
});

test('cast parses single type token into SpeciesType array', function () {
    $s = Species::factory()->syntype()->create();
    $types = $s->fresh()->type_species;

    expect($types)->toBeArray()
        ->and($types[0])->toBe(SpeciesType::Syntype);
});

test('cast parses multiple type tokens into SpeciesType array', function () {
    $s = Species::factory()->multitype('x h')->create();
    $types = $s->fresh()->type_species;

    expect($types)->toHaveCount(2)
        ->and($types[0])->toBe(SpeciesType::Syntype)
        ->and($types[1])->toBe(SpeciesType::Holotype);
});

test('cast ignores unrecognised tokens', function () {
    $s = Species::factory()->multitype('x z q')->create();
    $types = $s->fresh()->type_species;

    // only 'x' is a valid SpeciesType case
    expect($types)->toHaveCount(1)
        ->and($types[0])->toBe(SpeciesType::Syntype);
});
