<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SwiftBranch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SwiftBranchController extends Controller
{
    /**
     * Get all branches
     */
    public function index(): JsonResponse
    {
        $branches = SwiftBranch::where('is_active', true)->get();
        
        return response()->json([
            'success' => true,
            'branches' => $branches
        ]);
    }
    
    /**
     * Get branches by province
     */
    public function getByProvince(Request $request): JsonResponse
    {
        $request->validate([
            'province' => 'required|string'
        ]);
        
        $branches = SwiftBranch::getByProvince($request->province);
        
        return response()->json([
            'success' => true,
            'branches' => $branches
        ]);
    }
    
    /**
     * Get branches by district
     */
    public function getByDistrict(Request $request): JsonResponse
    {
        $request->validate([
            'district' => 'required|string'
        ]);
        
        $branches = SwiftBranch::getByDistrict($request->district);
        
        return response()->json([
            'success' => true,
            'branches' => $branches
        ]);
    }
    
    /**
     * Get all branches grouped by province
     */
    public function getAllGroupedByProvince(): JsonResponse
    {
        $groupedBranches = SwiftBranch::getAllGroupedByProvince();
        
        return response()->json([
            'success' => true,
            'provinces' => array_keys($groupedBranches),
            'branches' => $groupedBranches
        ]);
    }
}