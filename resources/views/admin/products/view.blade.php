@extends('layouts.app')

@section('title', 'Manage Products')

@section('content')
    <div class="container mx-auto p-6 bg-white shadow-lg rounded-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Product Management</h2>
            <button onclick="openProductModal()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                + Add Product
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex space-x-4">
            <input type="text" id="searchBox" placeholder="Search by name..." class="p-2 border rounded w-1/4">
            <select id="categoryFilter" class="p-2 border rounded">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <button onclick="reloadTable()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Apply Filters
            </button>
        </div>

        <!-- Product Table -->
        <table id="productTable" class="display w-full">
            <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Base Price</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
            <h2 id="modalTitle" class="text-xl font-semibold mb-4">Add Product</h2>
            <form id="productForm">
                @csrf
                <input type="hidden" id="productId">
                <div class="mb-4">
                    <label>Name:</label>
                    <input type="text" id="productName" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label>Category:</label>
                    <select id="productCategory" class="w-full p-2 border rounded">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label>Base Price:</label>
                    <input type="number" id="productPrice" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label>Image:</label>
                    <input type="file" id="productImage" class="w-full p-2 border rounded">
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeProductModal()" class="mr-2 px-4 py-2 border rounded">Cancel
                    </button>
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        $(document).ready(function () {
            // Initialize Datatable
            $('#productTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/products/list',
                    data: function (d) {
                        d.search = $('#searchBox').val();
                        d.category = $('#categoryFilter').val();
                    }
                },
                columns: [
                    {data: 'id'},
                    {
                        data: 'image', render: function (data) {
                            return `<img src="${data}" class="w-10 h-10 rounded-full">`;
                        }
                    },
                    {data: 'name'},
                    {data: 'category.name'},
                    {data: 'base_price'},
                    {data: 'actions', orderable: false, searchable: false}
                ]
            });
        });

        function reloadTable() {
            $('#productTable').DataTable().ajax.reload();
        }

        function openProductModal(id = null) {
            if (id) {
                $('#modalTitle').text('Edit Product');
                $.get(`/admin/products/${id}`, function (data) {
                    $('#productId').val(data.id);
                    $('#productName').val(data.name);
                    $('#productCategory').val(data.category_id);
                    $('#productPrice').val(data.base_price);
                });
            } else {
                $('#modalTitle').text('Add Product');
                $('#productForm')[0].reset();
                $('#productId').val('');
            }
            $('#productModal').removeClass('hidden');
        }

        function closeProductModal() {
            $('#productModal').addClass('hidden');
        }

        $('#productForm').submit(function (e) {
            e.preventDefault();
            let id = $('#productId').val();
            let formData = new FormData(this);

            $.ajax({
                url: id ? `/admin/products/update/${id}` : '/admin/products/store',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function () {
                    closeProductModal();
                    reloadTable();
                }
            });
        });

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                $.post(`/admin/products/delete/${id}`, {_token: "{{ csrf_token() }}"}, function () {
                    reloadTable();
                });
            }
        }
    </script>
@endsection

@push('scripts')
    <script>
        $('#productForm').submit(function (e) {
            e.preventDefault();
            let id = $('#productId').val();
            let formData = new FormData(this);

            $.ajax({
                url: id ? `/products/update/${id}` : '/products/store',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function () {
                    closeProductModal();
                    reloadTable();
                },
                error: function (xhr) {
                    alert('Something went wrong: ' + xhr.responseText);
                }
            });
        });
    </script>

@endpush
