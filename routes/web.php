<?php

use App\Http\Controllers\AdminAgentController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\ProfileController;
use App\Models\CreditPricing;
use App\Models\Document;
use App\Models\Form;
use App\Models\Product;
use App\Models\User;
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

    // Agents controller
    Route::get('/agents', [AdminAgentController::class, 'index']);

    //one link for them all
    Route::get('/download/{form}/{id}', [LoanApplicationController::class, 'downloadForm']);
});

require __DIR__ . '/auth.php';



