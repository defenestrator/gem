<?php

use App\Models\Animal;
use App\Models\Species;

it('renders a nonempty storefront with a linked species using the existing schema', function () {
    $this->withoutVite();
    config(['scout.driver' => 'null']);
    $species = Species::factory()->create(['species' => 'Python regius']);
    $animal = Animal::query()->create(['pet_name' => 'Test Ball Python', 'slug' => 'test-ball-python', 'species_id' => $species->id, 'status' => 'published']);
    $row = [
        'Animal_Id*' => $animal->slug, 'State' => 'For Sale', 'Enabled' => 'Active',
        'Visibility' => 'Public', 'Photo_Urls' => '', 'Title*' => $animal->pet_name,
        'Category*' => 'Ball Pythons', 'Maturity' => 'Adult', 'Traits' => '', 'Sex' => 'female', 'Origin' => 'Captive Bred',
        'Desc' => '', 'Diet' => '', 'Min_Shipping' => 50, 'Max_Shipping' => 100,
        'Is_Negotiable' => '', 'Is_For_Trade' => '', 'Price' => 250, 'Mm_Url**' => 'https://example.test/listing',
    ];
    $originalStorage = app()->storagePath();
    $temporaryStorage = sys_get_temp_dir().'/gem-species-homepage-'.bin2hex(random_bytes(8));
    mkdir($temporaryStorage.'/app/public', 0777, true);
    app()->useStoragePath($temporaryStorage);
    try {
        file_put_contents(storage_path('app/public/animals.json'), json_encode([$row]));
        $this->get('/')->assertOk()->assertSee('Python regius')->assertSee(route('species.show', $species));
    } finally {
        app()->useStoragePath($originalStorage);
        unlink($temporaryStorage.'/app/public/animals.json');
        rmdir($temporaryStorage.'/app/public');
        rmdir($temporaryStorage.'/app');
        rmdir($temporaryStorage);
    }
});
