<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/products', [AdminProductController::class, 'index'])
    ->middleware('auth')
    ->name('products');

Route::get('/applications', function () {
    return view('applications');
})->middleware(['auth', 'verified'])->name('applications');

Route::get('/forms', function () {
    return view('forms');
})->middleware(['auth', 'verified'])->name('forms');

Route::get('/agents', function () {
    return view('agents');
})->middleware(['auth', 'verified'])->name('agents');

Route::get('/settings', function () {
    return view('settings');
})->middleware(['auth', 'verified'])->name('settings');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Category Management
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::post('/categories/delete/{id}', [AdminCategoryController::class, 'destroy']);

    // Product Management
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::get('/products/list', [AdminProductController::class, 'list']);
    Route::post('/products/store', [AdminProductController::class, 'store']);
    Route::get('/products/{id}', [AdminProductController::class, 'show']);
    Route::post('/products/update/{id}', [AdminProductController::class, 'update']);
    Route::post('/products/delete/{id}', [AdminProductController::class, 'destroy']);

});

require __DIR__ . '/auth.php';



