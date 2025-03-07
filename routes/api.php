<?php

use App\Http\Controllers\Api\ApplicationStatusController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DirectorLinkController;
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
Route::post('/submit-form-with-files', [FormSubmissionController::class, 'submitWithFiles'])->name('api.form.submit-with-files');

Route::get('/applications/{formType}', [FormSubmissionController::class, 'listFormSubmissions']);

Route::post('/upload-kyc', [KYCController::class, 'upload']);

// Application status tracking
Route::post('/check-application-status', [ApplicationStatusController::class, 'checkStatus']);

// Form management
Route::post('/forms/{id}/status', [\App\Http\Controllers\Api\FormStatusController::class, 'updateStatus']);
Route::post('/forms/batch-status', [\App\Http\Controllers\Api\FormStatusController::class, 'batchUpdateStatus']);
Route::delete('/forms/{id}', [\App\Http\Controllers\Api\FormStatusController::class, 'deleteForm']);

// Director links for forms
Route::post('/director-links/generate', [DirectorLinkController::class, 'generate']);
Route::get('/director-links/{token}', [DirectorLinkController::class, 'getDirectorFormData']);

// Document management API endpoints
Route::middleware(['auth:sanctum'])->group(function () {
    // Document upload
    Route::post('/documents/upload', [DocumentController::class, 'upload']);
    
    // Get documents by agent
    Route::get('/documents/new/{agentId}', [DocumentController::class, 'getNewDocuments']);
    Route::get('/documents/processed/{agentId}', [DocumentController::class, 'getProcessedDocuments']);
    
    // Process document
    Route::post('/documents/{id}/process', [DocumentController::class, 'markProcessed']);
    
    // Agent search
    Route::get('/agents/search', [DocumentController::class, 'searchAgents']);
    
    // Get agent clients
    Route::get('/agents/{agentId}/clients', [DocumentController::class, 'getAgentClients']);
});
Route::post('/director-links/{token}/submit', [DirectorLinkController::class, 'submitDirectorData']);

// Document management
Route::middleware('auth:sanctum')->group(function () {
    // Document uploads by agents
    Route::post('/documents/upload', [DocumentController::class, 'upload']);
    Route::get('/documents/new/{agentId}', [DocumentController::class, 'getNewDocuments']);
    Route::get('/documents/processed/{agentId}', [DocumentController::class, 'getProcessedDocuments']);
    Route::post('/documents/{id}/process', [DocumentController::class, 'markProcessed']);
    
    // Agent search for document management
    Route::get('/agents/search', [DocumentController::class, 'searchAgents']);
    
    // Get clients for an agent
    Route::get('/agents/{id}/clients', [DocumentController::class, 'getAgentClients']);
});

// Delivery tracking routes
Route::post('/track-delivery', [\App\Http\Controllers\Api\DeliveryTrackingController::class, 'trackByNumber']);
Route::post('/user-deliveries', [\App\Http\Controllers\Api\DeliveryTrackingController::class, 'getUserDeliveries']);
Route::post('/delivery-details', [\App\Http\Controllers\Api\DeliveryTrackingController::class, 'getDeliveryDetails']);

// Inventory API endpoints
Route::middleware('auth:sanctum')->prefix('inventory')->group(function() {
    // Available items in a warehouse
    Route::get('/warehouse/{id}/available-items', function($id) {
        $items = \App\Models\InventoryItem::with('product')
            ->where('warehouse_id', $id)
            ->where('available_quantity', '>', 0)
            ->orderBy('product_id')
            ->get();
            
        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    });
});

// Commission API endpoints
Route::middleware('auth:sanctum')->prefix('commissions')->group(function() {
    // Calculate commissions for payment
    Route::get('/calculate', [\App\Http\Controllers\Api\CommissionController::class, 'calculateForPayment']);
});
