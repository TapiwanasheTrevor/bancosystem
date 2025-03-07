<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $query = PurchaseOrder::with(['creator', 'form'])
            ->orderBy('created_at', 'desc');
            
        if ($status) {
            $query->where('status', $status);
        }
        
        $purchaseOrders = $query->paginate(15);
        
        return view('admin.purchase_orders.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new purchase order
     */
    public function create()
    {
        $forms = Form::where('status', 'approved')
            ->whereDoesntHave('purchaseOrders')
            ->get();
            
        $products = Product::orderBy('name')->get();
        
        return view('admin.purchase_orders.create', compact('forms', 'products'));
    }

    /**
     * Create a new purchase order from a form
     */
    public function createFromForm($formId)
    {
        $form = Form::findOrFail($formId);
        $products = Product::orderBy('name')->get();
        
        // Pre-extract product from form if possible
        $productId = null;
        $formValues = $form->form_values ?? [];
        
        if (isset($formValues['product_id'])) {
            $productId = $formValues['product_id'];
        } elseif (isset($formValues['product']) && is_numeric($formValues['product'])) {
            $productId = $formValues['product'];
        }
        
        return view('admin.purchase_orders.create_from_form', compact('form', 'products', 'productId'));
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_id' => 'nullable|exists:forms,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
            'supplier' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
            'unit_price' => 'required|array',
            'unit_price.*' => 'required|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Create purchase order
        $purchaseOrder = new PurchaseOrder();
        $purchaseOrder->po_number = PurchaseOrder::generatePONumber();
        $purchaseOrder->form_id = $request->input('form_id');
        $purchaseOrder->created_by = Auth::id();
        $purchaseOrder->status = 'pending';
        $purchaseOrder->order_date = $request->input('order_date');
        $purchaseOrder->expected_delivery_date = $request->input('expected_delivery_date');
        $purchaseOrder->supplier = $request->input('supplier');
        $purchaseOrder->supplier_contact = $request->input('supplier_contact');
        $purchaseOrder->notes = $request->input('notes');
        $purchaseOrder->save();
        
        // Add items to purchase order
        $totalAmount = 0;
        for ($i = 0; $i < count($request->input('product_id')); $i++) {
            $productId = $request->input('product_id')[$i];
            $quantity = $request->input('quantity')[$i];
            $unitPrice = $request->input('unit_price')[$i];
            $totalPrice = $quantity * $unitPrice;
            
            $item = new PurchaseOrderItem();
            $item->purchase_order_id = $purchaseOrder->id;
            $item->product_id = $productId;
            $item->quantity = $quantity;
            $item->unit_price = $unitPrice;
            $item->total_price = $totalPrice;
            $item->status = 'pending';
            $item->quantity_fulfilled = 0;
            $item->save();
            
            $totalAmount += $totalPrice;
        }
        
        // Update total amount
        $purchaseOrder->total_amount = $totalAmount;
        $purchaseOrder->save();
        
        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'Purchase order created successfully.');
    }

    /**
     * Display the specified purchase order
     */
    public function show(string $id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.product', 'creator', 'form', 'goodsReceivingNotes'])
            ->findOrFail($id);
            
        return view('admin.purchase_orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the purchase order
     */
    public function edit(string $id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.product'])->findOrFail($id);
        
        // Only allow editing of draft or pending purchase orders
        if (!in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Cannot edit purchase orders that are already in progress.');
        }
        
        $products = Product::orderBy('name')->get();
        
        return view('admin.purchase_orders.edit', compact('purchaseOrder', 'products'));
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, string $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        
        // Only allow editing of draft or pending purchase orders
        if (!in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Cannot edit purchase orders that are already in progress.');
        }
        
        $validator = Validator::make($request->all(), [
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
            'supplier' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
            'unit_price' => 'required|array',
            'unit_price.*' => 'required|numeric|min:0',
            'item_id' => 'nullable|array',
            'item_id.*' => 'nullable|exists:purchase_order_items,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Update purchase order details
        $purchaseOrder->order_date = $request->input('order_date');
        $purchaseOrder->expected_delivery_date = $request->input('expected_delivery_date');
        $purchaseOrder->supplier = $request->input('supplier');
        $purchaseOrder->supplier_contact = $request->input('supplier_contact');
        $purchaseOrder->notes = $request->input('notes');
        
        // Delete items that are not in the request
        $existingItemIds = $request->input('item_id') ?? [];
        $purchaseOrder->items()->whereNotIn('id', array_filter($existingItemIds))->delete();
        
        // Update or create items
        $totalAmount = 0;
        for ($i = 0; $i < count($request->input('product_id')); $i++) {
            $itemId = isset($request->input('item_id')[$i]) ? $request->input('item_id')[$i] : null;
            $productId = $request->input('product_id')[$i];
            $quantity = $request->input('quantity')[$i];
            $unitPrice = $request->input('unit_price')[$i];
            $totalPrice = $quantity * $unitPrice;
            
            if ($itemId) {
                // Update existing item
                $item = PurchaseOrderItem::findOrFail($itemId);
                $item->product_id = $productId;
                $item->quantity = $quantity;
                $item->unit_price = $unitPrice;
                $item->total_price = $totalPrice;
                $item->save();
            } else {
                // Create new item
                $item = new PurchaseOrderItem();
                $item->purchase_order_id = $purchaseOrder->id;
                $item->product_id = $productId;
                $item->quantity = $quantity;
                $item->unit_price = $unitPrice;
                $item->total_price = $totalPrice;
                $item->status = 'pending';
                $item->quantity_fulfilled = 0;
                $item->save();
            }
            
            $totalAmount += $totalPrice;
        }
        
        // Update total amount
        $purchaseOrder->total_amount = $totalAmount;
        $purchaseOrder->save();
        
        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'Purchase order updated successfully.');
    }

    /**
     * Change the status of a purchase order
     */
    public function changeStatus(Request $request, string $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,cancelled',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $newStatus = $request->input('status');
        $purchaseOrder->status = $newStatus;
        $purchaseOrder->save();
        
        if ($newStatus === 'cancelled') {
            // Update all items to cancelled
            $purchaseOrder->items()->update(['status' => 'cancelled']);
        }
        
        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'Purchase order status updated successfully.');
    }

    /**
     * Remove the specified purchase order
     */
    public function destroy(string $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        
        // Only allow deleting draft purchase orders
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Only draft purchase orders can be deleted.');
        }
        
        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();
        
        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }
    
    /**
     * Generate PDF for the purchase order
     */
    public function generatePdf(string $id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.product', 'creator', 'form'])->findOrFail($id);
        
        $pdf = \PDF::loadView('admin.purchase_orders.pdf', compact('purchaseOrder'));
        
        return $pdf->download('purchase-order-' . $purchaseOrder->po_number . '.pdf');
    }
    
    /**
     * Create purchase order from an approved application
     */
    public function createFromApplication(Form $form)
    {
        // Check if form is approved and doesn't already have a purchase order
        if ($form->status !== 'approved') {
            return redirect()->route('applications')
                ->with('error', 'Only approved applications can have purchase orders created.');
        }
        
        if ($form->purchaseOrders()->exists()) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'This application already has a purchase order.');
        }
        
        // Redirect to the create from form page
        return redirect()->route('purchase-orders.create-from-form', $form->id);
    }
}
