<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'location',
        'contact_person',
        'contact_number',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the inventory items in this warehouse
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
    
    /**
     * Get the inventory transactions for this warehouse
     */
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
    
    /**
     * Get the good receiving notes for this warehouse
     */
    public function goodsReceivingNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivingNote::class);
    }
    
    /**
     * Get inventory transfers from this warehouse
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'source_warehouse_id');
    }
    
    /**
     * Get inventory transfers to this warehouse
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'destination_warehouse_id');
    }
    
    /**
     * Get the total inventory value in this warehouse
     */
    public function getTotalInventoryValue(): float
    {
        return $this->inventoryItems()
            ->join('products', 'inventory_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventory_items.quantity * COALESCE(inventory_items.unit_cost, products.base_price)) as total_value')
            ->value('total_value') ?? 0;
    }
    
    /**
     * Get the total product count in this warehouse
     */
    public function getProductCount(): int
    {
        return $this->inventoryItems()->distinct('product_id')->count('product_id');
    }
}
