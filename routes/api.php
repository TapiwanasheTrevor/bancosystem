<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\KYCController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/forms/{formType}', [FormController::class, 'show']);

// Category navigation API
Route::get('/categories', [CategoryController::class, 'index']); // Get all top categories

Route::get('/categories/{id}', [CategoryController::class, 'show']); // Get subcategories & products

// Product details API
Route::get('/products/{id}', [ProductController::class, 'show']); // Get product details

Route::post('/submit-form', [FormSubmissionController::class, 'submit'])->name('api.form.submit');

Route::get('/applications/{formType}', [FormSubmissionController::class, 'listFormSubmissions']);

Route::post('/upload-kyc', [KYCController::class, 'upload']);
