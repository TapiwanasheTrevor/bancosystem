<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'category_id', 'base_price', 'image', 'catalog_type'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creditPricings()
    {
        return $this->hasMany(CreditPricing::class);
    }
    
    public function deliveries()
    {
        return $this->hasMany(ProductDelivery::class);
    }
}
