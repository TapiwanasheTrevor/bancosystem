<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'form_id',
        'created_by',
        'status',
        'order_date',
        'expected_delivery_date',
        'supplier',
        'supplier_contact',
        'notes',
        'total_amount',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Generate a unique PO number
     */
    public static function generatePONumber(): string
    {
        $prefix = 'PO';
        $dateCode = date('Ymd');
        $lastPO = self::where('po_number', 'like', $prefix . $dateCode . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        $nextNumber = 1;

        if ($lastPO) {
            $lastNumber = substr($lastPO->po_number, strlen($prefix . $dateCode));
            $nextNumber = intval($lastNumber) + 1;
        }

        return $prefix . $dateCode . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the form associated with this purchase order
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the user who created this purchase order
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the items in this purchase order
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the goods receiving notes for this purchase order
     */
    public function goodsReceivingNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivingNote::class);
    }

    /**
     * Get the inventory transactions for this purchase order
     */
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Calculate the total amount for this purchase order
     */
    public function calculateTotal(): float
    {
        return $this->items()->sum('total_price');
    }

    /**
     * Update the status based on item fulfillment
     */
    public function updateStatus(): void
    {
        $totalItems = $this->items()->count();
        $fulfilledItems = $this->items()->where('status', 'fulfilled')->count();
        $partialItems = $this->items()->where('status', 'partial')->count();
        $cancelledItems = $this->items()->where('status', 'cancelled')->count();

        if ($totalItems === 0) {
            $this->status = 'draft';
        } elseif ($totalItems === $fulfilledItems) {
            $this->status = 'fulfilled';
        } elseif ($totalItems === $cancelledItems) {
            $this->status = 'cancelled';
        } elseif ($fulfilledItems > 0 || $partialItems > 0) {
            $this->status = 'partially_fulfilled';
        }

        $this->save();
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function checkInventory(): array
    {
        $missingItems = [];
        $availableItems = [];

        foreach ($this->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)
                ->where('status', 'available')
                ->first();

            if (!$inventory || $inventory->quantity < $item->quantity) {
                $missingItems[] = [
                    'product_id' => $item->product_id,
                    'required' => $item->quantity,
                    'available' => $inventory ? $inventory->quantity : 0
                ];
            } else {
                $availableItems[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'inventory_id' => $inventory->id
                ];
            }
        }

        return [
            'missing' => $missingItems,
            'available' => $availableItems
        ];
    }

    public function processDelivery(): bool
    {
        $inventoryCheck = $this->checkInventory();

        if (!empty($inventoryCheck['missing'])) {
            return false;
        }

        foreach ($inventoryCheck['available'] as $item) {
            $inventory = Inventory::find($item['inventory_id']);
            $inventory->quantity -= $item['quantity'];
            $inventory->save();

            $orderItem = $this->items()->where('product_id', $item['product_id'])->first();
            $orderItem->status = 'delivered';
            $orderItem->save();
        }

        $this->status = 'delivered';
        $this->save();

        return true;
    }

    public function createCreditNote(string $reason): CreditNote
    {
        return $this->creditNotes()->create([
            'reference_number' => 'CN-' . uniqid(),
            'amount' => $this->total_amount,
            'reason' => $reason,
            'status' => 'pending'
        ]);
    }
}
