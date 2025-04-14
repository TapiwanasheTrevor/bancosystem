<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AllowanceController extends Controller
{
    public function index()
    {
        $allowances = Allowance::with('agent')->get();
        return response()->json([
            'status' => 'success',
            'data' => $allowances
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:agents,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|date_format:Y-m',
            'status' => ['required', 'in:pending,approved,paid']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $allowance = Allowance::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $allowance
        ], 201);
    }

    public function show(Allowance $allowance)
    {
        return response()->json([
            'status' => 'success',
            'data' => $allowance->load('agent')
        ]);
    }

    public function update(Request $request, Allowance $allowance)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:pending,approved,paid'],
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $allowance->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $allowance
        ]);
    }

    public function destroy(Allowance $allowance)
    {
        $allowance->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Allowance deleted successfully'
        ]);
    }

    public function approve(Allowance $allowance)
    {
        $allowance->approve();

        return response()->json([
            'status' => 'success',
            'data' => $allowance
        ]);
    }

    public function markAsPaid(Allowance $allowance)
    {
        $allowance->markAsPaid();

        return response()->json([
            'status' => 'success',
            'data' => $allowance
        ]);
    }

    public function generateForPeriod(Request $request)
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

        $allowances = Allowance::generateForPeriod($request->period);

        return response()->json([
            'status' => 'success',
            'data' => $allowances
        ]);
    }

    public function calculateForPeriod(Request $request)
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

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    public function getAgentAllowances(Request $request, Agent $agent)
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

        $allowances = Allowance::where('agent_id', $agent->id)
            ->where('period', $request->period)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $allowances
        ]);
    }

    public function getPendingAllowances()
    {
        $allowances = Allowance::where('status', 'pending')
            ->with('agent')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $allowances
        ]);
    }

    public function getApprovedAllowances()
    {
        $allowances = Allowance::where('status', 'approved')
            ->with('agent')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $allowances
        ]);
    }

    public function getPaidAllowances()
    {
        $allowances = Allowance::where('status', 'paid')
            ->with('agent')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $allowances
        ]);
    }
}
