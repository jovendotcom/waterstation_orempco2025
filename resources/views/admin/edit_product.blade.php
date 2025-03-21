@extends('layout.admin')

@section('title', 'Edit Product')

@section('content')
<h1 class="mt-4">Edit Product</h1>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.productInventoryAdmin') }}">Product Inventory</a></li>
    <li class="breadcrumb-item active">Edit Product</li>
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

<!-- Edit Product Form -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-edit me-1"></i>
        Edit Product
    </div>
    <div class="card-body">
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <!-- Subcategory -->
                <div class="col-md-6">
                    <label for="subcategory_id" class="form-label fw-semibold">Subcategory <span class="text-danger">*</span></label>
                    <select class="form-select @error('subcategory_id') is-invalid @enderror" id="subcategory_id" name="subcategory_id" required>
                        <option value="" disabled>Select Subcategory</option>
                        @foreach($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ $product->subcategory_id == $subcategory->id ? 'selected' : '' }}>
                                {{ $subcategory->sub_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subcategory_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Product Name -->
                <div class="col-md-6">
                    <label for="product_name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ $product->product_name }}" placeholder="Enter product name" required>
                    @error('product_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Price -->
                <div class="col-md-6">
                    <label for="price" class="form-label fw-semibold">Price (₱) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ $product->price }}" placeholder="e.g., 120.00" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Product Image -->
                <div class="col-md-6">
                    <label for="product_image" class="form-label fw-semibold">Product Image</label>
                    <input type="file" class="form-control @error('product_image') is-invalid @enderror" id="product_image" name="product_image">
                    @if($product->product_image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $product->product_image) }}" alt="{{ $product->product_name }}" class="img-thumbnail" style="width: 100px; height: 100px;">
                        </div>
                    @endif
                    @error('product_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Product Quantity -->
                <div class="col-md-6">
                    <label for="product_quantity" class="form-label fw-semibold">Product Quantity</label>
                    <input type="number" class="form-control @error('product_quantity') is-invalid @enderror" id="product_quantity" name="product_quantity" value="{{ $product->product_quantity }}" placeholder="e.g., 100">
                    <small class="text-muted">Note: The quantity is not fixed. Leave it empty if not applicable.</small>
                    @error('product_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Size Options -->
                <div class="col-md-6">
                    <label for="size_options" class="form-label fw-semibold">Size Options</label>
                    <select class="form-select @error('size_options') is-invalid @enderror" id="size_options" name="size_options">
                        <option value="" disabled>Select Size</option>
                        <option value="Small" {{ $product->size_options == 'Small' ? 'selected' : '' }}>Small</option>
                        <option value="Medium" {{ $product->size_options == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Large" {{ $product->size_options == 'Large' ? 'selected' : '' }}>Large</option>
                    </select>
                    @error('size_options')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Materials Section -->
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Materials Used</label>
                    <div id="materials-container">
                        @foreach($product->materials as $index => $material)
                            <div class="row g-3 mb-3 material-row">
                                <div class="col-md-6">
                                    <select class="form-select material-select" name="materials[{{ $index }}][material_id]">
                                        <option value="" disabled>Select Material</option>
                                        @foreach($materials as $materialOption)
                                            <option value="{{ $materialOption->id }}" {{ $material->id == $materialOption->id ? 'selected' : '' }}>
                                                {{ $materialOption->material_name }} ({{ $materialOption->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" name="materials[{{ $index }}][quantity_used]" value="{{ $material->pivot->quantity_used }}" placeholder="Quantity Used" min="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-material">Remove</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-material">Add Material</button>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Product</button>
                <a href="{{ route('admin.productInventoryAdmin') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const materialsContainer = document.getElementById('materials-container');
        const addMaterialButton = document.getElementById('add-material');
        let materialIndex = {{ $product->materials->count() }};

        // Add Material Row
        addMaterialButton.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'g-3', 'mb-3', 'material-row');
            newRow.innerHTML = `
                <div class="col-md-6">
                    <select class="form-select material-select" name="materials[${materialIndex}][material_id]">
                        <option value="" disabled selected>Select Material</option>
                        @foreach($materials as $material)
                            <option value="{{ $material->id }}">{{ $material->material_name }} ({{ $material->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control" name="materials[${materialIndex}][quantity_used]" placeholder="Quantity Used" min="0">
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