<?php

use App\Models\Media;
use App\Models\Species;
use App\Models\Subspecies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// --- SubspeciesController::show ---

it('renders subspecies show page', function () {
    $sub = Subspecies::factory()->create();

    $this->get(route('subspecies.show', $sub))
        ->assertOk()
        ->assertSee($sub->full_name);
});

it('show page displays only approved media for guests', function () {
    $sub = Subspecies::factory()->create();
    Media::factory()->create(['mediable_type' => Subspecies::class, 'mediable_id' => $sub->id, 'moderation_status' => 'approved', 'url' => 'https://example.com/approved.jpg']);
    Media::factory()->create(['mediable_type' => Subspecies::class, 'mediable_id' => $sub->id, 'moderation_status' => 'pending', 'url' => 'https://example.com/pending.jpg']);

    $this->get(route('subspecies.show', $sub))
        ->assertSee('approved.jpg')
        ->assertDontSee('pending.jpg');
});

it('show page displays all media for admin', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $sub   = Subspecies::factory()->create();
    Media::factory()->create(['mediable_type' => Subspecies::class, 'mediable_id' => $sub->id, 'moderation_status' => 'approved', 'url' => 'https://example.com/approved.jpg']);
    Media::factory()->create(['mediable_type' => Subspecies::class, 'mediable_id' => $sub->id, 'moderation_status' => 'pending', 'url' => 'https://example.com/pending.jpg']);

    $this->actingAs($admin)
        ->get(route('subspecies.show', $sub))
        ->assertSee('approved.jpg')
        ->assertSee('pending.jpg');
});

// --- SubspeciesController::storeMedia ---

it('unauthenticated user cannot upload media', function () {
    $sub = Subspecies::factory()->create();

    $this->post(route('subspecies.media.store', $sub), [])
        ->assertRedirect(route('login'));
});

it('authenticated user uploads media with pending status', function () {
    Storage::fake('s3');
    $user = User::factory()->create(['is_admin' => false]);
    $sub  = Subspecies::factory()->create();

    $this->actingAs($user)
        ->post(route('subspecies.media.store', $sub), [
            'images' => [UploadedFile::fake()->image('photo.jpg')],
        ])
        ->assertRedirect();

    expect(Media::where('mediable_type', Subspecies::class)->first()->moderation_status)
        ->toBe('pending');
});

it('admin upload is approved immediately', function () {
    Storage::fake('s3');
    $admin = User::factory()->create(['is_admin' => true]);
    $sub   = Subspecies::factory()->create();

    $this->actingAs($admin)
        ->post(route('subspecies.media.store', $sub), [
            'images' => [UploadedFile::fake()->image('photo.jpg')],
        ])
        ->assertRedirect();

    expect(Media::where('mediable_type', Subspecies::class)->first()->moderation_status)
        ->toBe('approved');
});

// --- DashboardSubspeciesMediaController ---

it('non-admin cannot access subspecies media dashboard', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('dashboard.subspecies.media.index'))
        ->assertForbidden();
});

it('admin can access subspecies media dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('dashboard.subspecies.media.index'))
        ->assertOk();
});

it('admin can approve pending subspecies media', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $sub   = Subspecies::factory()->create();
    $media = Media::factory()->create([
        'mediable_type'     => Subspecies::class,
        'mediable_id'       => $sub->id,
        'moderation_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch(route('dashboard.subspecies.media.approve', $media))
        ->assertRedirect();

    expect($media->fresh()->moderation_status)->toBe('approved');
});

it('admin can reject pending subspecies media', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $sub   = Subspecies::factory()->create();
    $media = Media::factory()->create([
        'mediable_type'     => Subspecies::class,
        'mediable_id'       => $sub->id,
        'moderation_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch(route('dashboard.subspecies.media.reject', $media))
        ->assertRedirect();

    expect($media->fresh()->moderation_status)->toBe('rejected');
});

it('approve returns 422 when media is not a subspecies', function () {
    $admin   = User::factory()->create(['is_admin' => true]);
    $species = Species::factory()->create();
    $media   = Media::factory()->create([
        'mediable_type'     => Species::class,
        'mediable_id'       => $species->id,
        'moderation_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch(route('dashboard.subspecies.media.approve', $media))
        ->assertStatus(422);
});

it('non-admin cannot approve subspecies media', function () {
    $user  = User::factory()->create(['is_admin' => false]);
    $sub   = Subspecies::factory()->create();
    $media = Media::factory()->create([
        'mediable_type'     => Subspecies::class,
        'mediable_id'       => $sub->id,
        'moderation_status' => 'pending',
    ]);

    $this->actingAs($user)
        ->patch(route('dashboard.subspecies.media.approve', $media))
        ->assertForbidden();
});
