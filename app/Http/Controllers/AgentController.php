<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::all();
        return response()->json([
            'status' => 'success',
            'data' => $agents
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['field', 'online', 'office'])],
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:agents,email',
            'employee_number' => 'required|string|unique:agents,employee_number',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $agent = Agent::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $agent
        ], 201);
    }

    public function show(Agent $agent)
    {
        return response()->json([
            'status' => 'success',
            'data' => $agent
        ]);
    }

    public function update(Request $request, Agent $agent)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => ['sometimes', Rule::in(['field', 'online', 'office'])],
            'phone' => 'sometimes|string|max:20',
            'email' => ['sometimes', 'email', Rule::unique('agents')->ignore($agent->id)],
            'employee_number' => ['sometimes', 'string', Rule::unique('agents')->ignore($agent->id)],
            'commission_rate' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $agent->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $agent
        ]);
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Agent deleted successfully'
        ]);
    }

    public function calculateCommission(Request $request, Agent $agent)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $commission = $agent->calculateCommission($request->amount);

        return response()->json([
            'status' => 'success',
            'data' => [
                'amount' => $request->amount,
                'commission_rate' => $agent->commission_rate,
                'commission_amount' => $commission
            ]
        ]);
    }

    public function calculateAllowance(Request $request, Agent $agent)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $allowance = $agent->calculateAllowance($request->days);

        return response()->json([
            'status' => 'success',
            'data' => [
                'days' => $request->days,
                'allowance_amount' => $allowance
            ]
        ]);
    }

    public function getCommissionSummary(Request $request, Agent $agent)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|date_format:Y-m'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $summary = Commission::calculateForPeriod($request->period);
        $agentSummary = collect($summary)->firstWhere('agent_id', $agent->id);

        return response()->json([
            'status' => 'success',
            'data' => $agentSummary
        ]);
    }

    public function getAllowanceSummary(Request $request, Agent $agent)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|date_format:Y-m'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $summary = Allowance::calculateForPeriod($request->period);
        $agentSummary = collect($summary)->firstWhere('agent_id', $agent->id);

        return response()->json([
            'status' => 'success',
            'data' => $agentSummary
        ]);
    }
}
