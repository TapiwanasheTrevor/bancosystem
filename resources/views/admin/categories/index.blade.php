@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
    <div class="container mx-auto p-6 bg-white shadow-lg rounded-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">
                @if(isset($catalogType) && $catalogType == 'hirepurchase')
                    Manage Hire Purchase Categories
                @elseif(isset($catalogType) && $catalogType == 'microbiz')
                    Manage MicroBiz Categories
                @else
                    Manage Categories
                @endif
            </h2>
            <div class="flex space-x-4">
                <a href="/microbiz/categories" class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
                    MicroBiz Catalog
                </a>
                <a href="/hirepurchase/categories" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                    Hire Purchase Catalog
                </a>
            </div>
        </div>

        <!-- Category Form -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-4">Add New Category</h3>
            <form action="/categories" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-gray-700 font-medium">Category Name</label>
                    <input type="text" name="name" placeholder="Enter category name" required
                           class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium">Parent Category (Optional)</label>
                    <select name="parent_id"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400">
                        <option value="">No Parent (Main Category)</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium">Catalog Type</label>
                    <select name="catalog_type" required
                            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400">
                        @if(isset($catalogType) && $catalogType == 'hirepurchase')
                            <option value="hirepurchase" selected>Hire Purchase</option>
                            <option value="microbiz">MicroBiz</option>
                        @elseif(isset($catalogType) && $catalogType == 'microbiz')
                            <option value="microbiz" selected>MicroBiz</option>
                            <option value="hirepurchase">Hire Purchase</option>
                        @else
                            <option value="microbiz">MicroBiz</option>
                            <option value="hirepurchase">Hire Purchase</option>
                        @endif
                    </select>
                </div>

                <button type="submit"
                        class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                    + Add Category
                </button>
            </form>
        </div>

        <!-- Existing Categories -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold mb-4">Existing Categories</h3>
            <div class="space-y-3">
                @foreach($categories as $category)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="flex justify-between items-center bg-gray-100 p-3 cursor-pointer"
                             onclick="toggleAccordion('{{ $category->id }}')">
                            <div>
                                <span class="font-medium text-gray-700">{{ $category->name }}</span>
                                <span class="ml-2 text-xs px-2 py-1 rounded-full 
                                    {{ $category->catalog_type == 'hirepurchase' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $category->catalog_type == 'hirepurchase' ? 'Hire Purchase' : 'MicroBiz' }}
                                </span>
                            </div>
                            <div class="flex items-center">
                                <form action="/categories/delete/{{ $category->id }}" method="POST" class="mr-4">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Are you sure you want to delete this category?')"
                                            class="bg-red-600 text-white px-4 py-1 rounded-lg hover:bg-red-700 transition">
                                        Delete
                                    </button>
                                </form>
                                <span class="text-gray-500">▼</span>
                            </div>
                        </div>

                        <div id="accordion-{{ $category->id }}" class="hidden bg-gray-50 p-4">
                            @if($category->children->count())
                                <ul class="space-y-2">
                                    @foreach($category->children as $child)
                                        <li class="flex justify-between items-center p-2 bg-gray-200 rounded-lg">
                                            <div>
                                                <span class="text-gray-600">{{ $child->name }}</span>
                                                <span class="ml-2 text-xs px-2 py-1 rounded-full 
                                                    {{ $child->catalog_type == 'hirepurchase' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                                    {{ $child->catalog_type == 'hirepurchase' ? 'Hire Purchase' : 'MicroBiz' }}
                                                </span>
                                            </div>
                                            <a href="/categories/delete/{{ $child->id }}"
                                               class="text-red-600 hover:underline"
                                               onclick="return confirm('Are you sure you want to delete this subcategory?')">
                                                Delete
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        function toggleAccordion(id) {
            const element = document.getElementById(`accordion-${id}`);
            element.classList.toggle('hidden');
        }
    </script>
@endsection
