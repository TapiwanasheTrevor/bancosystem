@extends('layouts.app')

@section('title', 'Delivery Details')

@section('content')
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Delivery Details</h1>
            <a href="{{ route('admin.deliveries.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Delivery Information Card -->
            <div class="md:col-span-2 bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-indigo-600 text-white px-4 py-3">
                    <h2 class="font-bold">Delivery Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600 text-sm">Tracking Number:</p>
                            <p class="font-medium">{{ $delivery->tracking_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Current Status:</p>
                            <p>
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
                                    {{ $delivery->status_label }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Current Location:</p>
                            <p>{{ $delivery->current_location ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Estimated Delivery:</p>
                            <p>{{ $delivery->estimated_delivery_date ? $delivery->estimated_delivery_date->format('Y-m-d') : 'Not scheduled' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Created Date:</p>
                            <p>{{ $delivery->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Last Updated:</p>
                            <p>{{ $delivery->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Actual Delivery Date:</p>
                            <p>{{ $delivery->actual_delivery_date ? $delivery->actual_delivery_date->format('Y-m-d') : 'Not delivered yet' }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-gray-600 text-sm">Status Notes:</p>
                        <p class="mt-1 p-2 bg-gray-50 rounded">{{ $delivery->status_notes ?? 'No notes available' }}</p>
                    </div>
                </div>
            </div>

            <!-- Product and Customer Information -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-indigo-600 text-white px-4 py-3">
                    <h2 class="font-bold">Product & Customer</h2>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-gray-600 text-sm">Product:</p>
                        <p class="font-medium">{{ $delivery->product->name }}</p>
                    </div>

                    @if($delivery->product->image)
                        <div class="mb-4">
                            <img src="{{ asset($delivery->product->image) }}" alt="{{ $delivery->product->name }}" class="w-full h-32 object-cover rounded">
                        </div>
                    @endif

                    <div class="mb-4">
                        <p class="text-gray-600 text-sm">Customer:</p>
                        <p>{{ $delivery->form->applicant_name ?? 'N/A' }}</p>
                    </div>

                    <div class="mb-4">
                        <p class="text-gray-600 text-sm">Contact:</p>
                        <p>{{ $delivery->form->applicant_phone ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-600 text-sm">Application ID:</p>
                        <p>{{ $delivery->form->id ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Status Form -->
        <div class="mt-6 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-indigo-600 text-white px-4 py-3">
                <h2 class="font-bold">Update Delivery Status</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.deliveries.update-status', $delivery) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" name="status" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="pending" {{ $delivery->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $delivery->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="dispatched" {{ $delivery->status == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                <option value="in_transit" {{ $delivery->status == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="at_station" {{ $delivery->status == 'at_station' ? 'selected' : '' }}>At Station</option>
                                <option value="out_for_delivery" {{ $delivery->status == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                <option value="delivered" {{ $delivery->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="delayed" {{ $delivery->status == 'delayed' ? 'selected' : '' }}>Delayed</option>
                                <option value="cancelled" {{ $delivery->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" id="location" name="location" value="{{ $delivery->current_location }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Status Notes</label>
                            <textarea id="notes" name="notes" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status History -->
        <div class="mt-6 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-indigo-600 text-white px-4 py-3">
                <h2 class="font-bold">Status History</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Date & Time</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Location</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($delivery->statusUpdates as $update)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $update->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            {{ match($update->status) {
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
                                            {{ $update->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $update->location ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $update->notes ?? 'No notes' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-center text-gray-500">No status updates recorded</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection