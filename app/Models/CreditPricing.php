<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPricing extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'months', 'interest', 'final_price', 'installment_amount'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
