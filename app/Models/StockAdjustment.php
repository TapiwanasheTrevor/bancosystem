<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $fillable = [
        'inventory_id',
        'quantity',
        'reason',
        'previous_quantity',
        'new_quantity',
        'adjusted_by'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer'
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public static function generateReport(string $startDate, string $endDate): array
    {
        $adjustments = self::whereBetween('created_at', [$startDate, $endDate])
            ->with(['inventory', 'adjustedBy'])
            ->get()
            ->groupBy('inventory_id');

        $report = [];

        foreach ($adjustments as $inventoryId => $inventoryAdjustments) {
            $inventory = $inventoryAdjustments->first()->inventory;
            $totalAdjustments = $inventoryAdjustments->count();
            $netChange = $inventoryAdjustments->sum('quantity');

            $adjustmentsByReason = $inventoryAdjustments->groupBy('reason')
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'total_quantity' => $group->sum('quantity')
                    ];
                });

            $report[] = [
                'inventory_id' => $inventoryId,
                'product_id' => $inventory->product_id,
                'total_adjustments' => $totalAdjustments,
                'net_change' => $netChange,
                'adjustments_by_reason' => $adjustmentsByReason,
                'current_quantity' => $inventory->quantity
            ];
        }

        return $report;
    }

    public static function recordAdjustment(
        Inventory $inventory,
        int $quantity,
        string $reason,
        ?int $userId = null
    ): self {
        return self::create([
            'inventory_id' => $inventory->id,
            'quantity' => $quantity,
            'reason' => $reason,
            'previous_quantity' => $inventory->quantity - $quantity,
            'new_quantity' => $inventory->quantity,
            'adjusted_by' => $userId
        ]);
    }
}
