<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CostBuildup;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CostBuildupController extends Controller
{
    /**
     * Get all cost buildup templates
     */
    public function getTemplates(): JsonResponse
    {
        $templates = CostBuildup::getTemplates();
        
        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }
    
    /**
     * Get cost buildup by product ID
     */
    public function getByProduct(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $costBuildups = CostBuildup::where('product_id', $request->product_id)
            ->where('is_active', true)
            ->get();
        
        $product = Product::find($request->product_id);
        
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'base_price' => $product->base_price
            ],
            'cost_buildups' => $costBuildups
        ]);
    }
    
    /**
     * Create a new cost buildup
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'base_cost' => 'required|numeric|min:0',
            'variables' => 'required|array',
            'variables.*.name' => 'required|string',
            'variables.*.type' => 'required|in:fixed,percentage,multiplier',
            'variables.*.value' => 'required|numeric',
            'variables.*.description' => 'nullable|string',
            'template_name' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $costBuildup = CostBuildup::create([
            'product_id' => $request->product_id,
            'base_cost' => $request->base_cost,
            'variables' => $request->variables,
            'final_price' => 0, // Will be calculated
            'template_name' => $request->template_name,
            'is_active' => true,
            'created_by' => auth()->user()->name ?? 'System'
        ]);
        
        $costBuildup->calculateFinalPrice();
        
        return response()->json([
            'success' => true,
            'cost_buildup' => $costBuildup
        ], 201);
    }
    
    /**
     * Update a cost buildup
     */
    public function update(Request $request, $id): JsonResponse
    {
        $costBuildup = CostBuildup::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'base_cost' => 'nullable|numeric|min:0',
            'variables' => 'nullable|array',
            'variables.*.name' => 'required|string',
            'variables.*.type' => 'required|in:fixed,percentage,multiplier',
            'variables.*.value' => 'required|numeric',
            'variables.*.description' => 'nullable|string',
            'template_name' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        if (isset($request->base_cost)) {
            $costBuildup->base_cost = $request->base_cost;
        }
        
        if (isset($request->variables)) {
            $costBuildup->variables = $request->variables;
        }
        
        if (isset($request->template_name)) {
            $costBuildup->template_name = $request->template_name;
        }
        
        if (isset($request->is_active)) {
            $costBuildup->is_active = $request->is_active;
        }
        
        $costBuildup->calculateFinalPrice();
        
        return response()->json([
            'success' => true,
            'cost_buildup' => $costBuildup
        ]);
    }
    
    /**
     * Delete a cost buildup
     */
    public function delete($id): JsonResponse
    {
        $costBuildup = CostBuildup::findOrFail($id);
        $costBuildup->is_active = false;
        $costBuildup->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Cost buildup deleted successfully'
        ]);
    }
    
    /**
     * Create a cost buildup from a template
     */
    public function createFromTemplate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'template_id' => 'required|exists:cost_buildups,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $costBuildup = CostBuildup::createFromTemplate(
            $request->product_id,
            $request->template_id
        );
        
        return response()->json([
            'success' => true,
            'cost_buildup' => $costBuildup
        ], 201);
    }
    
    /**
     * Save a cost buildup as a template
     */
    public function saveAsTemplate(Request $request, $id): JsonResponse
    {
        $costBuildup = CostBuildup::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $costBuildup->saveAsTemplate(
            $request->template_name,
            auth()->user()->name ?? 'System'
        );
        
        return response()->json([
            'success' => true,
            'cost_buildup' => $costBuildup
        ]);
    }
}