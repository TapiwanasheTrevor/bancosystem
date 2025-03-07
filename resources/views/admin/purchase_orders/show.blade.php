@extends('layouts.app')

@section('title', 'Purchase Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Purchase Order: {{ $purchaseOrder->po_number }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('purchase-orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
            <a href="{{ route('purchase-orders.pdf', $purchaseOrder->id) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fas fa-file-pdf mr-2"></i> Download PDF
            </a>
            @if(in_array($purchaseOrder->status, ['draft', 'pending']))
            <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            @endif
            @if($purchaseOrder->status == 'pending')
            <a href="{{ route('inventory.grn.create-from-po', $purchaseOrder->id) }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                <i class="fas fa-truck-loading mr-2"></i> Create GRN
            </a>
            @endif
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

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Purchase Order Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic PO Info -->
                <div>
                    <div class="mb-4">
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
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Order Date</p>
                        <p class="font-medium">{{ $purchaseOrder->order_date->format('M d, Y') }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Expected Delivery Date</p>
                        <p class="font-medium">{{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Created By</p>
                        <p class="font-medium">{{ $purchaseOrder->creator->name ?? 'Unknown' }}</p>
                    </div>
                </div>
                
                <!-- Supplier Info -->
                <div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Supplier</p>
                        <p class="font-medium">{{ $purchaseOrder->supplier ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Supplier Contact</p>
                        <p class="font-medium">{{ $purchaseOrder->supplier_contact ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Related Application</p>
                        <p class="font-medium">
                            @if($purchaseOrder->form)
                            <a href="#" class="text-indigo-600 hover:text-indigo-900">
                                {{ $purchaseOrder->form->applicant_name }} - {{ $purchaseOrder->form->form_name }}
                            </a>
                            @else
                            None
                            @endif
                        </p>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600">Notes</p>
                    <p class="font-medium">{{ $purchaseOrder->notes ?? 'No notes provided' }}</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Order Items</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fulfilled</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($purchaseOrder->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $item->product->name ?? 'Unknown Product' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${{ number_format($item->total_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($item->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($item->status == 'partial') bg-purple-100 text-purple-800
                                    @elseif($item->status == 'fulfilled') bg-green-100 text-green-800
                                    @elseif($item->status == 'cancelled') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $item->quantity_fulfilled }} / {{ $item->quantity }}
                                @if($item->quantity > 0)
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-1">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min(100, ($item->quantity_fulfilled / $item->quantity) * 100) }}%"></div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                No items found in this purchase order.
                            </td>
                        </tr>
                        @endforelse
                        
                        <!-- Total Row -->
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                Total:
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold">
                                ${{ number_format($purchaseOrder->total_amount, 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Change Form -->
    @if(in_array($purchaseOrder->status, ['pending', 'approved', 'partially_fulfilled']))
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Change Status</h2>
            
            <form action="{{ route('purchase-orders.change-status', $purchaseOrder->id) }}" method="POST" class="flex flex-col md:flex-row md:items-end gap-4">
                @csrf
                <div class="w-full md:w-1/3">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
                    <select id="status" name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @if($purchaseOrder->status == 'pending')
                        <option value="approved">Approve</option>
                        <option value="cancelled">Cancel</option>
                        @elseif(in_array($purchaseOrder->status, ['approved', 'partially_fulfilled']))
                        <option value="cancelled">Cancel</option>
                        @endif
                    </select>
                </div>
                
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Update Status
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Goods Receiving Notes -->
    @if($purchaseOrder->goodsReceivingNotes && $purchaseOrder->goodsReceivingNotes->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Goods Receiving Notes</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($purchaseOrder->goodsReceivingNotes as $grn)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('inventory.grn.show', $grn->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $grn->grn_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $grn->received_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $grn->warehouse->name ?? 'Unknown Warehouse' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($grn->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($grn->status == 'verified') bg-green-100 text-green-800
                                    @elseif($grn->status == 'rejected') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($grn->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <a href="{{ route('inventory.grn.show', $grn->id) }}" class="text-indigo-600 hover:text-indigo-900" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.grn.pdf', $grn->id) }}" class="text-green-600 hover:text-green-900" title="Download PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection