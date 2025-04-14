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
    
    /**
     * Get the associated product from the form if product_id is not set explicitly
     */
    public function getProductIdAttribute($value)
    {
        if (!$value && $this->form_id) {
            $form = $this->form;
            if ($form && isset($form->form_values['product_id'])) {
                return $form->form_values['product_id'];
            }
        }
        return $value;
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
