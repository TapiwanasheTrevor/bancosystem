<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all top-level categories for microbiz catalog.
     */
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->where('catalog_type', 'microbiz')
            ->withCount('children')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ], 200);
    }

    /**
     * Get all top-level categories for hire purchase catalog.
     */
    public function hirePurchaseCategories()
    {
        $categories = Category::whereNull('parent_id')
            ->where('catalog_type', 'hirepurchase')
            ->withCount('children')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ], 200);
    }

    /**
     * Get subcategories and products for a microbiz category.
     */
    public function show($id)
    {
        return $this->showCategory($id, 'microbiz');
    }

    /**
     * Get subcategories and products for a hire purchase category.
     */
    public function showHirePurchaseCategory($id)
    {
        return $this->showCategory($id, 'hirepurchase');
    }

    /**
     * Common method to fetch category details for any catalog type.
     */
    private function showCategory($id, $catalogType)
    {
        $category = Category::with(['children' => function($query) use ($catalogType) {
                $query->where('catalog_type', $catalogType);
            }, 'products' => function($query) use ($catalogType) {
                $query->where('catalog_type', $catalogType);
            }, 'products.creditPricings'])
            ->where('catalog_type', $catalogType)
            ->find($id);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'catalog_type' => $category->catalog_type,
                'subcategories' => $category->children,
                'products' => $category->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'base_price' => $product->base_price,
                        'image' => $product->image ? asset('images/products/' . $product->image) : null,
                        'credit_options' => $product->creditPricings->map(function ($credit) {
                            return [
                                'months' => $credit->months,
                                'interest' => $credit->interest,
                                'final_price' => $credit->final_price,
                                'installment_amount' => number_format($credit->final_price / $credit->months, 2),
                            ];
                        }),
                    ];
                }),
            ],
        ], 200);
    }
}
