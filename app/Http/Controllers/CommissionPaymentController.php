<?php

namespace App\Http\Controllers;

use App\Models\CommissionPayment;
use App\Models\User;
use Illuminate\Http\Request;

class CommissionPaymentController extends Controller
{
    /**
     * Display a listing of commission payments
     */
    public function index(Request $request)
    {
        $agentId = $request->input('agent_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = CommissionPayment::with(['agent', 'processedBy']);
        
        if ($agentId) {
            $query->where('agent_id', $agentId);
        }
        
        if ($startDate && $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }
        
        $payments = $query->orderBy('payment_date', 'desc')->paginate(15);
        
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        
        return view('admin.commission_payments.index', compact('payments', 'agents', 'agentId', 'startDate', 'endDate'));
    }

    /**
     * Display the specified commission payment with details
     */
    public function show(string $id)
    {
        $payment = CommissionPayment::with([
            'agent', 
            'processedBy', 
            'paymentItems.commission.product'
        ])->findOrFail($id);
        
        return view('admin.commission_payments.show', compact('payment'));
    }

    /**
     * Generate PDF for the commission payment
     */
    public function generatePdf(string $id)
    {
        $payment = CommissionPayment::with([
            'agent', 
            'processedBy', 
            'paymentItems.commission.product'
        ])->findOrFail($id);
        
        $pdf = \PDF::loadView('admin.commission_payments.pdf', compact('payment'));
        
        return $pdf->download('commission-payment-' . $payment->payment_reference . '.pdf');
    }
}
