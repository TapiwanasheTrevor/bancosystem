@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Manage Products</h1>

        <form action="/products" method="POST" enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            <!-- Product Name -->
            <div>
                <label class="block text-gray-700 font-semibold">Product Name</label>
                <input type="text" name="name" placeholder="Enter product name" required
                       class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300 outline-none">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-gray-700 font-semibold">Product Description</label>
                <textarea name="description" placeholder="Enter product description" rows="4"
                          class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300 outline-none"></textarea>
            </div>

            <!-- Base Price -->
            <div>
                <label class="block text-gray-700 font-semibold">Base Price (USD)</label>
                <input type="number" name="base_price" placeholder="Enter base price" required step="0.01"
                       class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300 outline-none">
            </div>

            <!-- Category Selection -->
            <div>
                <label class="block text-gray-700 font-semibold">Category</label>
                <select name="category_id" required
                        class="w-full p-3 border rounded-lg bg-white focus:ring focus:ring-blue-300 outline-none">
                    <option value="" disabled selected>Choose a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @foreach($category->children as $child)
                            <option value="{{ $child->id }}" class="pl-4">-- {{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <!-- Image Upload -->
            <div>
                <label class="block text-gray-700 font-semibold">Product Image</label>
                <input type="file" name="image"
                       class="w-full p-3 border rounded-lg bg-white focus:ring focus:ring-blue-300 outline-none">
            </div>

            <!-- Credit Pricing -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Credit Pricing (Interest Rate)</h3>
                <div class="grid grid-cols-2 gap-4">
                    @foreach([3, 6, 9, 12] as $months)
                        <div class="flex flex-col">
                            <label class="text-gray-700">{{ $months }} Months</label>
                            <input type="number" name="credit[{{ $months }}][interest]" placeholder="Interest %"
                                   required step="0.01"
                                   class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300 outline-none">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-right">
                <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-all">
                    Add Product
                </button>
            </div>
        </form>
    </div>
@endsection
