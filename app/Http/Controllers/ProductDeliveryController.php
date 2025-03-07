<?php

namespace App\Http\Controllers;

use App\Models\DeliveryStatusUpdate;
use App\Models\Form;
use App\Models\Product;
use App\Models\ProductDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductDeliveryController extends Controller
{
    /**
     * Display a listing of product deliveries
     */
    public function index()
    {
        $deliveries = ProductDelivery::with(['product', 'form'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.deliveries.index', compact('deliveries'));
    }
    
    /**
     * Show the form for creating a new delivery record
     */
    public function create()
    {
        $forms = Form::where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $products = Product::orderBy('name')->get();
        
        return view('admin.deliveries.create', compact('forms', 'products'));
    }
    
    /**
     * Store a new delivery record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'status' => 'required|in:pending,processing,dispatched,in_transit,at_station,out_for_delivery,delivered,delayed,cancelled',
            'current_location' => 'nullable|string|max:255',
            'status_notes' => 'nullable|string',
            'estimated_delivery_date' => 'nullable|date',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Get the form
            $form = Form::findOrFail($validated['form_id']);
            
            // Check if form has product data in form_values JSON field
            $formValues = json_decode($form->form_values, true) ?? [];
            $productId = null;
            
            // Try to find the product ID from form values
            if (isset($formValues['questionnaireData']) && 
                isset($formValues['questionnaireData']['selectedProduct']) && 
                isset($formValues['questionnaireData']['selectedProduct']['product']) && 
                isset($formValues['questionnaireData']['selectedProduct']['product']['id'])) {
                    
                $productId = $formValues['questionnaireData']['selectedProduct']['product']['id'];
            }
            
            // If product ID wasn't found, try other possible locations in the form data
            if (!$productId && isset($formValues['productId'])) {
                $productId = $formValues['productId'];
            }
            
            // If still no product ID found, try any field that might contain it
            if (!$productId) {
                foreach ($formValues as $key => $value) {
                    if (is_numeric($value) && strpos(strtolower($key), 'product') !== false) {
                        $productId = $value;
                        break;
                    }
                }
            }
            
            // Validate that we found a product ID
            if (!$productId) {
                throw new \Exception('Could not determine product for this application. Please select a product manually.');
            }
            
            // Verify the product exists
            $product = Product::findOrFail($productId);
            
            // Add product_id to validated data
            $validated['product_id'] = $productId;
            
            // Generate unique tracking number
            $validated['tracking_number'] = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
            
            // Create delivery record
            $delivery = ProductDelivery::create($validated);
            
            // Create initial status update
            DeliveryStatusUpdate::create([
                'product_delivery_id' => $delivery->id,
                'status' => $validated['status'],
                'location' => $validated['current_location'] ?? null,
                'notes' => $validated['status_notes'] ?? 'Initial status record created'
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.deliveries.show', $delivery)
                ->with('success', "Delivery record created successfully for product: {$product->name} with tracking number: {$delivery->tracking_number}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating delivery record: ' . $e->getMessage());
        }
    }
    
    /**
     * Display a delivery record
     */
    public function show(ProductDelivery $delivery)
    {
        $delivery->load(['product', 'form', 'statusUpdates']);
        
        return view('admin.deliveries.show', compact('delivery'));
    }
    
    /**
     * Update delivery status
     */
    public function updateStatus(Request $request, ProductDelivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,dispatched,in_transit,at_station,out_for_delivery,delivered,delayed,cancelled',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update the delivery record
            $delivery->update([
                'status' => $validated['status'],
                'current_location' => $validated['location'] ?? $delivery->current_location,
                'status_notes' => $validated['notes'] ?? $delivery->status_notes,
                'actual_delivery_date' => $validated['status'] === 'delivered' ? now() : $delivery->actual_delivery_date,
            ]);
            
            // Create status update record
            DeliveryStatusUpdate::create([
                'product_delivery_id' => $delivery->id,
                'status' => $validated['status'],
                'location' => $validated['location'] ?? null,
                'notes' => $validated['notes'] ?? 'Status updated to: ' . $validated['status']
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.deliveries.show', $delivery)
                ->with('success', 'Delivery status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating delivery status: ' . $e->getMessage());
        }
    }
}
