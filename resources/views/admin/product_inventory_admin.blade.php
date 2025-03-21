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
        <i class="fas fa-check-circle me-2 fa-lg"></i>
        <div>{{ Session::get('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(Session::has('fail'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
        <div>{{ Session::get('fail') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Controls: Export & Add Material -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <div class="d-flex gap-2">
        <!-- Export Dropdown -->
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-export me-1"></i> Export Product Inventory
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href=""><i class="fas fa-file-excel me-1"></i> Export as Excel</a></li>
                <li><a class="dropdown-item" href=""><i class="fas fa-file-pdf me-1"></i> Export as PDF</a></li>
            </ul>
        </div>

        <!-- Add Material Button -->
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Product
        </button>
    </div>
</div>

<!-- Inventory Table -->
<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Product Inventory
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Subcategory</th>
                    <th>Materials Used</th>
                    <th>Material Cost</th>
                    <th>Product Price</th>
                    <th>Profit</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->product_name }}</td>
                        <td>{{ $product->subcategory->sub_name ?? 'N/A' }}</td>
                        <td>
                            <ul>
                                @foreach($product->materials as $material)
                                    <li>{{ $material->material_name }} ({{ $material->pivot->quantity_used }} {{ $material->unit }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>₱{{ number_format($product->material_cost, 2) }}</td>
                        <td>₱{{ number_format($product->price, 2) }}</td>
                        <td>₱{{ number_format($product->profit, 2) }}</td>
                        <td>
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addProductModalLabel"><i class="fa-solid fa-plus me-2"></i>Add New Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Display Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <!-- Product Name -->
                        <div class="col-md-6">
                            <label for="product_name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name') }}" placeholder="Enter product name" required>
                            @error('product_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="col-md-6">
                            <label for="price" class="form-label fw-semibold">Price (₱) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" placeholder="e.g., 120.00" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Subcategory -->
                        <div class="col-md-6">
                            <label for="subcategory_id" class="form-label fw-semibold">Subcategory <span class="text-danger">*</span></label>
                            <select class="form-select @error('subcategory_id') is-invalid @enderror" id="subcategory_id" name="subcategory_id" required>
                                <option value="" disabled {{ old('subcategory_id') ? '' : 'selected' }}>Select Subcategory</option>
                                @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                        {{ $subcategory->sub_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subcategory_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Product Image -->
                        <div class="col-md-6">
                            <label for="product_image" class="form-label fw-semibold">Product Image</label>
                            <input type="file" class="form-control @error('product_image') is-invalid @enderror" id="product_image" name="product_image">
                            @error('product_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Materials -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Materials Used <span class="text-danger">*</span></label>
                                <div id="materials-container">
                                    <div class="row g-3 mb-3 material-row">
                                        <div class="col-md-6">
                                            <select class="form-select material-select" name="materials[0][material_id]" required>
                                                <option value="" disabled selected>Select Material</option>
                                                @foreach($materials as $material)
                                                    <option value="{{ $material->id }}">{{ $material->material_name }} ({{ $material->unit }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" class="form-control" name="materials[0][quantity_used]" placeholder="Quantity Used" required min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm remove-material">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-material">Add Material</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Product</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const materialsContainer = document.getElementById('materials-container');
        const addMaterialButton = document.getElementById('add-material');
        let materialIndex = 1;

        // Add Material Row
        addMaterialButton.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'g-3', 'mb-3', 'material-row');
            newRow.innerHTML = `
                <div class="col-md-6">
                    <select class="form-select material-select" name="materials[${materialIndex}][material_id]" required>
                        <option value="" disabled selected>Select Material</option>
                        @foreach($materials as $material)
                            <option value="{{ $material->id }}">{{ $material->material_name }} ({{ $material->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control" name="materials[${materialIndex}][quantity_used]" placeholder="Quantity Used" required min="0">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-material">Remove</button>
                </div>
            `;
            materialsContainer.appendChild(newRow);
            materialIndex++;
        });

        // Remove Material Row
        materialsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-material')) {
                e.target.closest('.material-row').remove();
            }
        });
    });
</script>
@endsection
