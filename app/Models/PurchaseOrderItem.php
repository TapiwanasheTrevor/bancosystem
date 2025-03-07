<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'quantity_fulfilled',
        'notes',
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'quantity_fulfilled' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];
    
    /**
     * Get the purchase order this item belongs to
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
    
    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the GRN items related to this purchase order item
     */
    public function grnItems(): HasMany
    {
        return $this->hasMany(GrnItem::class);
    }
    
    /**
     * Get the inventory transactions for this item
     */
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
    
    /**
     * Calculate the fulfillment percentage
     */
    public function getFulfillmentPercentageAttribute(): int
    {
        if ($this->quantity === 0) {
            return 0;
        }
        
        return min(100, (int) round(($this->quantity_fulfilled / $this->quantity) * 100));
    }
    
    /**
     * Update the status based on fulfillment
     */
    public function updateStatus(): void
    {
        if ($this->quantity_fulfilled >= $this->quantity) {
            $this->status = 'fulfilled';
        } elseif ($this->quantity_fulfilled > 0) {
            $this->status = 'partial';
        }
        
        $this->save();
        
        // Update parent purchase order status
        $this->purchaseOrder->updateStatus();
    }
    
    /**
     * Calculate total price
     */
    public function calculateTotal(): void
    {
        $this->total_price = $this->unit_price * $this->quantity;
        $this->save();
    }
}
