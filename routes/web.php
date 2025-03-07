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
    // Fetch all form submissions
    $agents = User::where('role', 'agent')->get();
    $newDocuments = Document::all();
    $processedDocuments = Document::where('status', 'processed')->get();
    return view('forms', compact('agents', 'newDocuments', 'processedDocuments'));
})->middleware(['auth', 'verified'])->name('forms');

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



