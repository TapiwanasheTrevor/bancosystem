<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionPaymentItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'commission_payment_id',
        'commission_id',
        'amount',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
    ];
    
    /**
     * Get the payment this item belongs to
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(CommissionPayment::class, 'commission_payment_id');
    }
    
    /**
     * Get the commission this payment item is for
     */
    public function commission(): BelongsTo
    {
        return $this->belongsTo(Commission::class);
    }
}
