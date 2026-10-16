<?php

use App\Models\User;
use App\Services\AnimalInventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->originalStorage = app()->storagePath();
    $this->isolatedStorage = sys_get_temp_dir().'/gem-storefront-'.bin2hex(random_bytes(8));
    mkdir($this->isolatedStorage.'/app/public', 0777, true);
    app()->useStoragePath($this->isolatedStorage);
    $this->withoutVite();
});

afterEach(function () {
    app()->useStoragePath($this->originalStorage);
    @unlink($this->isolatedStorage.'/app/public/animals.json');
    @rmdir($this->isolatedStorage.'/app/public');
    @rmdir($this->isolatedStorage.'/app');
    @rmdir($this->isolatedStorage);
});

it('excludes private inactive and unavailable inventory even with a populated old cache', function () {
    $base = ['State' => 'For Sale', 'Enabled' => 'Active', 'Visibility' => 'Public'];
    $rows = [
        $base + ['Animal_Id*' => 'public'],
        array_replace($base, ['Animal_Id*' => 'private', 'Visibility' => 'Private']),
        array_replace($base, ['Animal_Id*' => 'inactive', 'Enabled' => 'Inactive']),
        array_replace($base, ['Animal_Id*' => 'sold', 'State' => 'Sold']),
        ['Animal_Id*' => 'unspecified', 'State' => 'For Sale', 'Enabled' => 'Active'],
    ];
    $path = storage_path('app/public/animals.json');
    file_put_contents($path, json_encode($rows));
    Cache::put('animals:inventory:'.filemtime($path).':recent', $rows, 1800);
    expect(array_column(app(AnimalInventoryService::class)->getActiveAnimals(), 'Animal_Id*'))->toBe(['public']);
});

it('renders a fresh session token on each storefront request', function (string $path) {
    $first = str_repeat('a', 40);
    $second = str_repeat('b', 40);
    $this->withSession(['_token' => $first])->get($path)->assertOk()->assertSee($first);
    $this->withSession(['_token' => $second])->get($path)->assertOk()->assertSee($second)->assertDontSee($first);
})->with(['/', '/categories', '/categories/corn-snakes', '/categories/carpet-pythons', '/categories/ball-pythons', '/categories/reticulated-pythons', '/categories/western-hognose']);

it('does not share authenticated navigation with guests', function () {
    $user = new User(['name' => 'Member', 'email' => 'member@example.test']);
    $this->actingAs($user)->get('/')->assertOk()->assertSee('Log Out');
    Auth::logout();
    $this->get('/')->assertOk()->assertDontSee('Log Out')->assertSee('Log In');
});
