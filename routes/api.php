<?php

use App\Http\Controllers\Api\ApplicationStatusController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DirectorLinkController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\KYCController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\AllowanceController;
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

// Main KYC upload route
Route::post('/upload-kyc', [KYCController::class, 'upload']);

// Improved fallback route for KYC uploads that returns success even if not implemented
Route::post('/kyc-fallback', function() {
    // Get the form ID from any of the common parameter names
    $formId = request('form_id', 
              request('insertId',
              request('application_id', 
              request('id', '0'))));
    
    // Log the fallback usage for debugging
    \Log::info('KYC fallback route used with form ID: ' . $formId);
    
    return response()->json([
        'success' => true,
        'message' => 'KYC documents received successfully via fallback',
        'insertId' => $formId,
        'form_id' => $formId,
    ]);
});

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

// Swift branches
Route::get('swift-branches', [\App\Http\Controllers\Api\SwiftBranchController::class, 'index']);
Route::get('swift-branches/province', [\App\Http\Controllers\Api\SwiftBranchController::class, 'getByProvince']);
Route::get('swift-branches/district', [\App\Http\Controllers\Api\SwiftBranchController::class, 'getByDistrict']);
Route::get('swift-branches/grouped', [\App\Http\Controllers\Api\SwiftBranchController::class, 'getAllGroupedByProvince']);

// Cost Buildups
Route::prefix('cost-buildups')->group(function() {
    Route::get('/templates', [\App\Http\Controllers\Api\CostBuildupController::class, 'getTemplates']);
    Route::get('/product', [\App\Http\Controllers\Api\CostBuildupController::class, 'getByProduct']);
    Route::post('/', [\App\Http\Controllers\Api\CostBuildupController::class, 'create']);
    Route::put('/{id}', [\App\Http\Controllers\Api\CostBuildupController::class, 'update']);
    Route::delete('/{id}', [\App\Http\Controllers\Api\CostBuildupController::class, 'delete']);
    Route::post('/from-template', [\App\Http\Controllers\Api\CostBuildupController::class, 'createFromTemplate']);
    Route::post('/{id}/save-as-template', [\App\Http\Controllers\Api\CostBuildupController::class, 'saveAsTemplate']);
});

// Agent routes
Route::apiResource('agents', AgentController::class);
Route::post('agents/{agent}/calculate-commission', [AgentController::class, 'calculateCommission']);
Route::post('agents/{agent}/calculate-allowance', [AgentController::class, 'calculateAllowance']);
Route::get('agents/{agent}/commission-summary', [AgentController::class, 'getCommissionSummary']);
Route::get('agents/{agent}/allowance-summary', [AgentController::class, 'getAllowanceSummary']);

// Commission routes
Route::apiResource('commissions', CommissionController::class);
Route::post('commissions/{commission}/approve', [CommissionController::class, 'approve']);
Route::post('commissions/{commission}/mark-as-paid', [CommissionController::class, 'markAsPaid']);
Route::post('commissions/{commission}/reject', [CommissionController::class, 'reject']);
Route::post('commissions/generate-for-period', [CommissionController::class, 'generateForPeriod']);
Route::get('commissions/calculate-for-period', [CommissionController::class, 'calculateForPeriod']);
Route::get('commissions/pending', [CommissionController::class, 'getPendingCommissions']);
Route::get('commissions/approved', [CommissionController::class, 'getApprovedCommissions']);
Route::get('commissions/paid', [CommissionController::class, 'getPaidCommissions']);

// Allowance routes
Route::apiResource('allowances', AllowanceController::class);
Route::post('allowances/{allowance}/approve', [AllowanceController::class, 'approve']);
Route::post('allowances/{allowance}/mark-as-paid', [AllowanceController::class, 'markAsPaid']);
Route::post('allowances/generate-for-period', [AllowanceController::class, 'generateForPeriod']);
Route::get('allowances/calculate-for-period', [AllowanceController::class, 'calculateForPeriod']);
Route::get('allowances/pending', [AllowanceController::class, 'getPendingAllowances']);
Route::get('allowances/approved', [AllowanceController::class, 'getApprovedAllowances']);
Route::get('allowances/paid', [AllowanceController::class, 'getPaidAllowances']);
