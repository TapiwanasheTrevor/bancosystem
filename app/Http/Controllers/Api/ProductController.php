<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get product details with credit pricing options.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'creditPricings'])->find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category->name ?? 'Uncategorized',
                'catalog_type' => $product->catalog_type,
                'base_price' => $product->base_price,
                'image' => $product->image ? asset('images/products/' . $product->image) : null,
                'credit_options' => $product->creditPricings->map(function ($credit) {
                    // Use the actual installment_amount if set, otherwise calculate it
                    $installmentAmount = $credit->installment_amount 
                        ? (string)$credit->installment_amount 
                        : number_format($credit->final_price / $credit->months, 2);
                        
                    return [
                        'months' => $credit->months,
                        'interest' => $credit->interest,
                        'final_price' => $credit->final_price,
                        'installment_amount' => $installmentAmount,
                    ];
                }),
            ],
        ], 200);
    }
}

