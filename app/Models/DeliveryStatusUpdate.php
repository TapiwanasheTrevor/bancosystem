<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryStatusUpdate extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'product_delivery_id',
        'status',
        'location',
        'notes'
    ];
    
    public function delivery()
    {
        return $this->belongsTo(ProductDelivery::class, 'product_delivery_id');
    }
    
    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}
