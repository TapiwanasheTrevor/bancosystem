<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionPayment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'payment_reference',
        'agent_id',
        'payment_date',
        'period_start',
        'period_end',
        'total_amount',
        'payment_method',
        'transaction_id',
        'status',
        'processed_by',
        'notes',
    ];
    
    protected $casts = [
        'payment_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
    ];
    
    /**
     * Get the agent who received this payment
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    
    /**
     * Get the user who processed this payment
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    
    /**
     * Get the payment items in this payment
     */
    public function paymentItems(): HasMany
    {
        return $this->hasMany(CommissionPaymentItem::class);
    }
    
    /**
     * Get all commissions included in this payment
     */
    public function commissions()
    {
        return Commission::whereHas('paymentItems', function($query) {
            $query->where('commission_payment_id', $this->id);
        });
    }
    
    /**
     * Generate a PDF payment voucher for this payment
     */
    public function generatePdfVoucher()
    {
        // This would normally use a PDF library like DOMPDF
        return 'Payment voucher for ' . $this->payment_reference;
    }
}
