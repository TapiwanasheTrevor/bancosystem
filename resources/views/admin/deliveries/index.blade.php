@extends('layouts.app')

@section('title', 'Product Deliveries')

@section('content')
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Product Deliveries</h1>
            <a href="{{ route('admin.deliveries.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i> Create New Delivery
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">ID</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tracking #</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Product</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Current Location</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Est. Delivery Date</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($deliveries as $delivery)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $delivery->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium">{{ $delivery->tracking_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $delivery->product->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium 
                                    {{ match($delivery->status) {
                                        'pending' => 'bg-gray-100 text-gray-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'dispatched' => 'bg-indigo-100 text-indigo-800',
                                        'in_transit' => 'bg-purple-100 text-purple-800',
                                        'at_station' => 'bg-yellow-100 text-yellow-800',
                                        'out_for_delivery' => 'bg-orange-100 text-orange-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'delayed' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    } }}">
                                    {{ ucwords(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $delivery->current_location ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $delivery->estimated_delivery_date ? $delivery->estimated_delivery_date->format('Y-m-d') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" class="text-blue-600 hover:text-blue-800 mr-2">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">No product deliveries found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="px-4 py-3 border-t">
                {{ $deliveries->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // No DataTables initialization - using Laravel's pagination instead
    });
</script>
@endpush