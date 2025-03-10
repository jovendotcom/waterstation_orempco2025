@extends('layout.admin')

@section('title', 'Product Inventory')

@section('content')
<h1 class="mt-4">Product Inventory</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Product Inventory</li>
    </ol>

    <!-- Success and Error Messages -->
    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-checck-circle me-2 fa-lg"></i>
            <div>
                {{ Session::get('success') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(Session::has('fail'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
            <div>
                {{ Session::get('fail') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Add button next to the search -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <!-- You can place a search bar here if needed -->
        </div>
        <div>
            <!-- Add New User Button -->
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fa-solid fa-faucet"></i> Add New Product
            </button>            
        </div>
    </div>

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Product List
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Item(s) Needed</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if($product->product_image)
                                    <img src="{{ asset('storage/' . $product->product_image) }}" alt="Product Image" style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                    <span>No Image</span>
                                @endif
                            </td>
                            <td>{{ $product->product_name }}</td>
                            <td>
                                @php
                                    $items = is_array($product->items_needed) ? $product->items_needed : json_decode($product->items_needed, true);
                                @endphp
                                @if(!empty($items))
                                    <ul class="list-unstyled mb-0">
                                        @foreach($items as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span>No Items Needed</span>
                                @endif
                            </td>
                            <td>{{ $product->quantity ?? 'N/A' }}</td>
                            <td>{{ number_format($product->price, 2) }}</td>
                            <td>
                                <!-- Edit Button -->
                                <button class="btn btn-success btn-sm add-stock-btn" title="Edit">
                                    <i class="fas fa-edit"></i> 
                                </button>

                                <!-- Add Stock Button -->
                                <button class="btn btn-primary btn-sm add-stock-btn"  
                                        title="Add Stock"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#addStockModal" 
                                        data-id="{{ $product->id }}" 
                                        data-name="{{ $product->product_name }}"
                                        {{ is_null($product->quantity) ? 'disabled' : '' }}>
                                    <i class="fas fa-plus"></i> 
                                </button>

                                <!-- Update Price Button -->
                                <button class="btn btn-warning btn-sm update-price-btn"  
                                        title="Update Price"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#updatePriceModal" 
                                        data-id="{{ $product->id }}" 
                                        data-name="{{ $product->product_name }}"
                                        data-price="{{ $product->price }}">
                                    <i class="fas fa-peso-sign"></i> 
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No products found</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

<!-- Add New Product Modal -->
<!-- Add New Product Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <!-- Use a custom width for the modal -->
    <div class="modal-dialog" style="max-width: 900px;"> <!-- Adjust max-width as needed -->
        <div class="modal-content">
            <form action="{{ route('products.storeProductAdmin') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Use Bootstrap grid to create two columns -->
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Subcategory Dropdown -->
                            <div class="mb-3">
                                <label for="subcategory_id" class="form-label">Subcategory</label>
                                <select class="form-select" id="subcategory_id" name="subcategory_id" required>
                                    <option value="">Select Subcategory</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}">{{ $subcategory->sub_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Product Name Field -->
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name') }}" required>
                                @error('product_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Price Field -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>

                            <!-- Quantity Field -->
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Size Options Dropdown -->
                            <div class="mb-3">
                                <label for="size_options" class="form-label">Size Options</label>
                                <select class="form-select" id="size_options" name="size_options">
                                    <option value="">Select Size</option>
                                    @foreach($sizeOptions as $size)
                                        <option value="{{ $size }}">{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Unit of Measurement Dropdown -->
                            <div class="mb-3">
                                <label for="unit_of_measurement" class="form-label">Unit of Measurement</label>
                                <select class="form-select" id="unit_of_measurement" name="unit_of_measurement">
                                    <option value="">Select Unit</option>
                                    @foreach($unitsOfMeasurement as $unit)
                                        <option value="{{ $unit }}">{{ $unit }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Product Image Field -->
                            <div class="mb-3">
                                <label for="product_image" class="form-label">Product Image</label>
                                <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*" required onchange="previewImage(event)">
                            </div>

                            <!-- Image Preview -->
                            <div class="mb-3 text-center">
                                <img id="imagePreview" src="" class="img-fluid rounded" style="max-height: 150px; display: none;">
                            </div>
                        </div>
                    </div>

                    <!-- Items Needed Field (Full Width) -->
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Select Material(s) Needed for this product:</label>
                                <div class="border p-2 rounded" style="max-height: 150px; overflow-y: auto;">
                                    @foreach($stocks->sortBy('item_name') as $stock)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="items_needed[{{ $stock->id }}]" value="{{ $stock->item_name }}">
                                            <label class="form-check-label" for="stock_{{ $stock->id }}">
                                                <strong class="text-black">{{ $stock->item_name }}</strong> <span class="text-success">(Available: {{ $stock->quantity }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('products.addStockAdmin') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" id="product_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Product Name:</strong> <span id="stock_product_name"></span></p>
                    <div class="mb-3">
                        <label for="add_quantity" class="form-label">Quantity to Add</label>
                        <input type="number" class="form-control" name="add_quantity" id="add_quantity" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Price Modal -->
<div class="modal fade" id="updatePriceModal" tabindex="-1" aria-labelledby="updatePriceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('products.updatePrice') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" id="update_product_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="updatePriceModalLabel">Update Price</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Product Name:</strong> <span id="update_product_name"></span></p>
                    <div class="mb-3">
                        <label for="new_price" class="form-label">New Price</label>
                        <input type="number" class="form-control" name="new_price" id="new_price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Price</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        let reader = new FileReader();
        reader.onload = function() {
            let output = document.getElementById('imagePreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Open modal if validation errors exist
    @if ($errors->any())
        const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        addUserModal.show();
    @endif

    document.addEventListener("DOMContentLoaded", function () {
        let addStockModal = document.getElementById('addStockModal');
        addStockModal.addEventListener('show.bs.modal', function (event) {
            let button = event.relatedTarget;
            let productId = button.getAttribute('data-id');
            let productName = button.getAttribute('data-name');

            document.getElementById('product_id').value = productId;
            document.getElementById('stock_product_name').textContent = productName;
        });
        // Update Price Modal
        document.getElementById('updatePriceModal').addEventListener('show.bs.modal', function (event) {
            let button = event.relatedTarget;
            document.getElementById('update_product_id').value = button.getAttribute('data-id');
            document.getElementById('update_product_name').textContent = button.getAttribute('data-name');
            document.getElementById('new_price').value = button.getAttribute('data-price');
        });
    });
</script>
@endsection