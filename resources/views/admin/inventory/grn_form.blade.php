@extends('layouts.app')

@section('title', 'Create Goods Receiving Note')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create Goods Receiving Note (GRN)</h1>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
        </a>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('inventory.grn.store') }}" method="POST" id="grnForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="purchase_order_id" class="block text-sm font-medium text-gray-700 mb-1">Purchase Order (Optional)</label>
                    <select id="purchase_order_id" name="purchase_order_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">No Purchase Order / Direct Receipt</option>
                        @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier ?? 'Unknown Supplier' }}</option>
                        @endforeach
                    </select>
                    @error('purchase_order_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
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
                    <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <input type="text" id="supplier" name="supplier" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('supplier')
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
                <h2 class="text-lg font-semibold mb-4">Received Items</h2>
                
                <div class="mb-4">
                    <button type="button" id="addItemBtn" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i> Add Item
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Reference</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Ordered</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Received</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Accepted</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch #</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer" class="bg-white divide-y divide-gray-200">
                            <!-- Items will be added here dynamically -->
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

<!-- Item template for dynamic addition -->
<template id="itemTemplate">
    <tr class="item-row">
        <td class="px-4 py-3">
            <select name="product_id[]" class="product-select w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Select Product</option>
                <!-- Products will be added dynamically when adding item -->
            </select>
            <input type="hidden" name="po_item_id[]" class="po-item-id" value="">
        </td>
        <td class="px-4 py-3">
            <span class="po-reference">N/A</span>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="quantity_ordered[]" class="qty-ordered w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="0" readonly>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="quantity_received[]" class="qty-received w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="1" min="1" required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="quantity_accepted[]" class="qty-accepted w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="1" min="0" required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="unit_cost[]" class="unit-cost w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="0.00" min="0" step="0.01" required>
        </td>
        <td class="px-4 py-3">
            <input type="text" name="batch_number[]" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </td>
        <td class="px-4 py-3">
            <input type="date" name="expiry_date[]" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </td>
        <td class="px-4 py-3">
            <button type="button" class="remove-item-btn text-red-600 hover:text-red-900">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
    <tr class="rejection-row hidden">
        <td colspan="9" class="px-4 py-3 bg-gray-50">
            <div class="flex items-center">
                <span class="font-medium text-red-600 mr-2">Rejection Reason:</span>
                <input type="text" name="rejection_reason[]" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Reason for rejecting items (if any)">
            </div>
        </td>
    </tr>
</template>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Purchase orders data
        const purchaseOrders = @json($purchaseOrders);
        
        // References to elements
        const purchaseOrderSelect = document.getElementById('purchase_order_id');
        const addItemBtn = document.getElementById('addItemBtn');
        const itemsContainer = document.getElementById('itemsContainer');
        const itemTemplate = document.getElementById('itemTemplate');
        const grnForm = document.getElementById('grnForm');
        
        // Add item event
        addItemBtn.addEventListener('click', function() {
            addNewItem();
        });
        
        // Initialize with one item
        addNewItem();
        
        // Purchase order change handler
        purchaseOrderSelect.addEventListener('change', function() {
            // Clear existing items
            itemsContainer.innerHTML = '';
            
            const selectedPoId = this.value;
            
            if (selectedPoId) {
                // Find the selected purchase order
                const selectedPo = purchaseOrders.find(po => po.id == selectedPoId);
                
                if (selectedPo) {
                    // Auto-fill supplier
                    document.getElementById('supplier').value = selectedPo.supplier || '';
                    
                    // Add items from purchase order
                    selectedPo.items.forEach(item => {
                        if (item.quantity > item.quantity_fulfilled) {
                            addNewItem(item);
                        }
                    });
                }
            } else {
                // If no PO selected, add an empty item
                addNewItem();
            }
        });
        
        // Handle form submission
        grnForm.addEventListener('submit', function(e) {
            // Check if there are any items
            if (itemsContainer.querySelectorAll('.item-row').length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the GRN.');
                return false;
            }
            
            // Validate each item's quantities
            let valid = true;
            itemsContainer.querySelectorAll('.item-row').forEach(row => {
                const qtyReceived = parseInt(row.querySelector('.qty-received').value) || 0;
                const qtyAccepted = parseInt(row.querySelector('.qty-accepted').value) || 0;
                
                if (qtyAccepted > qtyReceived) {
                    valid = false;
                    alert('Accepted quantity cannot be greater than received quantity.');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                return false;
            }
        });
        
        // Function to add a new item row
        function addNewItem(poItem = null) {
            // Clone the template
            const itemFragment = document.importNode(itemTemplate.content, true);
            const itemRow = itemFragment.querySelector('.item-row');
            const rejectionRow = itemFragment.querySelector('.rejection-row');
            
            // Get references to inputs
            const productSelect = itemRow.querySelector('.product-select');
            const poItemId = itemRow.querySelector('.po-item-id');
            const poReference = itemRow.querySelector('.po-reference');
            const qtyOrdered = itemRow.querySelector('.qty-ordered');
            const qtyReceived = itemRow.querySelector('.qty-received');
            const qtyAccepted = itemRow.querySelector('.qty-accepted');
            const unitCost = itemRow.querySelector('.unit-cost');
            
            // Populate product options
            if (poItem) {
                // If we have a PO item, just add that product
                const option = document.createElement('option');
                option.value = poItem.product.id;
                option.textContent = poItem.product.name;
                option.selected = true;
                productSelect.appendChild(option);
                
                // Set other values from PO item
                poItemId.value = poItem.id;
                poReference.textContent = 'PO Item #' + poItem.id;
                qtyOrdered.value = poItem.quantity;
                qtyReceived.value = poItem.quantity - poItem.quantity_fulfilled;
                qtyAccepted.value = poItem.quantity - poItem.quantity_fulfilled;
                unitCost.value = poItem.unit_price;
                
                // Make product select read-only for PO items
                productSelect.disabled = true;
            } else {
                // For non-PO items, add all products
                fetch('/products/list', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    productSelect.innerHTML = '<option value="">Select Product</option>';
                    data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        productSelect.appendChild(option);
                    });
                });
            }
            
            // Handle quantity changed
            qtyReceived.addEventListener('input', function() {
                // Update accepted qty if received changes
                qtyAccepted.value = this.value;
            });
            
            qtyAccepted.addEventListener('input', function() {
                // Show/hide rejection reason row
                const received = parseInt(qtyReceived.value) || 0;
                const accepted = parseInt(this.value) || 0;
                
                if (received > accepted) {
                    rejectionRow.classList.remove('hidden');
                } else {
                    rejectionRow.classList.add('hidden');
                }
            });
            
            // Remove item button
            itemRow.querySelector('.remove-item-btn').addEventListener('click', function() {
                itemRow.remove();
                rejectionRow.remove();
            });
            
            // Add the item to the container
            itemsContainer.appendChild(itemRow);
            itemsContainer.appendChild(rejectionRow);
        }
    });
</script>
@endpush