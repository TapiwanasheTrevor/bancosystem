@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
        <div class="flex space-x-2">
            <a href="{{ route('inventory.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i> Add Inventory
            </a>
            <a href="{{ route('inventory.search') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i> Search
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Filter Options -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filter Options</h2>
        <form action="{{ route('inventory.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="w-full md:w-1/4">
                <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                <select id="warehouse_id" name="warehouse_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-1/4">
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                <select id="product_id" name="product_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-1/4">
                <label for="condition" class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                <select id="condition" name="condition" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Conditions</option>
                    <option value="good" {{ request('condition') == 'good' ? 'selected' : '' }}>Good</option>
                    <option value="damaged" {{ request('condition') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="expired" {{ request('condition') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            
            <div class="w-full md:w-auto flex items-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventoryItems as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $item->product->name ?? 'Unknown Product' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        SKU: {{ $item->product->id ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $item->warehouse->name ?? 'Unknown Warehouse' }}</div>
                            <div class="text-xs text-gray-500">{{ $item->storage_location ?? 'No specific location' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $item->batch_number ?? 'No batch' }}</div>
                            @if($item->expiry_date)
                            <div class="text-xs text-gray-500">
                                Expires: {{ $item->expiry_date->format('M d, Y') }}
                                @if($item->isExpired())
                                <span class="text-red-600 font-bold">(Expired)</span>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->available_quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->reserved_quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->damaged_quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${{ number_format($item->getValue(), 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($item->condition == 'good') bg-green-100 text-green-800
                                @elseif($item->condition == 'damaged') bg-yellow-100 text-yellow-800
                                @elseif($item->condition == 'expired') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($item->condition) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('inventory.show', $item->id) }}" class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('inventory.edit', $item->id) }}" class="text-blue-600 hover:text-blue-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('inventory.adjust', $item->id) }}" class="text-green-600 hover:text-green-900" title="Adjust Inventory">
                                    <i class="fas fa-balance-scale"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                            No inventory items found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $inventoryItems->links() }}
    </div>
    
    <!-- Quick Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-800 mb-2">Total Inventory Value</h3>
            <p class="text-2xl font-bold text-indigo-600">${{ number_format($inventoryItems->sum(function($item) { return $item->getValue(); }), 2) }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-800 mb-2">Total Items</h3>
            <p class="text-2xl font-bold text-indigo-600">{{ $inventoryItems->sum('quantity') }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-800 mb-2">Available Items</h3>
            <p class="text-2xl font-bold text-indigo-600">{{ $inventoryItems->sum('available_quantity') }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-800 mb-2">Unique Products</h3>
            <p class="text-2xl font-bold text-indigo-600">{{ $inventoryItems->unique('product_id')->count() }}</p>
        </div>
    </div>
</div>
@endsection