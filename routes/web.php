<?php

use App\Http\Controllers\AdminAgentController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\ProductDeliveryController;
use App\Http\Controllers\ProfileController;
use App\Models\CreditPricing;
use App\Models\Document;
use App\Models\Form;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // Fetch base statistics
    $totalUsers = User::count();
    $activeApplications = Form::where('status', 'active')->count();

    // Calculate product stats from JSON data
    $forms = Form::whereNotNull('questionnaire_data')->get();
    $productStats = $forms->map(function ($form) {
        $data = json_decode($form->questionnaire_data, true);
        return [
            'product_name' => $data['selectedProduct']['product']['name'] ?? null,
            'credit_value' => $data['selectedProduct']['selectedCreditOption']['final_price'] ?? 0,
            'months' => $data['selectedProduct']['selectedCreditOption']['months'] ?? 0,
            'created_at' => $form->created_at
        ];
    })->filter(function ($stat) {
        return !is_null($stat['product_name']);
    });

    // Calculate totals
    $totalProducts = $productStats->pluck('product_name')->unique()->count();
    $totalCreditValue = $productStats->sum('credit_value');

    // Monthly applications data
    $monthlyApplications = $forms
        ->groupBy(function ($form) {
            return $form->created_at->format('n'); // Get month number
        })
        ->map(function ($monthForms) {
            return $monthForms->count();
        })
        ->sortKeys();

    $monthlyApplicationsLabels = $monthlyApplications->keys()->map(function ($month) {
        return date('F', mktime(0, 0, 0, $month, 1));
    });
    $monthlyApplicationsData = $monthlyApplications->values();

    // Product distribution data
    $productDistribution = $productStats
        ->groupBy('product_name')
        ->map(function ($products) {
            return $products->count();
        });

    $productDistributionLabels = $productDistribution->keys();
    $productDistributionData = $productDistribution->values();

    // Recent applications with decoded data
    $recentApplications = Form::latest()
        ->take(4)
        ->get()
        ->map(function ($form) {
            $data = json_decode($form->questionnaire_data, true);
            return (object)[
                'id' => $form->id,
                'form_name' => $data['selectedProduct']['product']['name'] ?? 'Unknown Product',
                'status' => $form->status,
                'status_color' => \App\Http\Controllers\AdminController::getStatusColor($form->status),
                'credit_value' => $data['selectedProduct']['selectedCreditOption']['final_price'] ?? 0,
                'months' => $data['selectedProduct']['selectedCreditOption']['months'] ?? 0
            ];
        });

    // Add additional analytics
    $averageCreditTerm = round($productStats->avg('months'), 1);
    $averageCreditValue = round($productStats->avg('credit_value'), 2);

    // System alerts with credit-specific checks
    $systemAlerts = \App\Http\Controllers\AdminController::generateSystemAlerts($productStats);

    return view('dashboard', compact(
        'totalUsers',
        'activeApplications',
        'totalProducts',
        'totalCreditValue',
        'monthlyApplicationsLabels',
        'monthlyApplicationsData',
        'productDistributionLabels',
        'productDistributionData',
        'recentApplications',
        'systemAlerts',
        'averageCreditTerm',
        'averageCreditValue'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/products', [AdminProductController::class, 'index'])
    ->middleware('auth')
    ->name('products');

Route::get('/applications', function () {
    return view('applications');
})->middleware(['auth', 'verified'])->name('applications');

Route::get('/forms', function () {
    // Get agents who are user type 'agent'
    $agents = User::where('role', 'agent')->get();
    
    // Add initials and document count for each agent
    foreach($agents as $agent) {
        $nameParts = explode(' ', $agent->name);
        $agent->initials = '';
        foreach($nameParts as $part) {
            if (strlen($part) > 0) {
                $agent->initials .= strtoupper(substr($part, 0, 1));
            }
        }
        $agent->documents_count = 0; // Default to 0 for now
    }
    
    // Fetch all documents
    $newDocuments = Document::all();
    $processedDocuments = Document::where('status', 'processed')->get();
    
    return view('forms', compact('agents', 'newDocuments', 'processedDocuments'));
})->middleware(['auth', 'verified'])->name('forms');

// Document routes - use web middleware for session auth
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/web-api/documents/new/{agentId}', [App\Http\Controllers\Api\DocumentController::class, 'getNewDocuments'])->name('documents.new');
    Route::get('/web-api/documents/processed/{agentId}', [App\Http\Controllers\Api\DocumentController::class, 'getProcessedDocuments'])->name('documents.processed');
    Route::get('/web-api/agents/search', [App\Http\Controllers\Api\DocumentController::class, 'searchAgents'])->name('agents.search');
});

// Removed duplicate route - using agents.index instead

Route::get('/settings', function () {
    return view('settings');
})->middleware(['auth', 'verified'])->name('settings');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Category Management - General route (legacy)
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    
    // Category Management - MicroBiz
    Route::get('/microbiz/categories', [AdminCategoryController::class, 'microbizCategories']);
    
    // Category Management - Hire Purchase
    Route::get('/hirepurchase/categories', [AdminCategoryController::class, 'hirePurchaseCategories']);
    
    // Common category operations
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

    // Agents management
    Route::get('/agents', [AdminAgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/create', [AdminAgentController::class, 'create'])->name('agents.create');
    Route::post('/agents', [AdminAgentController::class, 'store'])->name('agents.store');
    Route::get('/agents/{id}/edit', [AdminAgentController::class, 'edit'])->name('agents.edit');
    Route::put('/agents/{id}', [AdminAgentController::class, 'update'])->name('agents.update');
    Route::post('/agents/{id}/toggle-status', [AdminAgentController::class, 'toggleStatus'])->name('agents.toggle-status');
    Route::get('/agents/{id}/dashboard', [AdminAgentController::class, 'dashboard'])->name('agents.dashboard');
    Route::post('/agents/{id}/generate-link', [AdminAgentController::class, 'generateReferralLink'])->name('agents.generate-link');
    
    // Debug route for agent referrals
    Route::get('/debug/agent-referrals/{id}', function($id) {
        $agent = User::findOrFail($id);
        
        try {
            // Method 1: Direct query to see if column exists
            $referredUsers = DB::select('SELECT * FROM users WHERE referred_by = ?', [$agent->id]);
            
            // Method 2: Use Eloquent relationship
            $referrals = $agent->referrals;
            
            // Method 3: Test the new relationship for referred forms
            $referredForms = $agent->referredForms()->get();
            
            return [
                'success' => true,
                'agent' => $agent->only(['id', 'name', 'email', 'role']),
                'referredUsers' => $referredUsers,
                'userReferrals' => $referrals,
                'referredForms' => $referredForms,
                'referrals_count' => count($referredUsers)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    });

    //one link for them all
    Route::get('/download/{form}/{id}', [LoanApplicationController::class, 'downloadForm']);
    
    // Direct route for SSB form downloads
    Route::get('/download-ssb/{id}', function($id) {
        $controller = new \App\Http\Controllers\LoanApplicationController();
        return $controller->ssbLoanApplicationForm($id);
    });
    
    // Product Delivery Management
    Route::prefix('admin/deliveries')->name('admin.deliveries.')->group(function () {
        Route::get('/', [ProductDeliveryController::class, 'index'])->name('index');
        Route::get('/create', [ProductDeliveryController::class, 'create'])->name('create');
        Route::post('/', [ProductDeliveryController::class, 'store'])->name('store');
        Route::get('/{delivery}', [ProductDeliveryController::class, 'show'])->name('show');
        Route::post('/{delivery}/update-status', [ProductDeliveryController::class, 'updateStatus'])->name('update-status');
    });
});

require __DIR__ . '/auth.php';

// Director Form link - public route, no auth required
Route::get('/director-form/{token}', function ($token) {
    return view('director-form', ['token' => $token]);
})->name('director.form');

// Purchase Orders
Route::middleware(['auth'])->prefix('purchase-orders')->name('purchase-orders.')->group(function () {
    Route::get('/', [App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('create');
    Route::get('/create-from-form/{formId}', [App\Http\Controllers\PurchaseOrderController::class, 'createFromForm'])->name('create-from-form');
    Route::post('/', [App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\PurchaseOrderController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'update'])->name('update');
    Route::post('/{id}/change-status', [App\Http\Controllers\PurchaseOrderController::class, 'changeStatus'])->name('change-status');
    Route::delete('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/pdf', [App\Http\Controllers\PurchaseOrderController::class, 'generatePdf'])->name('pdf');
    Route::get('/create-from-application/{form}', [App\Http\Controllers\PurchaseOrderController::class, 'createFromApplication'])->name('create-from-application');
});

// Inventory Management
Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    // Inventory Items
    Route::get('/', [App\Http\Controllers\InventoryController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\InventoryController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\InventoryController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\InventoryController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\InventoryController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\InventoryController::class, 'update'])->name('update');
    Route::get('/{id}/adjust', [App\Http\Controllers\InventoryController::class, 'showAdjustForm'])->name('adjust');
    Route::post('/{id}/adjust', [App\Http\Controllers\InventoryController::class, 'processAdjustment'])->name('process-adjustment');
    Route::get('/search', [App\Http\Controllers\InventoryController::class, 'search'])->name('search');
    
    // Warehouse Management
    Route::get('/warehouses/manage', [App\Http\Controllers\InventoryController::class, 'manageWarehouses'])->name('warehouses.manage');
    Route::post('/warehouses', [App\Http\Controllers\InventoryController::class, 'storeWarehouse'])->name('warehouses.store');
    Route::put('/warehouses/{id}', [App\Http\Controllers\InventoryController::class, 'updateWarehouse'])->name('warehouses.update');
    Route::get('/warehouses/{id}/report', [App\Http\Controllers\InventoryController::class, 'warehouseReport'])->name('warehouses.report');
    
    // Inventory Transfers
    Route::get('/transfers/create', [App\Http\Controllers\InventoryController::class, 'showTransferForm'])->name('transfers.create');
    Route::post('/transfers', [App\Http\Controllers\InventoryController::class, 'processTransfer'])->name('transfers.store');
    Route::get('/transfers/{id}', [App\Http\Controllers\InventoryController::class, 'showTransfer'])->name('transfers.show');
    Route::post('/transfers/{id}/complete', [App\Http\Controllers\InventoryController::class, 'completeTransfer'])->name('transfers.complete');
    Route::post('/transfers/{id}/cancel', [App\Http\Controllers\InventoryController::class, 'cancelTransfer'])->name('transfers.cancel');
    
    // Goods Receiving Notes (GRN)
    Route::get('/grn/create', [App\Http\Controllers\InventoryController::class, 'showGrnForm'])->name('grn.create');
    Route::get('/grn/create-from-po/{poId}', [App\Http\Controllers\InventoryController::class, 'showGrnFormForPo'])->name('grn.create-from-po');
    Route::post('/grn', [App\Http\Controllers\InventoryController::class, 'processGrn'])->name('grn.store');
    Route::get('/grn/{id}', [App\Http\Controllers\InventoryController::class, 'showGrn'])->name('grn.show');
    Route::get('/grn/{id}/pdf', [App\Http\Controllers\InventoryController::class, 'generateGrnPdf'])->name('grn.pdf');
    
    // Product Reports
    Route::get('/products/{id}/report', [App\Http\Controllers\InventoryController::class, 'productReport'])->name('products.report');
});

// Commission Management
Route::middleware(['auth'])->prefix('commissions')->name('commissions.')->group(function () {
    Route::get('/', [App\Http\Controllers\CommissionController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\CommissionController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\CommissionController::class, 'store'])->name('store');
    Route::get('/payment/create', [App\Http\Controllers\CommissionController::class, 'showPaymentForm'])->name('payment.create');
    Route::post('/payment', [App\Http\Controllers\CommissionController::class, 'processPayment'])->name('payment.store');
    Route::get('/agent-report', [App\Http\Controllers\CommissionController::class, 'agentReport'])->name('agent-report');
    Route::get('/team-report', [App\Http\Controllers\CommissionController::class, 'teamReport'])->name('team-report');
    Route::post('/calculate', [App\Http\Controllers\CommissionController::class, 'calculateCommissions'])->name('calculate');
    
    // These routes should come after the specific routes to avoid conflicts
    Route::get('/{id}', [App\Http\Controllers\CommissionController::class, 'show'])->name('show')->where('id', '[0-9]+');
    Route::get('/{id}/edit', [App\Http\Controllers\CommissionController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
    Route::put('/{id}', [App\Http\Controllers\CommissionController::class, 'update'])->name('update')->where('id', '[0-9]+');
    Route::post('/{id}/approve', [App\Http\Controllers\CommissionController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
    Route::post('/{id}/reject', [App\Http\Controllers\CommissionController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
});

// Commission Payments
Route::middleware(['auth'])->prefix('commission-payments')->name('commission-payments.')->group(function () {
    Route::get('/', [App\Http\Controllers\CommissionPaymentController::class, 'index'])->name('index');
    Route::get('/{id}', [App\Http\Controllers\CommissionPaymentController::class, 'show'])->name('show');
    Route::get('/{id}/pdf', [App\Http\Controllers\CommissionPaymentController::class, 'generatePdf'])->name('pdf');
});

// Team Management
Route::middleware(['auth'])->prefix('teams')->name('teams.')->group(function () {
    Route::get('/', [App\Http\Controllers\TeamController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\TeamController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\TeamController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\TeamController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\TeamController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\TeamController::class, 'update'])->name('update');
    Route::post('/{id}/add-member', [App\Http\Controllers\TeamController::class, 'addMember'])->name('add-member');
    Route::post('/members/{id}/remove', [App\Http\Controllers\TeamController::class, 'removeMember'])->name('remove-member');
});

