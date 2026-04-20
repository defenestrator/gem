<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    $animals = json_decode(file_get_contents(base_path('resources/js/animals.json')), true);
    if ($animals === null) {
        $animals = [];
    }
    return view('welcome', ['animals' => $animals]);
})->name('welcome');

Route::get('/categories', function () {
    return view('categories');
})->name('categories');

Route::prefix('categories')->group(function () {
    Route::get('/corn-snakes', function () {
        $animals = json_decode(file_get_contents(base_path('resources/js/animals.json')), true);
        if ($animals === null) {
            $animals = [];
        }
        $filtered = array_filter($animals, function($animal) {
            return $animal['Category*'] === 'Corn Snakes' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
        });
        return view('corn-snakes', ['animals' => array_values($filtered)]);
    })->name('categories.corn-snakes');

    Route::get('/carpet-pythons', function () {
        $animals = json_decode(file_get_contents(base_path('resources/js/animals.json')), true);
        if ($animals === null) {
            $animals = [];
        }
        $filtered = array_filter($animals, function($animal) {
            return $animal['Category*'] === 'Carpet Pythons' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
        });
        return view('carpet-pythons', ['animals' => array_values($filtered)]);
    })->name('categories.carpet-pythons');

    Route::get('/ball-pythons', function () {
        $animals = json_decode(file_get_contents(base_path('resources/js/animals.json')), true);
        if ($animals === null) {
            $animals = [];
        }
        $filtered = array_filter($animals, function($animal) {
            return $animal['Category*'] === 'Ball Pythons' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
        });
        return view('ball-pythons', ['animals' => array_values($filtered)]);
    })->name('categories.ball-pythons');

    Route::get('/reticulated-pythons', function () {
        $animals = json_decode(file_get_contents(base_path('resources/js/animals.json')), true);
        if ($animals === null) {
            $animals = [];
        }
        $filtered = array_filter($animals, function($animal) {
            return $animal['Category*'] === 'Reticulated Pythons' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
        });
        return view('reticulated-pythons', ['animals' => array_values($filtered)]);
    })->name('categories.reticulated-pythons');

    Route::get('/western-hognose', function () {
        $animals = json_decode(file_get_contents(base_path('resources/js/animals.json')), true);
        if ($animals === null) {
            $animals = [];
        }
        $filtered = array_filter($animals, function($animal) {
            return $animal['Category*'] === 'Western Hognose' && $animal['State'] === 'For Sale' && $animal['Enabled'] === 'Active';
        });
        return view('western-hognose', ['animals' => array_values($filtered)]);
    })->name('categories.western-hognose');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
