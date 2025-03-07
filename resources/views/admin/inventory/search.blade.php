@extends('layouts.app')

@section('title', 'Inventory Search')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Inventory Search</h1>
        <a href="{{ route('inventory.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <form action="{{ route('inventory.search') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-2">
                    <label for="query" class="block text-sm font-medium text-gray-700 mb-1">Search Query *</label>
                    <input type="text" id="query" name="query" value="{{ $query ?? '' }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Enter product name or description..." required>
                </div>
                
                <div>
                    <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse (Optional)</label>
                    <select id="warehouse_id" name="warehouse_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ isset($warehouseId) && $warehouseId == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
            </div>
        </form>
    </div>
    
    @if(isset($results))
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Search Results</h2>
                <p class="text-gray-600 text-sm">Found {{ count($results) }} items matching your query</p>
            </div>
            
            @if(count($results) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Warehouse
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Batch
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Unit Cost
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Expiry Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($results as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($item->product->image)
                                                <div class="flex-shrink-0 h-10 w-10 mr-3">
                                                    <img class="h-10 w-10 rounded-full object-cover" 
                                                         src="{{ asset('images/products/' . $item->product->image) }}" 
                                                         alt="{{ $item->product->name }}">
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ $item->product_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->warehouse->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->batch_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->quantity }} units</div>
                                        <div class="text-xs text-gray-500">
                                            <span class="text-green-600">{{ $item->available_quantity }} available</span>
                                            @if($item->reserved_quantity > 0)
                                                <span class="text-orange-600 ml-2">{{ $item->reserved_quantity }} reserved</span>
                                            @endif
                                            @if($item->damaged_quantity > 0)
                                                <span class="text-red-600 ml-2">{{ $item->damaged_quantity }} damaged</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">${{ number_format($item->unit_cost, 2) }}</div>
                                        <div class="text-xs text-gray-500">Value: ${{ number_format($item->quantity * $item->unit_cost, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->expiry_date)
                                            <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</div>
                                            @php
                                                $daysToExpiry = \Carbon\Carbon::now()->diffInDays($item->expiry_date, false);
                                            @endphp
                                            @if($daysToExpiry < 0)
                                                <div class="text-xs text-red-600">Expired</div>
                                            @elseif($daysToExpiry < 30)
                                                <div class="text-xs text-orange-600">{{ $daysToExpiry }} days left</div>
                                            @else
                                                <div class="text-xs text-green-600">{{ $daysToExpiry }} days left</div>
                                            @endif
                                        @else
                                            <div class="text-sm text-gray-500">N/A</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->condition === 'good' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($item->condition) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('inventory.show', $item->id) }}" class="text-indigo-600 hover:text-indigo-900 mx-1">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('inventory.edit', $item->id) }}" class="text-blue-600 hover:text-blue-900 mx-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('inventory.adjust', $item->id) }}" class="text-green-600 hover:text-green-900 mx-1">
                                            <i class="fas fa-balance-scale"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center">
                    <p class="text-gray-500">No inventory items found matching your search criteria.</p>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection