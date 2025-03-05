<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDelivery extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'form_id', 
        'product_id',
        'tracking_number',
        'status',
        'current_location',
        'status_notes',
        'estimated_delivery_date',
        'actual_delivery_date'
    ];
    
    protected $casts = [
        'estimated_delivery_date' => 'datetime',
        'actual_delivery_date' => 'datetime',
    ];
    
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function statusUpdates()
    {
        return $this->hasMany(DeliveryStatusUpdate::class)->orderBy('created_at', 'desc');
    }
    
    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
    
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'gray',
            'processing' => 'blue',
            'dispatched' => 'indigo',
            'in_transit' => 'purple',
            'at_station' => 'yellow',
            'out_for_delivery' => 'orange',
            'delivered' => 'green',
            'delayed' => 'red',
            'cancelled' => 'red',
            default => 'gray'
        };
    }
}
