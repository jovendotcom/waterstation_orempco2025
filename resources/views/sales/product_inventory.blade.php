@extends('layout.sales')

@section('title', 'Product Inventory')

@section('content')
<h1 class="mt-4">Product Inventory</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('sales.transaction') }}">Home</a></li>
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
                            <td>{{ $product->product_name }} {{ $product->size_options }}</td>
                            <td>
                                @php
                                    $items = is_array($product->items_needed) ? $product->items_needed : json_decode($product->items_needed, true);
                                    $materialQuantities = is_array($product->material_quantities) ? $product->material_quantities : json_decode($product->material_quantities, true);
                                @endphp
                                @if(!empty($items))
                                    <ul class="list-unstyled mb-0">
                                        @foreach($items as $stockId => $itemName)
                                            <li>
                                                {{ $itemName }} - {{ $materialQuantities[$stockId] ?? 0 }} {{ $stocks->find($stockId)->unit_of_measurement ?? '' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span>No Items Needed</span>
                                @endif
                            </td>
                            <td>{{ $product->quantity ?? 'N/A' }}</td>
                            <td>{{ number_format($product->price, 2) }}</td>
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
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog" style="max-width: 900px;">
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
                                <label for="subcategory_id" class="form-label">Subcategory <span class="text-danger">*</span></label>
                                <select class="form-select" id="subcategory_id" name="subcategory_id" required>
                                    <option value="">Select Subcategory</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}">{{ $subcategory->sub_name }}</option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Product Name Field -->
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name') }}" required>
                                @error('product_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Price Field -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" required>
                                @error('price')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Quantity Field -->
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity (Optional)</label>
                                <input type="number" class="form-control" id="quantity" name="quantity">
                                <small class="text-muted">Leave this field empty if the product does not have a fixed quantity.</small>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Size Options Dropdown -->
                            <div class="mb-3">
                                <label for="size_options" class="form-label">Size Options <span class="text-danger">*</span></label>
                                <select class="form-select @error('size_options') is-invalid @enderror" id="size_options" name="size_options" required>
                                    <option value="">Select Size</option>
                                    @foreach($sizeOptions as $size)
                                        <option value="{{ $size }}">{{ $size }}</option>
                                    @endforeach
                                </select>
                                @error('size_options')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Product Image Field -->
                            <div class="mb-3">
                                <label for="product_image" class="form-label">Product Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('product_image') is-invalid @enderror" id="product_image" name="product_image" accept="image/*" required onchange="previewImage(event)">
                                @error('product_image')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Image Preview -->
                            <div class="mb-3 text-center">
                                <img id="imagePreview" src="" class="img-fluid rounded" style="max-height: 150px; display: none;">
                            </div>
                        </div>
                    </div>

                    <!-- Items Needed Field (Grouped by Category) -->
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Select Material(s) Needed for this product:</label>
                            @foreach($categories as $category)
                                <div class="mb-3">
                                    <h6 class="fw-bold">{{ $category->name }}</h6>
                                    <div class="border p-2 rounded" style="max-height: 150px; overflow-y: auto;">
                                        @foreach($stocks->where('category_id', $category->id)->sortBy('item_name') as $stock)
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <!-- Material Checkbox and Label -->
                                                <div class="form-check">
                                                    <input class="form-check-input material-checkbox" type="checkbox" name="items_needed[{{ $stock->id }}]" value="{{ $stock->item_name }}" data-unit="{{ $stock->unit_of_measurement }}" data-stock-id="{{ $stock->id }}">
                                                    <label class="form-check-label" for="stock_{{ $stock->id }}">
                                                        <strong class="text-black">{{ $stock->item_name }}</strong> <span class="text-success">(Available: {{ $stock->quantity }} {{ $stock->unit_of_measurement }})</span>
                                                    </label>
                                                </div>
                                                <!-- Quantity Input (Hidden by Default) -->
                                                <div class="input-group quantity-input" style="width: 180px; display: none;">
                                                    <input type="number" class="form-control" name="material_quantities[{{ $stock->id }}]" placeholder="Quantity" min="1">
                                                    <span class="input-group-text">{{ $stock->unit_of_measurement }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
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

    document.addEventListener("DOMContentLoaded", function () {
        // Handle material checkbox click
        document.querySelectorAll('.material-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const quantityInput = checkbox.closest('.d-flex').querySelector('.quantity-input');
                if (checkbox.checked) {
                    quantityInput.style.display = 'flex'; // Show quantity input
                } else {
                    quantityInput.style.display = 'none'; // Hide quantity input
                }
            });
        });
    });
</script>
@endsection