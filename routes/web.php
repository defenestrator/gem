<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\DashboardAnimalController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClassifiedController;
use App\Http\Controllers\DashboardClassifiedController;
use App\Http\Controllers\AnimalImportController;
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

// Helper function to load animals from JSON and sort by most recent first
$getAnimals = function() {
    $file = storage_path('app/public/animals.json');
    $mtime = filemtime($file);
    $key = 'animals_' . $mtime;
    $animals = Cache::remember($key, 30*60, function() use ($file) {
        $animals = json_decode(file_get_contents($file), true);
        if ($animals === null) {
            return [];
        }
        
        // Sort by Last_Update** in descending order (most recent first)
        usort($animals, function($a, $b) {
            $dateA = strtotime($a['Last_Update**'] ?? '1970-01-01');
            $dateB = strtotime($b['Last_Update**'] ?? '1970-01-01');
            return $dateB - $dateA;
        });
        
        return $animals;
    });
    return $animals;
};

    // Animal import routes
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard/animals/import', [AnimalImportController::class, 'showForm'])->name('dashboard.animals.import');
        Route::post('/dashboard/animals/import', [AnimalImportController::class, 'upload'])->name('dashboard.animals.import.upload');
    });

Route::get('/', function (Request $request) use ($getAnimals) {
    $sort = $request->query('sort', 'recent');
    $file = base_path('resources/js/animals.json');
    $mtime = filemtime($file);
    $responseKey = 'welcome_' . $sort . '_' . $mtime;
    $response = Cache::remember($responseKey, 30*60, function() use ($getAnimals, $sort) {
        $animals = $getAnimals();
        
        // Apply sorting based on filter
        if ($sort === 'price-low') {
            usort($animals, function($a, $b) {
                return $a['Price'] - $b['Price'];
            });
        } elseif ($sort === 'price-high') {
            usort($animals, function($a, $b) {
                return $b['Price'] - $a['Price'];
            });
        } elseif ($sort === 'date-new') {
            usort($animals, function($a, $b) {
                $dateA = strtotime($a['Dob'] ?? '1970-01-01');
                $dateB = strtotime($b['Dob'] ?? '1970-01-01');
                return $dateB - $dateA;
            });
        } elseif ($sort === 'category') {
            usort($animals, function($a, $b) {
                return strcmp($a['Category*'], $b['Category*']);
            });
        } elseif ($sort === 'category-desc') {
            usort($animals, function($a, $b) {
                return strcmp($b['Category*'], $a['Category*']);
            });
        }
        // 'recent' is the default, already sorted by Last_Update** from $getAnimals()
        
        return view('welcome', ['animals' => $animals, 'currentSort' => $sort])->render();
    });
    return response($response);
})->name('welcome');

Route::get('/categories', function () use ($getAnimals) {
    $file = base_path('resources/js/animals.json');
    $mtime = filemtime($file);
    $responseKey = 'categories_' . $mtime;
    $response = Cache::remember($responseKey, 30*60, function() use ($getAnimals) {
        $animals = $getAnimals();
        
        // Build categories array with animal counts for each category
        $categoryList = [
            'Corn Snakes',
            'Carpet Pythons',
            'Ball Pythons',
            'Reticulated Pythons',
            'Western Hognose'
        ];
        
        $categories = [];
        foreach ($categoryList as $category) {
            $count = count(array_filter($animals, function($animal) use ($category) {
                return $animal['Category*'] === $category && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
            }));
            $categories[$category] = $count;
        }
        return view('categories', ['categories' => $categories])->render();
    });
    return response($response);
})->name('categories');

Route::prefix('categories')->group(function () use ($getAnimals) {
    Route::get('/corn-snakes', function () use ($getAnimals) {
        $file = base_path('resources/js/animals.json');
        $mtime = filemtime($file);
        $responseKey = 'corn-snakes_' . $mtime;
        $response = Cache::remember($responseKey, 30*60, function() use ($getAnimals) {
            $animals = $getAnimals();
            $filtered = array_filter($animals, function($animal) {
                return $animal['Category*'] === 'Corn Snakes' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
            });
            return view('corn-snakes', ['animals' => array_values($filtered)])->render();
        });
        return response($response);
    })->name('categories.corn-snakes');

    Route::get('/carpet-pythons', function () use ($getAnimals) {
        $file = base_path('resources/js/animals.json');
        $mtime = filemtime($file);
        $responseKey = 'carpet-pythons_' . $mtime;
        $response = Cache::remember($responseKey, 30*60, function() use ($getAnimals) {
            $animals = $getAnimals();
            $filtered = array_filter($animals, function($animal) {
                return $animal['Category*'] === 'Carpet Pythons' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
            });
            return view('carpet-pythons', ['animals' => array_values($filtered)])->render();
        });
        return response($response);
    })->name('categories.carpet-pythons');

    Route::get('/ball-pythons', function () use ($getAnimals) {
        $file = base_path('resources/js/animals.json');
        $mtime = filemtime($file);
        $responseKey = 'ball-pythons_' . $mtime;
        $response = Cache::remember($responseKey, 30*60, function() use ($getAnimals) {
            $animals = $getAnimals();
            $filtered = array_filter($animals, function($animal) {
                return $animal['Category*'] === 'Ball Pythons' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
            });
            return view('ball-pythons', ['animals' => array_values($filtered)])->render();
        });
        return response($response);
    })->name('categories.ball-pythons');

    Route::get('/reticulated-pythons', function () use ($getAnimals) {
        $file = base_path('resources/js/animals.json');
        $mtime = filemtime($file);
        $responseKey = 'reticulated-pythons_' . $mtime;
        $response = Cache::remember($responseKey, 30*60, function() use ($getAnimals) {
            $animals = $getAnimals();
            $filtered = array_filter($animals, function($animal) {
                return $animal['Category*'] === 'Reticulated Pythons' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
            });
            return view('reticulated-pythons', ['animals' => array_values($filtered)])->render();
        });
        return response($response);
    })->name('categories.reticulated-pythons');

    Route::get('/western-hognose', function () use ($getAnimals) {
        $file = base_path('resources/js/animals.json');
        $mtime = filemtime($file);
        $responseKey = 'western-hognose_' . $mtime;
        $response = Cache::remember($responseKey, 30*60, function() use ($getAnimals) {
            $animals = $getAnimals();
            $filtered = array_filter($animals, function($animal) {
                return $animal['Category*'] === 'Western Hognose' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
            });
            return view('western-hognose', ['animals' => array_values($filtered)])->render();
        });
        return response($response);
    })->name('categories.western-hognose');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Classifieds Routes
Route::get('/classifieds', [ClassifiedController::class, 'index'])->name('classifieds.index');
Route::get('/classifieds/{classified:slug}', [ClassifiedController::class, 'show'])->name('classifieds.show');

// Animals Routes
Route::get('/animals', [AnimalController::class, 'index'])->name('animals.index');
Route::get('/animals/{animal:slug}', [AnimalController::class, 'show'])->name('animals.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Dashboard CRUD
    Route::name('dashboard.')->group(function () {
        Route::resource('dashboard/classifieds', DashboardClassifiedController::class)->middleware('verified');
        Route::resource('dashboard/animals', DashboardAnimalController::class)->middleware('verified');
        Route::delete('dashboard/media/{media}', [MediaController::class, 'destroy'])->name('dashboard.media.destroy')->middleware('verified');
    });
});

require __DIR__.'/auth.php';
