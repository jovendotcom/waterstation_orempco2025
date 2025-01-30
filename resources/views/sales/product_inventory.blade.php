@extends('layout.sales')

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
                        <th>PRODUCT IMAGE</th>
                        <th>PRODUCT NAME</th>
                        <th>CATEGORY</th>
                        <th>VARIANT</th>
                        <th>UNIT</th>
                        <th>PRICE</th>
                        <th>QUANTITY</th>
                        <th>LOW STOCK LIMIT</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
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
                            <td>{{ $product->category }}</td>
                            <td>{{ $product->variant ?? 'N/A' }}</td>
                            <td>{{ $product->unit }}</td>
                            <td>{{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->low_stock_limit }}</td>
                            <td>
                                @if($product->quantity <= $product->low_stock_limit)
                                    <span class="badge bg-danger">Low Stock</span>
                                @else
                                    <span class="badge bg-success">Available</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm">Edit</button>
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No products found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

<!-- Add Product Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="addProductForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Enter product name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" placeholder="Enter category" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="variant" class="form-label">Variant</label>
                        <input type="text" class="form-control" id="variant" name="variant" placeholder="Enter variant (optional)">
                    </div>
                    <div class="col-md-6">
                        <label for="unit" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="unit" name="unit" placeholder="Enter unit (e.g., per kilo)" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" placeholder="Enter price" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity" min="0" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="low_stock_limit" class="form-label">Low Stock Limit</label>
                        <input type="number" class="form-control" id="low_stock_limit" name="low_stock_limit" placeholder="Enter stock limit" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label for="product_image" class="form-label">Choose File</label>
                        <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12 d-flex justify-content-center">
                        <div class="image-preview" style="width: 300px; height: 300px; border: 1px solid #ccc; display: flex; justify-content: center; align-items: center; position: relative;">
                            <img id="imagePreview" src="" alt="Image Preview" style="max-width: 100%; max-height: 100%; display: none;">
                            <span id="textOverlay" style="position: absolute; color: #888; font-size: 18px;">Image Preview</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');
        const textOverlay = document.getElementById('textOverlay');

        if (file) {
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                textOverlay.style.display = 'none';
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
            textOverlay.style.display = 'block';
        }
    }

    // Reset the modal and image preview when it's closed
    const modal = document.getElementById('addUserModal');
    const form = document.getElementById('addProductForm');
    const imagePreview = document.getElementById('imagePreview');
    const textOverlay = document.getElementById('textOverlay');

    modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        imagePreview.style.display = 'none';
        textOverlay.style.display = 'block';
        imagePreview.src = '';
    });
</script>



@endsection