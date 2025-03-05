<?php

use App\Http\Controllers\Api\ApplicationStatusController;
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

// Form schema routes
Route::get('/forms/{formType}', [FormController::class, 'show']);
Route::get('/forms', [FormController::class, 'listForms']);

// Microbiz catalog API endpoints
Route::get('/categories', [CategoryController::class, 'index']); // Get all top categories for microbiz
Route::get('/categories/{id}', [CategoryController::class, 'show']); // Get subcategories & products for microbiz

// Hire Purchase catalog API endpoints
Route::get('/hirepurchase/categories', [CategoryController::class, 'hirePurchaseCategories']); // Get all top categories for hire purchase
Route::get('/hirepurchase/categories/{id}', [CategoryController::class, 'showHirePurchaseCategory']); // Get subcategories & products for hire purchase

// Product details API
Route::get('/products/{id}', [ProductController::class, 'show']); // Get product details

Route::post('/submit-form', [FormSubmissionController::class, 'submit'])->name('api.form.submit');

Route::get('/applications/{formType}', [FormSubmissionController::class, 'listFormSubmissions']);

Route::post('/upload-kyc', [KYCController::class, 'upload']);

// Application status tracking
Route::post('/check-application-status', [ApplicationStatusController::class, 'checkStatus']);

// Form management
Route::post('/forms/{id}/status', [\App\Http\Controllers\Api\FormStatusController::class, 'updateStatus']);
Route::post('/forms/batch-status', [\App\Http\Controllers\Api\FormStatusController::class, 'batchUpdateStatus']);
Route::delete('/forms/{id}', [\App\Http\Controllers\Api\FormStatusController::class, 'deleteForm']);

// Document management
Route::middleware('auth:sanctum')->group(function () {
    // Document uploads by agents
    Route::post('/documents/upload', [\App\Http\Controllers\Api\DocumentController::class, 'upload']);
    Route::get('/documents/new/{agentId}', [\App\Http\Controllers\Api\DocumentController::class, 'getNewDocuments']);
    Route::get('/documents/processed/{agentId}', [\App\Http\Controllers\Api\DocumentController::class, 'getProcessedDocuments']);
    Route::post('/documents/{id}/process', [\App\Http\Controllers\Api\DocumentController::class, 'markProcessed']);
    
    // Agent search for document management
    Route::get('/agents/search', [\App\Http\Controllers\Api\DocumentController::class, 'searchAgents']);
});

// Delivery tracking routes
Route::post('/track-delivery', [\App\Http\Controllers\Api\DeliveryTrackingController::class, 'trackByNumber']);
Route::post('/user-deliveries', [\App\Http\Controllers\Api\DeliveryTrackingController::class, 'getUserDeliveries']);
Route::post('/delivery-details', [\App\Http\Controllers\Api\DeliveryTrackingController::class, 'getDeliveryDetails']);
