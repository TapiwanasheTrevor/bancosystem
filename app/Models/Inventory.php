<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'quantity',
        'cost_price',
        'supplier',
        'location',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cost_price' => 'decimal:2'
    ];

    public function adjustStock(int $quantity, string $reason): void
    {
        $this->quantity += $quantity;
        $this->save();

        // Log the stock adjustment
        StockAdjustment::create([
            'inventory_id' => $this->id,
            'quantity' => $quantity,
            'reason' => $reason,
            'previous_quantity' => $this->quantity - $quantity,
            'new_quantity' => $this->quantity
        ]);
    }

    public function markAsDamaged(int $quantity, string $reason): void
    {
        if ($this->quantity < $quantity) {
            throw new \Exception('Not enough stock to mark as damaged');
        }

        // Create a new damaged inventory record
        Inventory::create([
            'product_id' => $this->product_id,
            'quantity' => $quantity,
            'cost_price' => $this->cost_price,
            'supplier' => $this->supplier,
            'location' => $this->location,
            'status' => 'damaged'
        ]);

        // Reduce the available quantity
        $this->adjustStock(-$quantity, "Marked as damaged: $reason");
    }

    public function reserve(int $quantity): void
    {
        if ($this->quantity < $quantity) {
            throw new \Exception('Not enough stock to reserve');
        }

        // Create a new reserved inventory record
        Inventory::create([
            'product_id' => $this->product_id,
            'quantity' => $quantity,
            'cost_price' => $this->cost_price,
            'supplier' => $this->supplier,
            'location' => $this->location,
            'status' => 'reserved'
        ]);

        // Reduce the available quantity
        $this->adjustStock(-$quantity, 'Reserved for order');
    }

    public function reportMissing(int $quantity, string $reason): void
    {
        if ($this->quantity < $quantity) {
            throw new \Exception('Reported missing quantity exceeds available stock');
        }

        // Create a new missing inventory record
        Inventory::create([
            'product_id' => $this->product_id,
            'quantity' => $quantity,
            'cost_price' => $this->cost_price,
            'supplier' => $this->supplier,
            'location' => $this->location,
            'status' => 'missing'
        ]);

        // Reduce the available quantity
        $this->adjustStock(-$quantity, "Reported missing: $reason");
    }

    public static function consolidate(string $productId): array
    {
        $total = self::where('product_id', $productId)->sum('quantity');
        $available = self::where('product_id', $productId)
            ->where('status', 'available')
            ->sum('quantity');
        $reserved = self::where('product_id', $productId)
            ->where('status', 'reserved')
            ->sum('quantity');
        $damaged = self::where('product_id', $productId)
            ->where('status', 'damaged')
            ->sum('quantity');
        $missing = self::where('product_id', $productId)
            ->where('status', 'missing')
            ->sum('quantity');

        return [
            'total' => $total,
            'available' => $available,
            'reserved' => $reserved,
            'damaged' => $damaged,
            'missing' => $missing
        ];
    }
}
