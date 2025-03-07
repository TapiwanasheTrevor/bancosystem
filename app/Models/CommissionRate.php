<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRate extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'product_id',
        'category_id',
        'rate_percentage',
        'applies_to',
        'effective_from',
        'effective_to',
        'is_active',
    ];
    
    protected $casts = [
        'rate_percentage' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the product this rate applies to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the category this rate applies to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Check if this rate is currently effective
     */
    public function isEffective(): bool
    {
        $now = now()->startOfDay();
        
        if (!$this->is_active) {
            return false;
        }
        
        if ($now->lt($this->effective_from)) {
            return false;
        }
        
        if ($this->effective_to && $now->gt($this->effective_to)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get commission rates applicable for a product
     */
    public static function getRatesForProduct(int $productId, int $categoryId = null): array
    {
        $query = self::where('is_active', true)
            ->where(function($q) use ($productId, $categoryId) {
                $q->where('product_id', $productId)
                  ->orWhere(function($q2) use ($categoryId) {
                      if ($categoryId) {
                          $q2->where('category_id', $categoryId)
                             ->where('applies_to', 'category');
                      }
                  })
                  ->orWhere('applies_to', 'all');
            })
            ->where(function($q) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', now()->format('Y-m-d'));
            })
            ->where('effective_from', '<=', now()->format('Y-m-d'))
            ->orderBy('applies_to')
            ->get();
            
        return $query->toArray();
    }
}
