<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommissionController extends Controller
{
    /**
     * Calculate commissions for a specific agent in a given period
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateForPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $agentId = $request->input('agent_id');
        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');

        // Get agent
        $agent = User::findOrFail($agentId);
        
        // Get unpaid/approved commissions for the agent within the period
        $commissions = Commission::with(['product', 'form'])
            ->where('agent_id', $agentId)
            ->where('status', 'approved')
            ->whereDate('sale_date', '>=', $periodStart)
            ->whereDate('sale_date', '<=', $periodEnd)
            ->orderBy('sale_date', 'desc')
            ->get();
            
        if ($commissions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No approved commissions found for this agent in the selected period.'
            ]);
        }
        
        $total = 0;
        $formattedCommissions = [];
        
        foreach ($commissions as $commission) {
            $total += $commission->commission_amount;
            
            $formattedCommissions[] = [
                'id' => $commission->id,
                'product_name' => $commission->product->name ?? 'Unknown Product',
                'date' => $commission->sale_date->format('M d, Y'),
                'sale_amount' => (float) $commission->sale_amount,
                'commission_amount' => (float) $commission->commission_amount,
                'commission_rate' => $commission->commission_rate,
            ];
        }
        
        return response()->json([
            'success' => true,
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
            ],
            'period' => [
                'start' => $periodStart,
                'end' => $periodEnd,
            ],
            'commissions' => $formattedCommissions,
            'total' => $total,
            'count' => count($formattedCommissions)
        ]);
    }
}