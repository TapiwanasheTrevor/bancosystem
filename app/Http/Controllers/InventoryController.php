<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivingNote;
use App\Models\GrnItem;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventory items
     */
    public function index(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $productId = $request->input('product_id');
        $condition = $request->input('condition');
        
        $query = InventoryItem::with(['product', 'warehouse'])
            ->orderBy('created_at', 'desc');
            
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        if ($productId) {
            $query->where('product_id', $productId);
        }
        
        if ($condition) {
            $query->where('condition', $condition);
        }
        
        $inventoryItems = $query->paginate(20);
        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('admin.inventory.index', compact('inventoryItems', 'warehouses', 'products', 'warehouseId', 'productId', 'condition'));
    }

    /**
     * Show the form for adding new inventory
     */
    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('admin.inventory.create', compact('warehouses', 'products'));
    }

    /**
     * Store new inventory items
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'batch_number' => 'nullable|string|max:255',
            'received_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:received_date',
            'notes' => 'nullable|string',
            'storage_location' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        try {
            $warehouseId = $request->input('warehouse_id');
            $productId = $request->input('product_id');
            $batchNumber = $request->input('batch_number') ?? date('Ymd') . rand(1000, 9999);
            
            // Check if inventory item already exists
            $inventoryItem = InventoryItem::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->where('batch_number', $batchNumber)
                ->first();
                
            $quantity = $request->input('quantity');
            
            if ($inventoryItem) {
                // Update existing inventory
                $inventoryItem->addInventory($quantity, $request->input('unit_cost'));
            } else {
                // Create new inventory item
                $inventoryItem = new InventoryItem();
                $inventoryItem->warehouse_id = $warehouseId;
                $inventoryItem->product_id = $productId;
                $inventoryItem->quantity = $quantity;
                $inventoryItem->available_quantity = $quantity;
                $inventoryItem->reserved_quantity = 0;
                $inventoryItem->damaged_quantity = 0;
                $inventoryItem->unit_cost = $request->input('unit_cost');
                $inventoryItem->batch_number = $batchNumber;
                $inventoryItem->received_date = $request->input('received_date');
                $inventoryItem->expiry_date = $request->input('expiry_date');
                $inventoryItem->condition = 'good';
                $inventoryItem->notes = $request->input('notes');
                $inventoryItem->storage_location = $request->input('storage_location');
                $inventoryItem->save();
            }
            
            // Record transaction
            $transaction = new InventoryTransaction();
            $transaction->transaction_number = 'TRX-' . time();
            $transaction->inventory_item_id = $inventoryItem->id;
            $transaction->product_id = $productId;
            $transaction->warehouse_id = $warehouseId;
            $transaction->transaction_type = 'receipt';
            $transaction->quantity = $quantity;
            $transaction->previous_quantity = $inventoryItem->quantity - $quantity;
            $transaction->unit_cost = $request->input('unit_cost');
            $transaction->total_cost = $request->input('unit_cost') * $quantity;
            $transaction->user_id = Auth::id();
            $transaction->notes = $request->input('notes');
            $transaction->transaction_date = now();
            $transaction->save();
            
            DB::commit();
            
            return redirect()->route('inventory.show', $inventoryItem->id)
                ->with('success', 'Inventory added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error adding inventory: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the inventory item details
     */
    public function show(string $id)
    {
        $inventoryItem = InventoryItem::with(['product', 'warehouse', 'transactions.user'])->findOrFail($id);
        
        return view('admin.inventory.show', compact('inventoryItem'));
    }

    /**
     * Show the form for editing the inventory item
     */
    public function edit(string $id)
    {
        $inventoryItem = InventoryItem::with(['product', 'warehouse'])->findOrFail($id);
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.inventory.edit', compact('inventoryItem', 'warehouses'));
    }

    /**
     * Update the inventory item
     */
    public function update(Request $request, string $id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'unit_cost' => 'sometimes|required|numeric|min:0',
            'expiry_date' => 'nullable|date|after:received_date',
            'notes' => 'nullable|string',
            'storage_location' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        if ($request->has('unit_cost')) {
            $inventoryItem->unit_cost = $request->input('unit_cost');
        }
        
        if ($request->has('expiry_date')) {
            $inventoryItem->expiry_date = $request->input('expiry_date');
        }
        
        $inventoryItem->notes = $request->input('notes');
        $inventoryItem->storage_location = $request->input('storage_location');
        $inventoryItem->save();
        
        return redirect()->route('inventory.show', $inventoryItem->id)
            ->with('success', 'Inventory updated successfully.');
    }

    /**
     * Show form for adjusting inventory
     */
    public function showAdjustForm(string $id)
    {
        $inventoryItem = InventoryItem::with(['product', 'warehouse'])->findOrFail($id);
        
        return view('admin.inventory.adjust', compact('inventoryItem'));
    }
    
    /**
     * Process inventory adjustment
     */
    public function processAdjustment(Request $request, string $id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'required|in:add,remove,damage,count',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $adjustmentType = $request->input('adjustment_type');
        $quantity = $request->input('quantity');
        $reason = $request->input('reason');
        
        DB::beginTransaction();
        try {
            $previousQuantity = $inventoryItem->quantity;
            $previousAvailable = $inventoryItem->available_quantity;
            
            switch ($adjustmentType) {
                case 'add':
                    $inventoryItem->quantity += $quantity;
                    $inventoryItem->available_quantity += $quantity;
                    $transactionType = 'adjustment';
                    break;
                case 'remove':
                    if ($inventoryItem->available_quantity < $quantity) {
                        throw new \Exception('Not enough available quantity for removal.');
                    }
                    $inventoryItem->quantity -= $quantity;
                    $inventoryItem->available_quantity -= $quantity;
                    $transactionType = 'adjustment';
                    break;
                case 'damage':
                    if ($inventoryItem->available_quantity < $quantity) {
                        throw new \Exception('Not enough available quantity to mark as damaged.');
                    }
                    $inventoryItem->markAsDamaged($quantity);
                    $transactionType = 'damage';
                    break;
                case 'count':
                    // Set total quantity to new count
                    $countDiff = $quantity - $inventoryItem->quantity;
                    $inventoryItem->quantity = $quantity;
                    
                    // Adjust available quantity proportionally
                    if ($previousQuantity > 0) {
                        $availableRatio = $previousAvailable / $previousQuantity;
                        $inventoryItem->available_quantity = max(0, round($quantity * $availableRatio));
                    } else {
                        $inventoryItem->available_quantity = $quantity;
                    }
                    
                    $transactionType = 'count';
                    break;
            }
            
            $inventoryItem->save();
            
            // Record transaction
            $transaction = new InventoryTransaction();
            $transaction->transaction_number = 'ADJ-' . time();
            $transaction->inventory_item_id = $inventoryItem->id;
            $transaction->product_id = $inventoryItem->product_id;
            $transaction->warehouse_id = $inventoryItem->warehouse_id;
            $transaction->transaction_type = $transactionType;
            $transaction->quantity = $adjustmentType == 'count' ? abs($countDiff) : $quantity;
            $transaction->previous_quantity = $previousQuantity;
            $transaction->unit_cost = $inventoryItem->unit_cost;
            $transaction->user_id = Auth::id();
            $transaction->notes = $reason;
            $transaction->transaction_date = now();
            $transaction->save();
            
            DB::commit();
            
            return redirect()->route('inventory.show', $inventoryItem->id)
                ->with('success', 'Inventory adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error adjusting inventory: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show inventory transfer form
     */
    public function showTransferForm()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with(['inventoryItems' => function($query) {
            $query->where('available_quantity', '>', 0);
        }])->get();
        
        return view('admin.inventory.transfer_form', compact('warehouses', 'products'));
    }
    
    /**
     * Process inventory transfer
     */
    public function processTransfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        try {
            // Create transfer record
            $transfer = new InventoryTransfer();
            $transfer->transfer_number = 'TR-' . time();
            $transfer->source_warehouse_id = $request->input('source_warehouse_id');
            $transfer->destination_warehouse_id = $request->input('destination_warehouse_id');
            $transfer->created_by = Auth::id();
            $transfer->status = 'pending';
            $transfer->transfer_date = $request->input('transfer_date');
            $transfer->notes = $request->input('notes');
            $transfer->save();
            
            // Add transfer items
            for ($i = 0; $i < count($request->input('product_id')); $i++) {
                $productId = $request->input('product_id')[$i];
                $quantity = $request->input('quantity')[$i];
                
                // Check inventory availability
                $sourceInventory = InventoryItem::where('warehouse_id', $transfer->source_warehouse_id)
                    ->where('product_id', $productId)
                    ->where('available_quantity', '>=', $quantity)
                    ->orderBy('expiry_date', 'asc')
                    ->first();
                    
                if (!$sourceInventory) {
                    throw new \Exception("Not enough available quantity for product #$productId in the source warehouse.");
                }
                
                // Reserve inventory
                $sourceInventory->reserve($quantity);
                
                // Create transfer item
                $transferItem = new InventoryTransferItem();
                $transferItem->inventory_transfer_id = $transfer->id;
                $transferItem->product_id = $productId;
                $transferItem->quantity = $quantity;
                $transferItem->received_quantity = 0;
                $transferItem->batch_number = $sourceInventory->batch_number;
                $transferItem->save();
                
                // Record transaction for source warehouse
                $transaction = new InventoryTransaction();
                $transaction->transaction_number = 'TRX-OUT-' . time() . '-' . $i;
                $transaction->inventory_item_id = $sourceInventory->id;
                $transaction->product_id = $productId;
                $transaction->warehouse_id = $transfer->source_warehouse_id;
                $transaction->transaction_type = 'transfer_out';
                $transaction->quantity = $quantity;
                $transaction->previous_quantity = $sourceInventory->quantity;
                $transaction->unit_cost = $sourceInventory->unit_cost;
                $transaction->total_cost = $sourceInventory->unit_cost * $quantity;
                $transaction->user_id = Auth::id();
                $transaction->reference_number = $transfer->transfer_number;
                $transaction->transaction_date = now();
                $transaction->save();
            }
            
            DB::commit();
            
            return redirect()->route('inventory.transfers.show', $transfer->id)
                ->with('success', 'Inventory transfer initiated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating transfer: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show inventory transfer details
     */
    public function showTransfer($id)
    {
        $transfer = InventoryTransfer::with([
            'sourceWarehouse', 
            'destinationWarehouse', 
            'creator', 
            'approver',
            'transferItems.product'
        ])->findOrFail($id);
        
        return view('admin.inventory.transfer_details', compact('transfer'));
    }
    
    /**
     * Complete inventory transfer
     */
    public function completeTransfer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'received_quantity' => 'required|array',
            'received_quantity.*' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $transfer = InventoryTransfer::with(['transferItems.product'])->findOrFail($id);
        
        if ($transfer->status !== 'pending' && $transfer->status !== 'in_transit') {
            return redirect()->route('inventory.transfers.show', $transfer->id)
                ->with('error', 'This transfer is already ' . $transfer->status . '.');
        }
        
        DB::beginTransaction();
        try {
            // Process each transfer item
            $receivedQuantities = $request->input('received_quantity');
            
            foreach ($transfer->transferItems as $index => $item) {
                $receivedQty = (int) $receivedQuantities[$item->id];
                
                // Find source inventory item
                $sourceInventory = InventoryItem::where('warehouse_id', $transfer->source_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('batch_number', $item->batch_number)
                    ->first();
                    
                if (!$sourceInventory) {
                    throw new \Exception("Source inventory for product {$item->product->name} not found.");
                }
                
                // Unreserve and consume from source
                if ($receivedQty > 0) {
                    if ($receivedQty > $item->quantity) {
                        $receivedQty = $item->quantity; // Cap at original quantity
                    }
                    
                    // Consume from source
                    $sourceInventory->consume($receivedQty);
                    
                    // Find or create destination inventory
                    $destInventory = InventoryItem::firstOrCreate(
                        [
                            'warehouse_id' => $transfer->destination_warehouse_id,
                            'product_id' => $item->product_id,
                            'batch_number' => $item->batch_number
                        ],
                        [
                            'quantity' => 0,
                            'available_quantity' => 0,
                            'reserved_quantity' => 0,
                            'damaged_quantity' => 0,
                            'unit_cost' => $sourceInventory->unit_cost,
                            'received_date' => now(),
                            'expiry_date' => $sourceInventory->expiry_date,
                            'condition' => 'good',
                            'storage_location' => $sourceInventory->storage_location
                        ]
                    );
                    
                    // Add to destination
                    $destInventory->addInventory($receivedQty, $sourceInventory->unit_cost);
                    
                    // Record transaction for destination
                    $transaction = new InventoryTransaction();
                    $transaction->transaction_number = 'TRX-IN-' . time() . '-' . $index;
                    $transaction->inventory_item_id = $destInventory->id;
                    $transaction->product_id = $item->product_id;
                    $transaction->warehouse_id = $transfer->destination_warehouse_id;
                    $transaction->transaction_type = 'transfer_in';
                    $transaction->quantity = $receivedQty;
                    $transaction->previous_quantity = $destInventory->quantity - $receivedQty;
                    $transaction->unit_cost = $sourceInventory->unit_cost;
                    $transaction->total_cost = $sourceInventory->unit_cost * $receivedQty;
                    $transaction->user_id = Auth::id();
                    $transaction->reference_number = $transfer->transfer_number;
                    $transaction->transaction_date = now();
                    $transaction->save();
                } else {
                    // If nothing received, unreserve in source
                    $sourceInventory->unreserve($item->quantity);
                }
                
                // Update transfer item
                $item->received_quantity = $receivedQty;
                $item->save();
            }
            
            // Update transfer status
            $transfer->status = 'completed';
            $transfer->completed_date = now();
            $transfer->approved_by = Auth::id();
            if ($request->has('notes')) {
                $transfer->notes .= "\n" . $request->input('notes');
            }
            $transfer->save();
            
            DB::commit();
            
            return redirect()->route('inventory.transfers.show', $transfer->id)
                ->with('success', 'Inventory transfer completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error completing transfer: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Cancel inventory transfer
     */
    public function cancelTransfer($id)
    {
        $transfer = InventoryTransfer::with(['transferItems.product'])->findOrFail($id);
        
        if ($transfer->status !== 'pending' && $transfer->status !== 'in_transit') {
            return redirect()->route('inventory.transfers.show', $transfer->id)
                ->with('error', 'Cannot cancel a transfer that is already ' . $transfer->status . '.');
        }
        
        DB::beginTransaction();
        try {
            // Unreserve all items
            foreach ($transfer->transferItems as $item) {
                $sourceInventory = InventoryItem::where('warehouse_id', $transfer->source_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('batch_number', $item->batch_number)
                    ->first();
                    
                if ($sourceInventory) {
                    $sourceInventory->unreserve($item->quantity - $item->received_quantity);
                }
            }
            
            // Update transfer status
            $transfer->status = 'cancelled';
            $transfer->save();
            
            DB::commit();
            
            return redirect()->route('inventory.transfers.show', $transfer->id)
                ->with('success', 'Inventory transfer cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error cancelling transfer: ' . $e->getMessage());
        }
    }
    
    /**
     * Show goods receiving form
     */
    public function showGrnForm()
    {
        $purchaseOrders = PurchaseOrder::with(['items.product'])
            ->where('status', 'approved')
            ->orWhere('status', 'partially_fulfilled')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.inventory.grn_form', compact('purchaseOrders', 'warehouses'));
    }
    
    /**
     * Show GRN form for specific purchase order
     */
    public function showGrnFormForPo($poId)
    {
        $purchaseOrder = PurchaseOrder::with(['items.product'])->findOrFail($poId);
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.inventory.grn_form_for_po', compact('purchaseOrder', 'warehouses'));
    }
    
    /**
     * Process GRN
     */
    public function processGrn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier' => 'nullable|string|max:255',
            'delivery_note_number' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'received_date' => 'required|date',
            'notes' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'po_item_id' => 'nullable|array',
            'po_item_id.*' => 'nullable|exists:purchase_order_items,id',
            'quantity_ordered' => 'nullable|array',
            'quantity_ordered.*' => 'nullable|integer|min:0',
            'quantity_received' => 'required|array',
            'quantity_received.*' => 'required|integer|min:1',
            'quantity_accepted' => 'required|array',
            'quantity_accepted.*' => 'required|integer|min:0',
            'unit_cost' => 'required|array',
            'unit_cost.*' => 'required|numeric|min:0',
            'batch_number' => 'nullable|array',
            'batch_number.*' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|array',
            'expiry_date.*' => 'nullable|date|after:received_date',
            'rejection_reason' => 'nullable|array',
            'rejection_reason.*' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        try {
            // Create GRN record
            $grn = new GoodsReceivingNote();
            $grn->grn_number = 'GRN-' . date('Ymd') . '-' . rand(1000, 9999);
            $grn->purchase_order_id = $request->input('purchase_order_id');
            $grn->warehouse_id = $request->input('warehouse_id');
            $grn->received_by = Auth::id();
            $grn->supplier = $request->input('supplier');
            $grn->delivery_note_number = $request->input('delivery_note_number');
            $grn->invoice_number = $request->input('invoice_number');
            $grn->received_date = $request->input('received_date');
            $grn->status = 'pending';
            $grn->notes = $request->input('notes');
            $grn->save();
            
            // Process each GRN item
            $productIds = $request->input('product_id');
            $poItemIds = $request->input('po_item_id');
            $quantitiesOrdered = $request->input('quantity_ordered');
            $quantitiesReceived = $request->input('quantity_received');
            $quantitiesAccepted = $request->input('quantity_accepted');
            $unitCosts = $request->input('unit_cost');
            $batchNumbers = $request->input('batch_number');
            $expiryDates = $request->input('expiry_date');
            $rejectionReasons = $request->input('rejection_reason');
            
            for ($i = 0; $i < count($productIds); $i++) {
                $productId = $productIds[$i];
                $poItemId = isset($poItemIds[$i]) ? $poItemIds[$i] : null;
                $quantityOrdered = isset($quantitiesOrdered[$i]) ? $quantitiesOrdered[$i] : null;
                $quantityReceived = $quantitiesReceived[$i];
                $quantityAccepted = $quantitiesAccepted[$i];
                $quantityRejected = $quantityReceived - $quantityAccepted;
                $unitCost = $unitCosts[$i];
                $batchNumber = isset($batchNumbers[$i]) ? $batchNumbers[$i] : date('Ymd') . rand(1000, 9999);
                $expiryDate = isset($expiryDates[$i]) ? $expiryDates[$i] : null;
                $rejectionReason = isset($rejectionReasons[$i]) ? $rejectionReasons[$i] : null;
                
                // Create GRN item
                $grnItem = new GrnItem();
                $grnItem->goods_receiving_note_id = $grn->id;
                $grnItem->purchase_order_item_id = $poItemId;
                $grnItem->product_id = $productId;
                $grnItem->quantity_ordered = $quantityOrdered;
                $grnItem->quantity_received = $quantityReceived;
                $grnItem->quantity_accepted = $quantityAccepted;
                $grnItem->quantity_rejected = $quantityRejected;
                $grnItem->unit_cost = $unitCost;
                $grnItem->batch_number = $batchNumber;
                $grnItem->expiry_date = $expiryDate;
                $grnItem->rejection_reason = $rejectionReason;
                $grnItem->save();
                
                // Update purchase order item if applicable
                if ($poItemId) {
                    $poItem = \App\Models\PurchaseOrderItem::find($poItemId);
                    if ($poItem) {
                        $poItem->quantity_fulfilled += $quantityAccepted;
                        $poItem->updateStatus();
                    }
                }
                
                // Add to inventory if accepted quantity > 0
                if ($quantityAccepted > 0) {
                    // Find or create inventory item
                    $inventoryItem = InventoryItem::firstOrCreate(
                        [
                            'warehouse_id' => $grn->warehouse_id,
                            'product_id' => $productId,
                            'batch_number' => $batchNumber
                        ],
                        [
                            'quantity' => 0,
                            'available_quantity' => 0,
                            'reserved_quantity' => 0,
                            'damaged_quantity' => 0,
                            'unit_cost' => $unitCost,
                            'received_date' => $grn->received_date,
                            'expiry_date' => $expiryDate,
                            'condition' => 'good',
                        ]
                    );
                    
                    // Add accepted quantity to inventory
                    $inventoryItem->addInventory($quantityAccepted, $unitCost);
                    
                    // Record transaction
                    $transaction = new InventoryTransaction();
                    $transaction->transaction_number = 'GRN-' . time() . '-' . $i;
                    $transaction->inventory_item_id = $inventoryItem->id;
                    $transaction->product_id = $productId;
                    $transaction->warehouse_id = $grn->warehouse_id;
                    $transaction->transaction_type = 'receipt';
                    $transaction->quantity = $quantityAccepted;
                    $transaction->previous_quantity = $inventoryItem->quantity - $quantityAccepted;
                    $transaction->unit_cost = $unitCost;
                    $transaction->total_cost = $unitCost * $quantityAccepted;
                    $transaction->purchase_order_id = $grn->purchase_order_id;
                    $transaction->purchase_order_item_id = $poItemId;
                    $transaction->user_id = Auth::id();
                    $transaction->reference_number = $grn->grn_number;
                    $transaction->transaction_date = now();
                    $transaction->save();
                }
                
                // Add rejected inventory if applicable
                if ($quantityRejected > 0) {
                    // Create batch number for rejected items
                    $rejectedBatchNumber = $batchNumber . '-REJ';
                    
                    // Find or create inventory item for rejected goods
                    $rejectedInventory = InventoryItem::firstOrCreate(
                        [
                            'warehouse_id' => $grn->warehouse_id,
                            'product_id' => $productId,
                            'batch_number' => $rejectedBatchNumber
                        ],
                        [
                            'quantity' => 0,
                            'available_quantity' => 0,
                            'reserved_quantity' => 0,
                            'damaged_quantity' => 0,
                            'unit_cost' => $unitCost,
                            'received_date' => $grn->received_date,
                            'expiry_date' => $expiryDate,
                            'condition' => 'damaged',
                            'notes' => $rejectionReason,
                        ]
                    );
                    
                    // Add rejected quantity as damaged
                    $rejectedInventory->quantity += $quantityRejected;
                    $rejectedInventory->damaged_quantity += $quantityRejected;
                    $rejectedInventory->save();
                    
                    // Record transaction for rejected items
                    $rejTransaction = new InventoryTransaction();
                    $rejTransaction->transaction_number = 'GRN-REJ-' . time() . '-' . $i;
                    $rejTransaction->inventory_item_id = $rejectedInventory->id;
                    $rejTransaction->product_id = $productId;
                    $rejTransaction->warehouse_id = $grn->warehouse_id;
                    $rejTransaction->transaction_type = 'damage';
                    $rejTransaction->quantity = $quantityRejected;
                    $rejTransaction->previous_quantity = $rejectedInventory->quantity - $quantityRejected;
                    $rejTransaction->unit_cost = $unitCost;
                    $rejTransaction->total_cost = $unitCost * $quantityRejected;
                    $rejTransaction->purchase_order_id = $grn->purchase_order_id;
                    $rejTransaction->purchase_order_item_id = $poItemId;
                    $rejTransaction->user_id = Auth::id();
                    $rejTransaction->reference_number = $grn->grn_number;
                    $rejTransaction->notes = $rejectionReason;
                    $rejTransaction->transaction_date = now();
                    $rejTransaction->save();
                }
            }
            
            // Update GRN status
            $grn->status = 'verified';
            $grn->verified_by = Auth::id();
            $grn->save();
            
            // Update purchase order status if applicable
            if ($grn->purchase_order_id) {
                $po = PurchaseOrder::find($grn->purchase_order_id);
                if ($po) {
                    $po->updateStatus();
                }
            }
            
            DB::commit();
            
            return redirect()->route('inventory.grn.show', $grn->id)
                ->with('success', 'Goods receiving note processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error processing GRN: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show GRN details
     */
    public function showGrn($id)
    {
        $grn = GoodsReceivingNote::with([
            'purchaseOrder', 
            'warehouse', 
            'receivedBy', 
            'verifiedBy',
            'grnItems.product'
        ])->findOrFail($id);
        
        return view('admin.inventory.grn_details', compact('grn'));
    }
    
    /**
     * Generate GRN PDF
     */
    public function generateGrnPdf($id)
    {
        $grn = GoodsReceivingNote::with([
            'purchaseOrder', 
            'warehouse', 
            'receivedBy', 
            'verifiedBy',
            'grnItems.product'
        ])->findOrFail($id);
        
        $pdf = \PDF::loadView('admin.inventory.grn_pdf', compact('grn'));
        
        return $pdf->download('grn-' . $grn->grn_number . '.pdf');
    }
    
    /**
     * Show warehouse management screen
     */
    public function manageWarehouses()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        
        return view('admin.inventory.warehouses', compact('warehouses'));
    }
    
    /**
     * Create new warehouse
     */
    public function storeWarehouse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:warehouses,name',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $warehouse = new Warehouse();
        $warehouse->name = $request->input('name');
        $warehouse->location = $request->input('location');
        $warehouse->contact_person = $request->input('contact_person');
        $warehouse->contact_number = $request->input('contact_number');
        $warehouse->description = $request->input('description');
        $warehouse->is_active = true;
        $warehouse->save();
        
        return redirect()->route('inventory.warehouses.manage')
            ->with('success', 'Warehouse created successfully.');
    }
    
    /**
     * Update warehouse
     */
    public function updateWarehouse(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:warehouses,name,' . $id,
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $warehouse->name = $request->input('name');
        $warehouse->location = $request->input('location');
        $warehouse->contact_person = $request->input('contact_person');
        $warehouse->contact_number = $request->input('contact_number');
        $warehouse->description = $request->input('description');
        $warehouse->is_active = $request->input('is_active', false);
        $warehouse->save();
        
        return redirect()->route('inventory.warehouses.manage')
            ->with('success', 'Warehouse updated successfully.');
    }
    
    /**
     * Show warehouse report
     */
    public function warehouseReport($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $inventoryItems = InventoryItem::with(['product'])
            ->where('warehouse_id', $id)
            ->where('quantity', '>', 0)
            ->orderBy('product_id')
            ->get();
            
        $totalValue = $inventoryItems->sum(function($item) {
            return $item->getValue();
        });
        
        $productCount = $inventoryItems->unique('product_id')->count();
        
        return view('admin.inventory.warehouse_report', compact('warehouse', 'inventoryItems', 'totalValue', 'productCount'));
    }
    
    /**
     * Show product inventory report
     */
    public function productReport($id)
    {
        $product = Product::findOrFail($id);
        $inventoryItems = InventoryItem::with(['warehouse'])
            ->where('product_id', $id)
            ->where('quantity', '>', 0)
            ->orderBy('warehouse_id')
            ->get();
            
        $totalQuantity = $inventoryItems->sum('quantity');
        $availableQuantity = $inventoryItems->sum('available_quantity');
        $reservedQuantity = $inventoryItems->sum('reserved_quantity');
        $damagedQuantity = $inventoryItems->sum('damaged_quantity');
        
        $totalValue = $inventoryItems->sum(function($item) {
            return $item->getValue();
        });
        
        return view('admin.inventory.product_report', compact(
            'product', 
            'inventoryItems', 
            'totalQuantity',
            'availableQuantity',
            'reservedQuantity',
            'damagedQuantity',
            'totalValue'
        ));
    }
    
    /**
     * Show inventory search form
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $warehouseId = $request->input('warehouse_id');
        
        $results = [];
        if ($query) {
            $inventoryQuery = InventoryItem::with(['product', 'warehouse'])
                ->where('quantity', '>', 0)
                ->whereHas('product', function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
                
            if ($warehouseId) {
                $inventoryQuery->where('warehouse_id', $warehouseId);
            }
            
            $results = $inventoryQuery->get();
        }
        
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.inventory.search', compact('results', 'warehouses', 'query', 'warehouseId'));
    }
}
