<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commission extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'agent_id',
        'form_id',
        'product_id',
        'sale_amount',
        'base_price',
        'commission_rate',
        'commission_amount',
        'status',
        'sale_date',
        'approval_date',
        'payment_date',
        'payment_reference',
        'approved_by',
        'notes',
    ];
    
    protected $casts = [
        'sale_amount' => 'decimal:2',
        'base_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'sale_date' => 'date',
        'approval_date' => 'date',
        'payment_date' => 'date',
    ];
    
    /**
     * Get the agent who earned this commission
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    
    /**
     * Get the form this commission is related to
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
    
    /**
     * Get the product this commission is for
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the user who approved this commission
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get the payment items for this commission
     */
    public function paymentItems(): HasMany
    {
        return $this->hasMany(CommissionPaymentItem::class);
    }
    
    /**
     * Calculate commission amount
     */
    public function calculateCommission(): void
    {
        $this->commission_amount = $this->base_price * ($this->commission_rate / 100);
        $this->save();
    }
    
    /**
     * Approve the commission
     */
    public function approve(int $approverId): void
    {
        $this->status = 'approved';
        $this->approved_by = $approverId;
        $this->approval_date = now();
        $this->save();
    }
    
    /**
     * Mark the commission as paid
     */
    public function markAsPaid(string $paymentReference): void
    {
        $this->status = 'paid';
        $this->payment_reference = $paymentReference;
        $this->payment_date = now();
        $this->save();
    }
    
    /**
     * Reject the commission
     */
    public function reject(int $approverId, string $reason = null): void
    {
        $this->status = 'rejected';
        $this->approved_by = $approverId;
        $this->approval_date = now();
        
        if ($reason) {
            $this->notes = $reason;
        }
        
        $this->save();
    }
    
    /**
     * Scope query to only include pending commissions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope query to only include approved commissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope query to only include paid commissions
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
    
    /**
     * Scope query to only include rejected commissions
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
    
    /**
     * Scope query to only include commissions for a specific agent
     */
    public function scopeForAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }
    
    /**
     * Scope query to only include commissions for a specific period
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }
}
