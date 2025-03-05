@extends('layouts.app')

@section('title', 'Create Delivery Record')

@section('content')
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Create Delivery Record</h1>
            <a href="{{ route('admin.deliveries.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-hidden p-6">
            <form action="{{ route('admin.deliveries.store') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="form_id" class="block text-sm font-medium text-gray-700 mb-2">Application Form</label>
                    <select id="form_id" name="form_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Select an Application</option>
                        @foreach($forms as $form)
                            <option value="{{ $form->id }}" {{ old('form_id') == $form->id ? 'selected' : '' }}>
                                #{{ $form->id }} - {{ $form->applicant_name ?? 'Unnamed' }} 
                                ({{ $form->form_name ?? 'Unknown Form' }})
                            </option>
                        @endforeach
                    </select>
                    @error('form_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                    <select id="product_id" name="product_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Select a Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Initial Status</label>
                    <select id="status" name="status" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="dispatched" {{ old('status') == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                        <option value="in_transit" {{ old('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="at_station" {{ old('status') == 'at_station' ? 'selected' : '' }}>At Station</option>
                        <option value="out_for_delivery" {{ old('status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="current_location" class="block text-sm font-medium text-gray-700 mb-2">Current Location</label>
                    <input type="text" id="current_location" name="current_location" value="{{ old('current_location') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('current_location')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="estimated_delivery_date" class="block text-sm font-medium text-gray-700 mb-2">Estimated Delivery Date</label>
                    <input type="date" id="estimated_delivery_date" name="estimated_delivery_date" value="{{ old('estimated_delivery_date') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('estimated_delivery_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="status_notes" class="block text-sm font-medium text-gray-700 mb-2">Status Notes</label>
                    <textarea id="status_notes" name="status_notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('status_notes') }}</textarea>
                    @error('status_notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Create Delivery Record
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection