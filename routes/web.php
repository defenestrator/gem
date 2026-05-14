<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\SupportTicketController;
use App\Models\Animal;
use App\Http\Controllers\ContentSubmissionController;
use App\Http\Controllers\DashboardContentSubmissionController;
use App\Http\Controllers\DashboardMediaModerationController;
use App\Http\Controllers\DashboardSpeciesController;
use App\Http\Controllers\DashboardSubspeciesController;
use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\SubspeciesController;
use App\Http\Controllers\DashboardAnimalController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellerProfileController;
use App\Http\Controllers\ClassifiedController;
use App\Http\Controllers\DashboardClassifiedController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AnimalImportController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ClassifiedInquiryController;
use App\Http\Controllers\DashboardInquiryController;
use App\Http\Controllers\ShippingQuoteController;
use App\Http\Controllers\ShipCenterController;
use App\Http\Controllers\IncomingEmailController;
use App\Http\Controllers\DashboardConversationController;
use App\Services\AnimalInventoryService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

$inventory = app(AnimalInventoryService::class);

    // Animal import routes
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard/animals/import', [AnimalImportController::class, 'showForm'])->name('dashboard.animals.import');
        Route::post('/dashboard/animals/import', [AnimalImportController::class, 'upload'])->name('dashboard.animals.import.upload');
    });

// 301 redirects for all favicon/manifest paths → DO Spaces CDN.
// Nginx serves public/ files before PHP — activate these routes by adding
// scripts/nginx-favicons.conf to the Forge site nginx config (see that file).
foreach ([
    '/favicon.ico'                     => 'favicon.ico',
    '/favicon.png'                     => 'favicon.png',
    '/favicon-16x16.png'               => 'favicon-16x16.png',
    '/favicon-32x32.png'               => 'favicon-32x32.png',
    '/apple-touch-icon.png'            => 'apple-touch-icon.png',
    '/apple-touch-icon-precomposed.png'=> 'apple-touch-icon.png',
    '/site.webmanifest'                => 'site.webmanifest',
    '/ms-favicon.png'                  => 'ms-favicon.png',
] as $path => $asset) {
    Route::get($path, fn() => redirect(
        "https://gemx.sfo3.digitaloceanspaces.com/assets/{$asset}", 301
    )->header('Cache-Control', 'public, max-age=31536000, immutable'));
}

Route::get('/', function (Request $request) use ($inventory) {
    $sort        = $request->query('sort', 'recent');
    $file        = storage_path('app/public/animals.json');
    $mtime       = file_exists($file) ? filemtime($file) : 0;
    $responseKey = "welcome:{$sort}:{$mtime}";

    $response = Cache::remember($responseKey, 1800, function () use ($inventory, $sort) {
        $animals = $inventory->getActiveAnimals($sort);

        $slugs      = array_column($animals, 'Animal_Id*');
        $speciesMap = $slugs
            ? Animal::whereIn('slug', $slugs)
                ->whereNotNull('species_id')
                ->with('species:id,species,common_name,slug')
                ->get()
                ->keyBy('slug')
                ->map(fn ($a) => $a->species)
            : collect();

        return view('welcome', [
            'animals'     => $animals,
            'currentSort' => $sort,
            'speciesMap'  => $speciesMap,
        ])->render();
    });

    return response($response);
})->name('welcome');

Route::get('/categories', function () use ($inventory) {
    $file  = storage_path('app/public/animals.json');
    $mtime = file_exists($file) ? filemtime($file) : 0;

    $response = Cache::remember("categories:{$mtime}", 1800, function () use ($inventory) {
        $animals = $inventory->getActiveAnimals();

        $categoryList = ['Corn Snakes', 'Carpet Pythons', 'Ball Pythons', 'Reticulated Pythons', 'Western Hognose'];
        $categories   = [];

        foreach ($categoryList as $cat) {
            $categories[$cat] = count(array_filter($animals, fn ($a) => ($a['Category*'] ?? '') === $cat));
        }

        return view('categories', ['categories' => $categories])->render();
    });

    return response($response);
})->name('categories');

Route::prefix('categories')->group(function () use ($inventory) {
    $file  = storage_path('app/public/animals.json');
    $mtime = file_exists($file) ? filemtime($file) : 0;

    $categoryRoute = function (string $slug, string $category, string $view) use ($inventory, $mtime) {
        return function () use ($inventory, $mtime, $category, $view) {
            $response = Cache::remember("categories:{$view}:{$mtime}", 1800, function () use ($inventory, $category, $view) {
                $animals = array_values(array_filter(
                    $inventory->getActiveAnimals(),
                    fn ($a) => ($a['Category*'] ?? '') === $category
                ));
                return view($view, ['animals' => $animals])->render();
            });
            return response($response);
        };
    };

    Route::get('/corn-snakes', $categoryRoute('corn-snakes', 'Corn Snakes', 'corn-snakes'))
        ->name('categories.corn-snakes');

    Route::get('/carpet-pythons', $categoryRoute('carpet-pythons', 'Carpet Pythons', 'carpet-pythons'))
        ->name('categories.carpet-pythons');

    Route::get('/ball-pythons', $categoryRoute('ball-pythons', 'Ball Pythons', 'ball-pythons'))
        ->name('categories.ball-pythons');

    Route::get('/reticulated-pythons', $categoryRoute('reticulated-pythons', 'Reticulated Pythons', 'reticulated-pythons'))
        ->name('categories.reticulated-pythons');

    Route::get('/western-hognose', $categoryRoute('western-hognose', 'Western Hognose', 'western-hognose'))
        ->name('categories.western-hognose');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Breeder Profiles Routes
Route::get('/profiles', [SellerController::class, 'index'])->name('profiles.index');
Route::get('/profiles/{seller:slug}', [SellerController::class, 'show'])->name('profiles.show');

// Classifieds Routes
if (config('features.classifieds')) {
    Route::get('/classifieds', [ClassifiedController::class, 'index'])->name('classifieds.index');
    Route::get('/classifieds/{classified:slug}', [ClassifiedController::class, 'show'])->name('classifieds.show');
    Route::get('/classifieds/{classified:slug}/inquire', [ClassifiedInquiryController::class, 'create'])->name('classifieds.inquiries.create');
    Route::post('/classifieds/{classified:slug}/inquire', [ClassifiedInquiryController::class, 'store'])->name('classifieds.inquiries.store');
}

if (config('features.easyship')) {
    Route::post('/shipping/quote',    ShippingQuoteController::class)->name('shipping.quote');
    Route::post('/shipping/location', ShipCenterController::class)->name('shipping.location');
}

// Species Routes
Route::get('/species', [SpeciesController::class, 'index'])->name('species.index');
Route::get('/species/search', [SpeciesController::class, 'search'])->name('species.search')->middleware('cache.headers:public;max_age=300;s_maxage=300;etag');
Route::get('/species/{species}', [SpeciesController::class, 'show'])->name('species.show');
Route::post('/species/{species}/media', [SpeciesController::class, 'storeMedia'])->name('species.media.store')->middleware('auth');
Route::post('/species/{species}/submissions', [ContentSubmissionController::class, 'storeForSpecies'])->name('species.submissions.store')->middleware('auth');

// Subspecies Routes
Route::get('/subspecies/{subspecies}', [SubspeciesController::class, 'show'])->name('subspecies.show');
Route::post('/subspecies/{subspecies}/media', [SubspeciesController::class, 'storeMedia'])->name('subspecies.media.store')->middleware('auth');
Route::post('/subspecies/{subspecies}/submissions', [ContentSubmissionController::class, 'storeForSubspecies'])->name('subspecies.submissions.store')->middleware('auth');
Route::get('/media/{media}/attribution', [MediaController::class, 'attribution'])->name('media.attribution');

// Animals Routes
Route::get('/animals', [AnimalController::class, 'index'])->name('animals.index');
Route::get('/animals/search', [AnimalController::class, 'search'])->name('animals.search');
Route::get('/animals/{animal:slug}', [AnimalController::class, 'show'])->name('animals.show');
Route::get('/animals/{animal:slug}/inquire', [InquiryController::class, 'create'])->name('animals.inquiries.create');
Route::post('/animals/{animal:slug}/inquire', [InquiryController::class, 'store'])->name('animals.inquiries.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::patch('/profile/seller', [SellerProfileController::class, 'save'])->name('profile.seller.save');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard CRUD
    Route::name('dashboard.')->group(function () {
        if (config('features.classifieds')) {
            Route::resource('dashboard/classifieds', DashboardClassifiedController::class)->middleware('verified');
        }
        Route::resource('dashboard/animals', DashboardAnimalController::class)->middleware('verified');
        Route::delete('dashboard/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy')->middleware('verified');

        // Inquiries (admin only)
        Route::get('dashboard/inquiries', [DashboardInquiryController::class, 'index'])->name('inquiries.index');
        Route::get('dashboard/inquiries/{inquiry}', [DashboardInquiryController::class, 'show'])->name('inquiries.show');
        Route::post('dashboard/inquiries/{inquiry}/reply', [DashboardInquiryController::class, 'reply'])->name('inquiries.reply');
        Route::patch('dashboard/inquiries/{inquiry}/close', [DashboardInquiryController::class, 'close'])->name('inquiries.close');
        Route::delete('dashboard/inquiries/{inquiry}', [DashboardInquiryController::class, 'destroy'])->name('inquiries.destroy');

        // Unified media moderation queue (admin only)
        Route::get('dashboard/media', [DashboardMediaModerationController::class, 'index'])->name('media.index');
        Route::patch('dashboard/media/{media}/approve', [DashboardMediaModerationController::class, 'approve'])->name('media.approve');
        Route::patch('dashboard/media/{media}/reject', [DashboardMediaModerationController::class, 'reject'])->name('media.reject');
        Route::patch('dashboard/media/{media}/feature', [DashboardMediaModerationController::class, 'setFeatured'])->name('media.feature');

        // Species admin editing + media detach
        Route::get('dashboard/species/{species}/edit', [DashboardSpeciesController::class, 'edit'])->name('species.edit');
        Route::patch('dashboard/species/{species}', [DashboardSpeciesController::class, 'update'])->name('species.update');
        Route::delete('dashboard/species/{species}/media/{media}', [DashboardSpeciesController::class, 'detachMedia'])->name('species.media.detach');

        // Subspecies admin editing + media detach
        Route::get('dashboard/subspecies/{subspecies}/edit', [DashboardSubspeciesController::class, 'edit'])->name('subspecies.edit');
        Route::patch('dashboard/subspecies/{subspecies}', [DashboardSubspeciesController::class, 'update'])->name('subspecies.update');
        Route::delete('dashboard/subspecies/{subspecies}/media/{media}', [DashboardSubspeciesController::class, 'detachMedia'])->name('subspecies.media.detach');

        // Content submission moderation (admin only)
        Route::get('dashboard/submissions', [DashboardContentSubmissionController::class, 'index'])->name('submissions.index');
        Route::patch('dashboard/submissions/{submission}/approve', [DashboardContentSubmissionController::class, 'approve'])->name('submissions.approve');
        Route::patch('dashboard/submissions/{submission}/reject', [DashboardContentSubmissionController::class, 'reject'])->name('submissions.reject');

        // Email CRM (admin only)
        Route::get('dashboard/conversations', [DashboardConversationController::class, 'index'])->name('conversations.index');
        Route::get('dashboard/conversations/{conversation}', [DashboardConversationController::class, 'show'])->name('conversations.show');
        Route::post('dashboard/conversations/{conversation}/reply', [DashboardConversationController::class, 'reply'])->name('conversations.reply');
        Route::patch('dashboard/conversations/{conversation}/status', [DashboardConversationController::class, 'updateStatus'])->name('conversations.status');
        Route::delete('dashboard/conversations/{conversation}', [DashboardConversationController::class, 'destroy'])->name('conversations.destroy');

    });
});

// Support
Route::get('/support', [SupportTicketController::class, 'create'])->name('support.create');
Route::post('/support', [SupportTicketController::class, 'store'])->name('support.store');

// Legal
Route::get('/privacy', fn () => view('legal.privacy'))->name('legal.privacy');
Route::get('/terms', fn () => view('legal.terms'))->name('legal.terms');

// SendGrid Inbound Email Webhook
Route::post('/mail/inbound', [IncomingEmailController::class, 'handle'])->name('email.inbound');

require __DIR__.'/auth.php';
