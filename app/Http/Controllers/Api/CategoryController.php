<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all top-level categories.
     */
    public function index()
    {
        $categories = Category::whereNull('parent_id')->withCount('children')->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ], 200);
    }

    /**
     * Get subcategories and products for a category.
     */
    public function show($id)
    {
        $category = Category::with('children', 'products.creditPricings')->find($id);

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
                            ];
                        }),
                    ];
                }),
            ],
        ], 200);
    }
}
