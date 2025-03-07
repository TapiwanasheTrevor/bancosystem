@extends('layouts.app')

@section('title', 'Warehouse Inventory Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Warehouse Inventory Report</h1>
        <div class="flex space-x-2">
            <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fas fa-print mr-2"></i> Print Report
            </button>
            <a href="{{ route('inventory.warehouses.manage') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to Warehouses
            </a>
        </div>
    </div>

    <!-- Warehouse Information -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Warehouse Information</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm text-gray-600">Name</p>
                    <p class="font-medium">{{ $warehouse->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Location</p>
                    <p class="font-medium">{{ $warehouse->location ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-medium">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $warehouse->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                @if($warehouse->contact_person)
                <div>
                    <p class="text-sm text-gray-600">Contact Person</p>
                    <p class="font-medium">{{ $warehouse->contact_person }}</p>
                </div>
                @endif
                @if($warehouse->contact_number)
                <div>
                    <p class="text-sm text-gray-600">Contact Number</p>
                    <p class="font-medium">{{ $warehouse->contact_number }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Inventory Summary -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Inventory Summary</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-blue-100 rounded-lg p-4">
                    <p class="text-sm text-blue-600 mb-1">Total Products</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $productCount }}</p>
                </div>
                <div class="bg-green-100 rounded-lg p-4">
                    <p class="text-sm text-green-600 mb-1">Total Inventory Value</p>
                    <p class="text-2xl font-bold text-green-800">${{ number_format($totalValue, 2) }}</p>
                </div>
                <div class="bg-purple-100 rounded-lg p-4">
                    <p class="text-sm text-purple-600 mb-1">Total Items</p>
                    <p class="text-2xl font-bold text-purple-800">{{ $inventoryItems->sum('quantity') }}</p>
                </div>
                <div class="bg-yellow-100 rounded-lg p-4">
                    <p class="text-sm text-yellow-600 mb-1">Available Items</p>
                    <p class="text-2xl font-bold text-yellow-800">{{ $inventoryItems->sum('available_quantity') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Items Table -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Inventory Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="inventoryTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventoryItems as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $item->product->sku ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $item->batch_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->available_quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->reserved_quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->damaged_quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($item->unit_cost, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($item->getValue(), 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->received_date ? $item->received_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->expiry_date ? $item->expiry_date->format('M d, Y') : 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No inventory items found in this warehouse.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-700">
                            Totals:
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $inventoryItems->sum('quantity') }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $inventoryItems->sum('available_quantity') }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $inventoryItems->sum('reserved_quantity') }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $inventoryItems->sum('damaged_quantity') }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            <!-- No sum for unit cost -->
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ number_format($totalValue, 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style type="text/css" media="print">
    @page {
        size: landscape;
    }
    
    header, .bg-indigo-900, button, a {
        display: none !important;
    }
    
    body {
        background-color: white !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .shadow {
        box-shadow: none !important;
    }
    
    table {
        font-size: 10px !important;
    }
    
    #inventoryTable th, #inventoryTable td {
        padding: 4px 6px !important;
    }
</style>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add any JavaScript for sorting or filtering inventory items here
    });
</script>
@endpush