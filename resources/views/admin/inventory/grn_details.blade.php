@extends('layouts.app')

@section('title', 'Goods Receiving Note Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Goods Receiving Note (GRN) Details</h1>
        <div class="flex space-x-2">
            <a href="{{ route('inventory.grn.pdf', $grn->id) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700" target="_blank">
                <i class="fas fa-file-pdf mr-2"></i> Download PDF
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

    <!-- GRN Header Information -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">GRN Information</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm text-gray-600">GRN Number</p>
                    <p class="font-medium">{{ $grn->grn_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-medium">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($grn->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($grn->status == 'verified') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($grn->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Warehouse</p>
                    <p class="font-medium">{{ $grn->warehouse->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Received Date</p>
                    <p class="font-medium">{{ $grn->received_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Received By</p>
                    <p class="font-medium">{{ $grn->receivedBy->name }}</p>
                </div>
                @if($grn->verifiedBy)
                <div>
                    <p class="text-sm text-gray-600">Verified By</p>
                    <p class="font-medium">{{ $grn->verifiedBy->name }}</p>
                </div>
                @endif
                @if($grn->supplier)
                <div>
                    <p class="text-sm text-gray-600">Supplier</p>
                    <p class="font-medium">{{ $grn->supplier }}</p>
                </div>
                @endif
                @if($grn->delivery_note_number)
                <div>
                    <p class="text-sm text-gray-600">Delivery Note</p>
                    <p class="font-medium">{{ $grn->delivery_note_number }}</p>
                </div>
                @endif
                @if($grn->invoice_number)
                <div>
                    <p class="text-sm text-gray-600">Invoice Number</p>
                    <p class="font-medium">{{ $grn->invoice_number }}</p>
                </div>
                @endif
                @if($grn->purchaseOrder)
                <div>
                    <p class="text-sm text-gray-600">Purchase Order</p>
                    <p class="font-medium">
                        <a href="{{ route('purchase-orders.show', $grn->purchaseOrder->id) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $grn->purchaseOrder->po_number }}
                        </a>
                    </p>
                </div>
                @endif
            </div>
            
            @if($grn->notes)
            <div class="mt-6">
                <p class="text-sm text-gray-600">Notes</p>
                <p class="mt-1">{{ $grn->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- GRN Items -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Items Received</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Ordered</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Received</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Accepted</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Rejected</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $totalValue = 0; @endphp
                    @foreach($grn->grnItems as $item)
                    @php 
                        $itemValue = $item->quantity_accepted * $item->unit_cost;
                        $totalValue += $itemValue;
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                            <div class="text-xs text-gray-500">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->quantity_ordered ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->quantity_received }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="text-green-600 font-medium">{{ $item->quantity_accepted }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($item->quantity_rejected > 0)
                                <span class="text-red-600 font-medium">{{ $item->quantity_rejected }}</span>
                            @else
                                0
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($item->unit_cost, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($itemValue, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->batch_number ?? 'N/A' }}
                        </td>
                    </tr>
                    @if($item->rejection_reason)
                    <tr class="bg-red-50">
                        <td colspan="8" class="px-6 py-2 text-sm text-red-700">
                            <span class="font-medium">Rejection Reason:</span> {{ $item->rejection_reason }}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="6" class="px-6 py-3 text-right text-sm font-medium text-gray-700">
                            Total Value:
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ number_format($totalValue, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection