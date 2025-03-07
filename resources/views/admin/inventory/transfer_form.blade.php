@extends('layouts.app')

@section('title', 'Create Inventory Transfer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create Inventory Transfer</h1>
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
        <form action="{{ route('inventory.transfers.store') }}" method="POST" id="transferForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="source_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Source Warehouse *</label>
                    <select id="source_warehouse_id" name="source_warehouse_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Source Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('source_warehouse_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="destination_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Destination Warehouse *</label>
                    <select id="destination_warehouse_id" name="destination_warehouse_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Destination Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('destination_warehouse_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1">Transfer Date *</label>
                    <input type="date" id="transfer_date" name="transfer_date" value="{{ date('Y-m-d') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('transfer_date')
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
                <h2 class="text-lg font-semibold mb-4">Transfer Items</h2>
                
                <div id="productsList" class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Select source warehouse to view available items</p>
                </div>
                
                <div id="selectedItems" class="overflow-x-auto hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Qty</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Qty</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transferItemsContainer" class="bg-white divide-y divide-gray-200">
                            <!-- Transfer items will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700" id="submitBtn" disabled>
                    <i class="fas fa-exchange-alt mr-2"></i> Create Transfer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Item template for dynamic addition -->
<template id="transferItemTemplate">
    <tr class="transfer-item-row">
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900 product-name"></div>
            <input type="hidden" name="product_id[]" class="product-id">
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500 batch-number"></div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500 available-qty"></div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <input type="number" name="quantity[]" class="quantity-input w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="1" required>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <button type="button" class="remove-item-btn text-red-600 hover:text-red-900">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sourceWarehouseSelect = document.getElementById('source_warehouse_id');
        const destinationWarehouseSelect = document.getElementById('destination_warehouse_id');
        const productsListContainer = document.getElementById('productsList');
        const selectedItemsContainer = document.getElementById('selectedItems');
        const transferItemsContainer = document.getElementById('transferItemsContainer');
        const transferItemTemplate = document.getElementById('transferItemTemplate');
        const submitBtn = document.getElementById('submitBtn');
        const transferForm = document.getElementById('transferForm');
        
        let selectedItems = [];
        
        // Source warehouse change
        sourceWarehouseSelect.addEventListener('change', function() {
            const warehouseId = this.value;
            
            // Reset destination warehouse if same as source
            if (destinationWarehouseSelect.value === warehouseId) {
                destinationWarehouseSelect.value = '';
            }
            
            // Clear current items
            selectedItems = [];
            transferItemsContainer.innerHTML = '';
            selectedItemsContainer.classList.add('hidden');
            submitBtn.disabled = true;
            
            if (!warehouseId) {
                productsListContainer.innerHTML = '<p class="text-sm text-gray-600 mb-2">Select source warehouse to view available items</p>';
                return;
            }
            
            // Show loading
            productsListContainer.innerHTML = '<p class="text-sm text-gray-600 mb-2">Loading available items...</p>';
            
            // Fetch available items from the warehouse
            fetch(`/api/inventory/warehouse/${warehouseId}/available-items`)
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        renderAvailableItems(data.items);
                    } else {
                        productsListContainer.innerHTML = '<p class="text-sm text-gray-600 mb-2">No available items in this warehouse</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                    productsListContainer.innerHTML = '<p class="text-sm text-red-600 mb-2">Error loading items. Please try again.</p>';
                });
        });
        
        // Destination warehouse change
        destinationWarehouseSelect.addEventListener('change', function() {
            const sourceId = sourceWarehouseSelect.value;
            const destinationId = this.value;
            
            // Validation
            if (sourceId && destinationId && sourceId === destinationId) {
                alert('Source and destination warehouses cannot be the same.');
                this.value = '';
            }
            
            validateForm();
        });
        
        // Render available items as selectable cards
        function renderAvailableItems(items) {
            let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
            
            items.forEach(item => {
                html += `
                <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer item-card" data-id="${item.id}" data-product-id="${item.product_id}" data-product-name="${item.product.name}" data-batch="${item.batch_number}" data-available="${item.available_quantity}">
                    <h3 class="font-medium text-gray-900">${item.product.name}</h3>
                    <p class="text-sm text-gray-500">Batch: ${item.batch_number}</p>
                    <p class="text-sm text-gray-500">Available: ${item.available_quantity}</p>
                    <button type="button" class="mt-2 bg-indigo-100 text-indigo-700 px-3 py-1 rounded text-sm hover:bg-indigo-200">
                        <i class="fas fa-plus mr-1"></i> Add to Transfer
                    </button>
                </div>
                `;
            });
            
            html += '</div>';
            productsListContainer.innerHTML = html;
            
            // Add event listeners to item cards
            document.querySelectorAll('.item-card').forEach(card => {
                card.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-id');
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.getAttribute('data-product-name');
                    const batchNumber = this.getAttribute('data-batch');
                    const availableQty = parseInt(this.getAttribute('data-available'));
                    
                    // Check if already added
                    if (selectedItems.some(item => item.id === itemId)) {
                        alert('This item is already added to the transfer.');
                        return;
                    }
                    
                    // Add to selected items
                    selectedItems.push({
                        id: itemId,
                        product_id: productId,
                        product_name: productName,
                        batch_number: batchNumber,
                        available_quantity: availableQty
                    });
                    
                    // Add to transfer items table
                    addTransferItemRow({
                        product_id: productId,
                        product_name: productName,
                        batch_number: batchNumber,
                        available_quantity: availableQty
                    });
                    
                    // Show selected items container if first item
                    if (selectedItems.length === 1) {
                        selectedItemsContainer.classList.remove('hidden');
                    }
                    
                    validateForm();
                });
            });
        }
        
        // Add transfer item row
        function addTransferItemRow(item) {
            const rowFragment = document.importNode(transferItemTemplate.content, true);
            const row = rowFragment.querySelector('.transfer-item-row');
            
            // Set values
            row.querySelector('.product-name').textContent = item.product_name;
            row.querySelector('.product-id').value = item.product_id;
            row.querySelector('.batch-number').textContent = item.batch_number;
            row.querySelector('.available-qty').textContent = item.available_quantity;
            
            const quantityInput = row.querySelector('.quantity-input');
            quantityInput.setAttribute('max', item.available_quantity);
            quantityInput.value = 1; // Default to 1
            
            // Quantity input validation
            quantityInput.addEventListener('input', function() {
                const val = parseInt(this.value) || 0;
                const max = parseInt(this.getAttribute('max'));
                
                if (val > max) {
                    this.value = max;
                    alert(`Maximum available quantity is ${max}.`);
                } else if (val < 1) {
                    this.value = 1;
                }
                
                validateForm();
            });
            
            // Remove button handler
            row.querySelector('.remove-item-btn').addEventListener('click', function() {
                const productId = row.querySelector('.product-id').value;
                const batchNumber = row.querySelector('.batch-number').textContent;
                
                // Remove from selected items array
                selectedItems = selectedItems.filter(item => 
                    !(item.product_id === productId && item.batch_number === batchNumber)
                );
                
                // Remove row
                row.remove();
                
                // Hide container if no items
                if (selectedItems.length === 0) {
                    selectedItemsContainer.classList.add('hidden');
                }
                
                validateForm();
            });
            
            // Add to container
            transferItemsContainer.appendChild(row);
        }
        
        // Validate form
        function validateForm() {
            const sourceId = sourceWarehouseSelect.value;
            const destinationId = destinationWarehouseSelect.value;
            const hasItems = selectedItems.length > 0;
            
            submitBtn.disabled = !sourceId || !destinationId || !hasItems;
        }
        
        // Form submission validation
        transferForm.addEventListener('submit', function(e) {
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the transfer.');
                return false;
            }
            
            // Validate destination warehouse
            if (sourceWarehouseSelect.value === destinationWarehouseSelect.value) {
                e.preventDefault();
                alert('Source and destination warehouses cannot be the same.');
                return false;
            }
            
            // Validate quantities
            let valid = true;
            document.querySelectorAll('.quantity-input').forEach(input => {
                const val = parseInt(input.value) || 0;
                const max = parseInt(input.getAttribute('max'));
                
                if (val <= 0) {
                    valid = false;
                    alert('Quantity must be greater than 0.');
                } else if (val > max) {
                    valid = false;
                    alert(`Maximum available quantity is ${max}.`);
                }
            });
            
            if (!valid) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endpush