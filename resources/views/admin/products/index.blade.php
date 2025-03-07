@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manage Products</h1>
            <div class="flex space-x-4">
                <a href="/products?type=microbiz" class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
                    MicroBiz Products
                </a>
                <a href="/products?type=hirepurchase" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                    Hire Purchase Products
                </a>
            </div>
        </div>

        <form action="/products" method="POST" enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            
            <!-- Catalog Type Selection -->
            <div>
                <label class="block text-gray-700 font-semibold">Catalog Type</label>
                <select name="catalog_type" id="catalog_type" required
                        class="w-full p-3 border rounded-lg bg-white focus:ring focus:ring-blue-300 outline-none">
                    <option value="microbiz">MicroBiz</option>
                    <option value="hirepurchase">Hire Purchase</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">Select the catalog type to filter categories accordingly</p>
            </div>
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
                        <option value="{{ $category->id }}" data-catalog-type="{{ $category->catalog_type }}">
                            {{ $category->name }} ({{ $category->catalog_type == 'hirepurchase' ? 'Hire Purchase' : 'MicroBiz' }})
                        </option>
                        @foreach($category->children as $child)
                            <option value="{{ $child->id }}" data-catalog-type="{{ $child->catalog_type }}" class="pl-4">
                                -- {{ $child->name }} ({{ $child->catalog_type == 'hirepurchase' ? 'Hire Purchase' : 'MicroBiz' }})
                            </option>
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
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Credit Pricing</h3>
                <div class="grid grid-cols-2 gap-4">
                    @foreach([3, 6, 9, 12] as $months)
                        <div class="flex flex-col space-y-2 border rounded-lg p-4">
                            <label class="text-gray-700 font-medium">{{ $months }} Months</label>
                            
                            <!-- Interest Rate -->
                            <div>
                                <label class="text-sm text-gray-600">Interest (%)</label>
                                <input type="number" name="credit[{{ $months }}][interest]" placeholder="Interest %"
                                    step="0.01"
                                    class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-300 outline-none">
                            </div>
                            
                            <!-- Flat Fee / Installment Amount -->
                            <div>
                                <label class="text-sm text-gray-600">Monthly Installment (USD)</label>
                                <input type="number" name="credit[{{ $months }}][installment_amount]" 
                                    placeholder="Amount per month"
                                    step="0.01"
                                    class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-300 outline-none">
                                <p class="text-xs text-gray-500 mt-1">Fixed monthly payment amount</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-sm text-gray-500 mt-2">Note: You can specify either an interest rate or a fixed monthly installment amount (or both).</p>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const catalogTypeSelector = document.getElementById('catalog_type');
        const categorySelector = document.querySelector('select[name="category_id"]');
        
        // Filter categories based on selected catalog type
        catalogTypeSelector.addEventListener('change', function() {
            const selectedCatalogType = this.value;
            
            // Hide all options first
            Array.from(categorySelector.options).forEach(option => {
                if (option.dataset.catalogType !== selectedCatalogType && option.value !== "") {
                    option.style.display = 'none';
                } else {
                    option.style.display = 'block';
                }
            });
            
            // Reset selection if current selection is now hidden
            if (categorySelector.selectedOptions[0].style.display === 'none') {
                categorySelector.value = "";
            }
        });
        
        // Initialize with URL parameter if present
        const urlParams = new URLSearchParams(window.location.search);
        const typeParam = urlParams.get('type');
        
        if (typeParam) {
            catalogTypeSelector.value = typeParam;
            // Trigger change event to filter categories
            catalogTypeSelector.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush
