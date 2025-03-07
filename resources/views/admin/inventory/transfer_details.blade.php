@extends('layouts.app')

@section('title', 'Inventory Transfer Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Inventory Transfer Details</h1>
        <div class="flex space-x-2">
            <a href="{{ route('inventory.transfers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i> New Transfer
            </a>
            <a href="{{ route('inventory.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Transfer Information Card -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Transfer Information</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm text-gray-600">Transfer Number</p>
                    <p class="font-medium">{{ $transfer->transfer_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-medium">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($transfer->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($transfer->status == 'in_transit') bg-blue-100 text-blue-800
                            @elseif($transfer->status == 'completed') bg-green-100 text-green-800
                            @elseif($transfer->status == 'cancelled') bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Transfer Date</p>
                    <p class="font-medium">{{ $transfer->transfer_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Source Warehouse</p>
                    <p class="font-medium">{{ $transfer->sourceWarehouse->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Destination Warehouse</p>
                    <p class="font-medium">{{ $transfer->destinationWarehouse->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Created By</p>
                    <p class="font-medium">{{ $transfer->creator->name }}</p>
                </div>
                @if($transfer->completed_date)
                <div>
                    <p class="text-sm text-gray-600">Completed Date</p>
                    <p class="font-medium">{{ $transfer->completed_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($transfer->approver)
                <div>
                    <p class="text-sm text-gray-600">Approved By</p>
                    <p class="font-medium">{{ $transfer->approver->name }}</p>
                </div>
                @endif
            </div>
            
            @if($transfer->notes)
            <div class="mt-6">
                <p class="text-sm text-gray-600">Notes</p>
                <p class="mt-1">{{ $transfer->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Transfer Items -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Transfer Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        @if($transfer->status == 'completed' || $transfer->status == 'partially_fulfilled')
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Quantity</th>
                        @endif
                        @if($transfer->status == 'pending' || $transfer->status == 'in_transit')
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($transfer->transferItems as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                            <div class="text-xs text-gray-500">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->batch_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->quantity }}
                        </td>
                        @if($transfer->status == 'completed' || $transfer->status == 'partially_fulfilled')
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium {{ $item->received_quantity < $item->quantity ? 'text-yellow-600' : 'text-green-600' }}">
                                {{ $item->received_quantity }}
                            </span>
                            @if($item->received_quantity < $item->quantity)
                            <span class="text-xs text-red-500 block">
                                Missing: {{ $item->quantity - $item->received_quantity }}
                            </span>
                            @endif
                        </td>
                        @endif
                        @if($transfer->status == 'pending' || $transfer->status == 'in_transit')
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $transfer->status == 'pending' ? 'Pending' : 'In Transit' }}
                            </span>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($transfer->status == 'pending' || $transfer->status == 'in_transit')
    <div class="flex justify-end space-x-4 mb-6">
        <form action="{{ route('inventory.transfers.cancel', $transfer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this transfer?');">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                <i class="fas fa-times mr-2"></i> Cancel Transfer
            </button>
        </form>
        
        @if($transfer->status == 'pending')
        <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700" onclick="document.getElementById('receiveTransferModal').classList.remove('hidden')">
            <i class="fas fa-check mr-2"></i> Process Receipt
        </button>
        @endif
    </div>
    @endif
</div>

<!-- Receive Transfer Modal -->
@if($transfer->status == 'pending' || $transfer->status == 'in_transit')
<div id="receiveTransferModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-4xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Process Transfer Receipt</h2>
            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="document.getElementById('receiveTransferModal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="{{ route('inventory.transfers.complete', $transfer->id) }}" method="POST">
            @csrf
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-4">
                    Enter the actual quantities received at {{ $transfer->destinationWarehouse->name }}. If the received 
                    quantity is less than the transferred quantity, the difference will remain reserved at the source warehouse.
                </p>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transferred</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transfer->transferItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->batch_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="number" name="received_quantity[{{ $item->id }}]" value="{{ $item->quantity }}" 
                                        min="0" max="{{ $item->quantity }}" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Receipt Notes</label>
                <textarea id="notes" name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter any notes about the receipt..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300" onclick="document.getElementById('receiveTransferModal').classList.add('hidden')">
                    Cancel
                </button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    Complete Transfer
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection