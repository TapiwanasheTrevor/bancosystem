<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'available_quantity',
        'reserved_quantity',
        'damaged_quantity',
        'unit_cost',
        'batch_number',
        'received_date',
        'expiry_date',
        'condition',
        'notes',
        'storage_location',
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'available_quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'damaged_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'received_date' => 'date',
        'expiry_date' => 'date',
    ];
    
    /**
     * Get the product this inventory item is for
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the warehouse this inventory item is in
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    /**
     * Get the inventory transactions for this item
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
    
    /**
     * Check if the inventory has available quantity
     */
    public function hasAvailable(int $requestedQuantity): bool
    {
        return $this->available_quantity >= $requestedQuantity;
    }
    
    /**
     * Reserve inventory from available quantity
     */
    public function reserve(int $quantity): bool
    {
        if (!$this->hasAvailable($quantity)) {
            return false;
        }
        
        $this->available_quantity -= $quantity;
        $this->reserved_quantity += $quantity;
        $this->save();
        
        return true;
    }
    
    /**
     * Unreserve inventory
     */
    public function unreserve(int $quantity): bool
    {
        if ($this->reserved_quantity < $quantity) {
            return false;
        }
        
        $this->reserved_quantity -= $quantity;
        $this->available_quantity += $quantity;
        $this->save();
        
        return true;
    }
    
    /**
     * Consume reserved inventory (use it)
     */
    public function consume(int $quantity): bool
    {
        if ($this->reserved_quantity < $quantity) {
            return false;
        }
        
        $this->reserved_quantity -= $quantity;
        $this->quantity -= $quantity;
        $this->save();
        
        return true;
    }
    
    /**
     * Add inventory
     */
    public function addInventory(int $quantity, float $unitCost = null): void
    {
        $this->quantity += $quantity;
        $this->available_quantity += $quantity;
        
        if ($unitCost !== null) {
            // Update unit cost with weighted average
            $totalValue = ($this->quantity - $quantity) * $this->unit_cost + $quantity * $unitCost;
            $this->unit_cost = $totalValue / $this->quantity;
        }
        
        $this->save();
    }
    
    /**
     * Mark inventory as damaged
     */
    public function markAsDamaged(int $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }
        
        $this->available_quantity -= $quantity;
        $this->damaged_quantity += $quantity;
        $this->save();
        
        return true;
    }
    
    /**
     * Get inventory value
     */
    public function getValue(): float
    {
        return $this->quantity * ($this->unit_cost ?? $this->product->base_price);
    }
    
    /**
     * Check if an item is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
    
    /**
     * Synchronize quantities if they don't match
     */
    public function synchronizeQuantities(): void
    {
        $expected = $this->available_quantity + $this->reserved_quantity + $this->damaged_quantity;
        
        if ($this->quantity !== $expected) {
            $this->quantity = $expected;
            $this->save();
        }
    }
}
