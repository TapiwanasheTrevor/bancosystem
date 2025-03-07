@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create Purchase Order</h1>
        <a href="{{ route('purchase-orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i> Back to Purchase Orders
        </a>
    </div>

    <!-- Alert for errors -->
    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Validation Error</p>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Purchase Order Form -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
            @csrf
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold mb-4">Purchase Order Information</h2>
                
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Form Selection -->
                    <div>
                        <label for="form_id" class="block text-sm font-medium text-gray-700 mb-1">Application (Optional)</label>
                        <select id="form_id" name="form_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select an application</option>
                            @foreach($forms as $form)
                            <option value="{{ $form->id }}" {{ old('form_id') == $form->id ? 'selected' : '' }}>
                                {{ $form->applicant_name }} - {{ $form->form_name }} (#{{ $form->id }})
                            </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Optional: Select if this purchase order is for a specific application</p>
                    </div>

                    <!-- Order Date -->
                    <div>
                        <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1">Order Date*</label>
                        <input type="date" id="order_date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Expected Delivery Date -->
                    <div>
                        <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery Date*</label>
                        <input type="date" id="expected_delivery_date" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Supplier -->
                    <div>
                        <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                        <input type="text" id="supplier" name="supplier" value="{{ old('supplier') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Supplier Contact -->
                    <div>
                        <label for="supplier_contact" class="block text-sm font-medium text-gray-700 mb-1">Supplier Contact</label>
                        <input type="text" id="supplier_contact" name="supplier_contact" value="{{ old('supplier_contact') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
                
                <h2 class="text-lg font-semibold mb-4">Order Items</h2>
                
                <!-- Order Items Table -->
                <div class="mb-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product*</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity*</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price*</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="orderItems">
                            <tr class="item-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="product_id[]" class="product-select w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->base_price }}">
                                            {{ $product->name }} - ${{ number_format($product->base_price, 2) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="quantity[]" min="1" value="1" class="quantity-input w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="unit_price[]" step="0.01" min="0" class="unit-price-input w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="line-total">$0.00</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button type="button" class="remove-item text-red-600 hover:text-red-900" title="Remove Item" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-center">
                    <button type="button" id="addItemBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-2"></i> Add Item
                    </button>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Total: <span id="orderTotal" class="font-bold">$0.00</span></p>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 text-right">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Create Purchase Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add new item row
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const itemsTable = document.getElementById('orderItems');
        const newRow = document.querySelector('.item-row').cloneNode(true);
        
        // Reset inputs in the new row
        newRow.querySelector('.product-select').value = '';
        newRow.querySelector('.quantity-input').value = 1;
        newRow.querySelector('.unit-price-input').value = '';
        newRow.querySelector('.line-total').textContent = '$0.00';
        
        // Enable remove button for all rows except the first if there's only one
        const removeBtn = newRow.querySelector('.remove-item');
        removeBtn.disabled = false;
        
        // Add event listeners to the new row
        addRowEventListeners(newRow);
        
        // Add to table
        itemsTable.appendChild(newRow);
        
        // Enable remove button for the first row if we now have multiple rows
        if (document.querySelectorAll('.item-row').length > 1) {
            document.querySelector('.item-row .remove-item').disabled = false;
        }
    });
    
    // Add event listeners to the initial row
    addRowEventListeners(document.querySelector('.item-row'));
    
    // Handle form submission
    document.getElementById('poForm').addEventListener('submit', function(event) {
        const productSelects = document.querySelectorAll('.product-select');
        let hasProducts = false;
        
        productSelects.forEach(select => {
            if (select.value) {
                hasProducts = true;
            }
        });
        
        if (!hasProducts) {
            event.preventDefault();
            alert('Please add at least one product to the purchase order.');
        }
    });
    
    // Functions to add event listeners to a row
    function addRowEventListeners(row) {
        // Product selection changes
        const productSelect = row.querySelector('.product-select');
        productSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const price = option.dataset.price || 0;
            row.querySelector('.unit-price-input').value = price;
            updateRowTotal(row);
        });
        
        // Quantity or unit price changes
        row.querySelector('.quantity-input').addEventListener('input', function() {
            updateRowTotal(row);
        });
        
        row.querySelector('.unit-price-input').addEventListener('input', function() {
            updateRowTotal(row);
        });
        
        // Remove item
        row.querySelector('.remove-item').addEventListener('click', function() {
            row.remove();
            updateOrderTotal();
            
            // Disable remove button on the first row if it's the only one left
            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 1) {
                rows[0].querySelector('.remove-item').disabled = true;
            }
        });
    }
    
    // Update row total
    function updateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const total = quantity * unitPrice;
        
        row.querySelector('.line-total').textContent = '$' + total.toFixed(2);
        
        updateOrderTotal();
    }
    
    // Update order total
    function updateOrderTotal() {
        let total = 0;
        document.querySelectorAll('.line-total').forEach(function(element) {
            const value = parseFloat(element.textContent.replace('$', '')) || 0;
            total += value;
        });
        
        document.getElementById('orderTotal').textContent = '$' + total.toFixed(2);
    }
});
</script>
@endpush