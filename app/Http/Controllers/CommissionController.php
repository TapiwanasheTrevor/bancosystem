<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\CommissionPaymentItem;
use App\Models\CommissionRate;
use App\Models\Form;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommissionController extends Controller
{
    /**
     * Display a listing of commissions
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $agentId = $request->input('agent_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = Commission::with(['agent', 'form', 'product']);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($agentId) {
            $query->where('agent_id', $agentId);
        }
        
        if ($startDate && $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        }
        
        $commissions = $query->orderBy('sale_date', 'desc')->paginate(20);
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        
        return view('admin.commissions.index', compact('commissions', 'agents', 'status', 'agentId', 'startDate', 'endDate'));
    }

    /**
     * Show the form for creating a new commission
     */
    public function create()
    {
        $agents = User::where('role', 'agent')
            ->where('status', 1)
            ->orderBy('name')
            ->get();
            
        $forms = Form::where('status', 'approved')
            ->orWhere('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $products = Product::orderBy('name')->get();
        $commissionRates = CommissionRate::where('is_active', true)->get();
        
        return view('admin.commissions.create', compact('agents', 'forms', 'products', 'commissionRates'));
    }

    /**
     * Store a newly created commission
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id',
            'product_id' => 'required|exists:products,id',
            'sale_amount' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $commission = new Commission();
        $commission->agent_id = $request->input('agent_id');
        $commission->form_id = $request->input('form_id');
        $commission->product_id = $request->input('product_id');
        $commission->sale_amount = $request->input('sale_amount');
        $commission->base_price = $request->input('base_price');
        $commission->commission_rate = $request->input('commission_rate');
        $commission->commission_amount = $request->input('base_price') * ($request->input('commission_rate') / 100);
        $commission->status = 'pending';
        $commission->sale_date = $request->input('sale_date');
        $commission->notes = $request->input('notes');
        $commission->save();
        
        return redirect()->route('commissions.show', $commission->id)
            ->with('success', 'Commission created successfully.');
    }

    /**
     * Display the specified commission
     */
    public function show(string $id)
    {
        $commission = Commission::with(['agent', 'form', 'product', 'approver'])->findOrFail($id);
        
        return view('admin.commissions.show', compact('commission'));
    }

    /**
     * Show the form for editing the commission
     */
    public function edit(string $id)
    {
        $commission = Commission::findOrFail($id);
        
        // Only allow editing of pending commissions
        if ($commission->status !== 'pending') {
            return redirect()->route('commissions.show', $commission->id)
                ->with('error', 'Only pending commissions can be edited.');
        }
        
        $agents = User::where('role', 'agent')
            ->where('status', 1)
            ->orderBy('name')
            ->get();
            
        $forms = Form::where('status', 'approved')
            ->orWhere('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $products = Product::orderBy('name')->get();
        
        return view('admin.commissions.edit', compact('commission', 'agents', 'forms', 'products'));
    }

    /**
     * Update the specified commission
     */
    public function update(Request $request, string $id)
    {
        $commission = Commission::findOrFail($id);
        
        // Only allow editing of pending commissions
        if ($commission->status !== 'pending') {
            return redirect()->route('commissions.show', $commission->id)
                ->with('error', 'Only pending commissions can be edited.');
        }
        
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id',
            'product_id' => 'required|exists:products,id',
            'sale_amount' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $commission->agent_id = $request->input('agent_id');
        $commission->form_id = $request->input('form_id');
        $commission->product_id = $request->input('product_id');
        $commission->sale_amount = $request->input('sale_amount');
        $commission->base_price = $request->input('base_price');
        $commission->commission_rate = $request->input('commission_rate');
        $commission->commission_amount = $request->input('base_price') * ($request->input('commission_rate') / 100);
        $commission->sale_date = $request->input('sale_date');
        $commission->notes = $request->input('notes');
        $commission->save();
        
        return redirect()->route('commissions.show', $commission->id)
            ->with('success', 'Commission updated successfully.');
    }

    /**
     * Approve the specified commission
     */
    public function approve(string $id)
    {
        $commission = Commission::findOrFail($id);
        
        if ($commission->status !== 'pending') {
            return redirect()->route('commissions.show', $commission->id)
                ->with('error', 'This commission is already ' . $commission->status . '.');
        }
        
        $commission->approve(Auth::id());
        
        return redirect()->route('commissions.show', $commission->id)
            ->with('success', 'Commission approved successfully.');
    }
    
    /**
     * Reject the specified commission
     */
    public function reject(Request $request, string $id)
    {
        $commission = Commission::findOrFail($id);
        
        if ($commission->status !== 'pending') {
            return redirect()->route('commissions.show', $commission->id)
                ->with('error', 'This commission is already ' . $commission->status . '.');
        }
        
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $commission->reject(Auth::id(), $request->input('rejection_reason'));
        
        return redirect()->route('commissions.show', $commission->id)
            ->with('success', 'Commission rejected successfully.');
    }

    /**
     * Show commission payment form
     */
    public function showPaymentForm()
    {
        $agents = User::where('role', 'agent')
            ->where('status', 1)
            ->orderBy('name')
            ->get();
            
        return view('admin.commissions.payment_form', compact('agents'));
    }
    
    /**
     * Process commission payment
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
            'payment_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $agentId = $request->input('agent_id');
        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');
        
        // Get commissions to be paid
        $commissions = Commission::where('agent_id', $agentId)
            ->where('status', 'approved')
            ->whereBetween('sale_date', [$periodStart, $periodEnd])
            ->get();
            
        if ($commissions->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No approved commissions found for this agent and period.')
                ->withInput();
        }
        
        // Create payment
        DB::beginTransaction();
        try {
            $totalAmount = $commissions->sum('commission_amount');
            
            $payment = new CommissionPayment();
            $payment->payment_reference = 'PAY-' . time();
            $payment->agent_id = $agentId;
            $payment->payment_date = $request->input('payment_date');
            $payment->period_start = $periodStart;
            $payment->period_end = $periodEnd;
            $payment->total_amount = $totalAmount;
            $payment->payment_method = $request->input('payment_method');
            $payment->transaction_id = $request->input('transaction_id');
            $payment->status = 'completed';
            $payment->processed_by = Auth::id();
            $payment->notes = $request->input('notes');
            $payment->save();
            
            // Create payment items and update commissions
            foreach ($commissions as $commission) {
                $paymentItem = new CommissionPaymentItem();
                $paymentItem->commission_payment_id = $payment->id;
                $paymentItem->commission_id = $commission->id;
                $paymentItem->amount = $commission->commission_amount;
                $paymentItem->save();
                
                $commission->markAsPaid($payment->payment_reference);
            }
            
            DB::commit();
            
            return redirect()->route('commission-payments.show', $payment->id)
                ->with('success', 'Commission payment processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error processing payment: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show agent commission report
     */
    public function agentReport(Request $request)
    {
        $agentId = $request->input('agent_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = Commission::with(['form', 'product']);
        
        if ($agentId) {
            $query->where('agent_id', $agentId);
        }
        
        if ($startDate && $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        }
        
        $commissions = $query->orderBy('sale_date', 'desc')->get();
        
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        $agent = $agentId ? User::find($agentId) : null;
        
        $totalCommission = $commissions->sum('commission_amount');
        $paidCommission = $commissions->where('status', 'paid')->sum('commission_amount');
        $pendingCommission = $commissions->where('status', 'pending')->sum('commission_amount');
        $approvedCommission = $commissions->where('status', 'approved')->sum('commission_amount');
        
        return view('admin.commissions.agent_report', compact(
            'commissions', 
            'agents', 
            'agent', 
            'agentId', 
            'startDate', 
            'endDate',
            'totalCommission',
            'paidCommission',
            'pendingCommission',
            'approvedCommission'
        ));
    }
    
    /**
     * Show team commission report
     */
    public function teamReport(Request $request)
    {
        $teamLeadId = $request->input('team_lead_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $teamLeads = User::where('is_team_lead', true)
            ->orderBy('name')
            ->get();
            
        $teamLead = $teamLeadId ? User::find($teamLeadId) : null;
        
        if ($teamLead) {
            $teamMembers = $teamLead->getAllTeamMembers();
            $teamMemberIds = $teamMembers->pluck('id')->toArray();
            
            $query = Commission::with(['agent', 'form', 'product'])
                ->whereIn('agent_id', $teamMemberIds);
                
            if ($startDate && $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            }
            
            $commissions = $query->orderBy('sale_date', 'desc')->get();
            
            $agentPerformance = [];
            foreach ($teamMembers as $member) {
                $memberCommissions = $commissions->where('agent_id', $member->id);
                $agentPerformance[$member->id] = [
                    'name' => $member->name,
                    'total' => $memberCommissions->sum('commission_amount'),
                    'count' => $memberCommissions->count(),
                    'paid' => $memberCommissions->where('status', 'paid')->sum('commission_amount'),
                    'pending' => $memberCommissions->where('status', 'pending')->sum('commission_amount'),
                    'approved' => $memberCommissions->where('status', 'approved')->sum('commission_amount'),
                ];
            }
            
            $totalCommission = $commissions->sum('commission_amount');
            $paidCommission = $commissions->where('status', 'paid')->sum('commission_amount');
            $pendingCommission = $commissions->where('status', 'pending')->sum('commission_amount');
            $approvedCommission = $commissions->where('status', 'approved')->sum('commission_amount');
            
            return view('admin.commissions.team_report', compact(
                'teamLeads',
                'teamLead',
                'startDate',
                'endDate',
                'commissions',
                'agentPerformance',
                'totalCommission',
                'paidCommission',
                'pendingCommission',
                'approvedCommission'
            ));
        } else {
            return view('admin.commissions.team_report', compact('teamLeads'));
        }
    }
    
    /**
     * Automatically calculate commissions for approved applications
     */
    public function calculateCommissions()
    {
        $formsWithoutCommission = Form::where('status', 'approved')
            ->whereDoesntHave('commissions')
            ->with(['agent', 'productDeliveries.product'])
            ->get();
            
        $count = 0;
        
        foreach ($formsWithoutCommission as $form) {
            if (!$form->agent) {
                continue;
            }
            
            $productDelivery = $form->productDeliveries->first();
            if (!$productDelivery || !$productDelivery->product) {
                continue;
            }
            
            $product = $productDelivery->product;
            
            // Get commission rate for the product or category
            $commissionRate = CommissionRate::where('is_active', true)
                ->where(function($query) use ($product) {
                    $query->where('product_id', $product->id)
                        ->orWhere('category_id', $product->category_id)
                        ->orWhere('applies_to', 'all');
                })
                ->orderBy(DB::raw("CASE 
                    WHEN product_id = {$product->id} THEN 1
                    WHEN category_id = {$product->category_id} THEN 2
                    WHEN applies_to = 'all' THEN 3
                    ELSE 4
                END"))
                ->first();
                
            if (!$commissionRate) {
                continue;
            }
            
            $commission = new Commission();
            $commission->agent_id = $form->agent_id;
            $commission->form_id = $form->id;
            $commission->product_id = $product->id;
            $commission->sale_amount = $product->base_price;
            $commission->base_price = $product->base_price;
            $commission->commission_rate = $commissionRate->rate_percentage;
            $commission->commission_amount = $product->base_price * ($commissionRate->rate_percentage / 100);
            $commission->status = 'pending';
            $commission->sale_date = now();
            $commission->save();
            
            $count++;
        }
        
        return redirect()->route('commissions.index')
            ->with('success', "Automatically calculated {$count} new commissions.");
    }
}
