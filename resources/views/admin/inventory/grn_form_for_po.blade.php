@extends('layouts.app')

@section('title', 'Create GRN from Purchase Order')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create GRN from Purchase Order</h1>
        <div class="flex space-x-2">
            <a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-eye mr-2"></i> View PO
            </a>
            <a href="{{ route('inventory.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
            </a>
        </div>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Purchase Order Summary -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
            <div>
                <p class="text-sm text-gray-600">Purchase Order</p>
                <p class="font-medium">{{ $purchaseOrder->po_number }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Supplier</p>
                <p class="font-medium">{{ $purchaseOrder->supplier ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Amount</p>
                <p class="font-medium">${{ number_format($purchaseOrder->total_amount, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Order Date</p>
                <p class="font-medium">{{ $purchaseOrder->order_date->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Expected Delivery</p>
                <p class="font-medium">{{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <p class="font-medium">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($purchaseOrder->status == 'draft') bg-gray-100 text-gray-800
                        @elseif($purchaseOrder->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($purchaseOrder->status == 'approved') bg-blue-100 text-blue-800
                        @elseif($purchaseOrder->status == 'partially_fulfilled') bg-purple-100 text-purple-800
                        @elseif($purchaseOrder->status == 'fulfilled') bg-green-100 text-green-800
                        @elseif($purchaseOrder->status == 'cancelled') bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- GRN Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('inventory.grn.store') }}" method="POST" id="grnForm">
            @csrf
            <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Receiving Warehouse *</label>
                    <select id="warehouse_id" name="warehouse_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Received Date *</label>
                    <input type="date" id="received_date" name="received_date" value="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('received_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-1">Delivery Note Number</label>
                    <input type="text" id="delivery_note_number" name="delivery_note_number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('delivery_note_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
                    <input type="text" id="invoice_number" name="invoice_number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('invoice_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Items from Purchase Order</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Ordered</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Fulfilled</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Remaining</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Received</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Accepted</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer" class="bg-white divide-y divide-gray-200">
                            @foreach($purchaseOrder->items as $index => $item)
                                @if($item->quantity > $item->quantity_fulfilled)
                                <tr class="item-row">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                        <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                        <input type="hidden" name="po_item_id[]" value="{{ $item->id }}">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="quantity_ordered[]" class="qty-ordered w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ $item->quantity }}" readonly>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-500">{{ $item->quantity_fulfilled }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-indigo-600">{{ $item->quantity - $item->quantity_fulfilled }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="quantity_received[]" class="qty-received-{{ $index }} w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ $item->quantity - $item->quantity_fulfilled }}" min="0" max="{{ $item->quantity - $item->quantity_fulfilled }}" required>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="quantity_accepted[]" class="qty-accepted-{{ $index }} w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ $item->quantity - $item->quantity_fulfilled }}" min="0" max="{{ $item->quantity - $item->quantity_fulfilled }}" required>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="unit_cost[]" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ $item->unit_price }}" min="0" step="0.01" required>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="batch_number[]" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="date" name="expiry_date[]" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </td>
                                </tr>
                                <tr class="rejection-row-{{ $index }} hidden bg-red-50">
                                    <td colspan="9" class="px-4 py-2">
                                        <div class="flex items-center">
                                            <span class="font-medium text-red-600 mr-2">Rejection Reason:</span>
                                            <input type="text" name="rejection_reason[]" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Reason for rejecting items (if any)">
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700" id="submitBtn">
                    <i class="fas fa-save mr-2"></i> Process Goods Receipt
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const grnForm = document.getElementById('grnForm');
        
        // For each item setup quantity validation and rejection reason toggle
        @foreach($purchaseOrder->items as $index => $item)
            @if($item->quantity > $item->quantity_fulfilled)
                // Get references to inputs
                const qtyReceived{{ $index }} = document.querySelector('.qty-received-{{ $index }}');
                const qtyAccepted{{ $index }} = document.querySelector('.qty-accepted-{{ $index }}');
                const rejectionRow{{ $index }} = document.querySelector('.rejection-row-{{ $index }}');
                
                // Handle quantity received changes
                qtyReceived{{ $index }}.addEventListener('input', function() {
                    // Update accepted qty if received changes
                    qtyAccepted{{ $index }}.value = this.value;
                    qtyAccepted{{ $index }}.max = this.value;
                    
                    // Hide rejection row when there's nothing to reject
                    rejectionRow{{ $index }}.classList.add('hidden');
                });
                
                // Handle quantity accepted changes
                qtyAccepted{{ $index }}.addEventListener('input', function() {
                    // Show/hide rejection reason row
                    const received = parseInt(qtyReceived{{ $index }}.value) || 0;
                    const accepted = parseInt(this.value) || 0;
                    
                    if (received > accepted) {
                        rejectionRow{{ $index }}.classList.remove('hidden');
                    } else {
                        rejectionRow{{ $index }}.classList.add('hidden');
                    }
                });
            @endif
        @endforeach
        
        // Handle form submission
        grnForm.addEventListener('submit', function(e) {
            // Validate each item's quantities
            let valid = true;
            let hasReceivedItems = false;
            
            @foreach($purchaseOrder->items as $index => $item)
                @if($item->quantity > $item->quantity_fulfilled)
                    const qtyReceived{{ $index }} = parseInt(document.querySelector('.qty-received-{{ $index }}').value) || 0;
                    const qtyAccepted{{ $index }} = parseInt(document.querySelector('.qty-accepted-{{ $index }}').value) || 0;
                    
                    if (qtyReceived{{ $index }} > 0) {
                        hasReceivedItems = true;
                    }
                    
                    if (qtyAccepted{{ $index }} > qtyReceived{{ $index }}) {
                        valid = false;
                        alert('Accepted quantity cannot be greater than received quantity.');
                    }
                @endif
            @endforeach
            
            if (!hasReceivedItems) {
                e.preventDefault();
                alert('Please enter at least one item with a received quantity greater than 0.');
                return false;
            }
            
            if (!valid) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endpush