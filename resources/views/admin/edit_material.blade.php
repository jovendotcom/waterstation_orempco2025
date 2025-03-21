@extends('layout.admin')

@section('title', 'Edit Material')

@section('content')
<h1 class="mt-4">Edit Material</h1>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.materialInventory') }}">Material Inventory</a></li>
    <li class="breadcrumb-item active">Edit Material</li>
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

<!-- Edit Material Form -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-edit me-1"></i>
        Edit Material
    </div>
    <div class="card-body">
        <form action="{{ route('materials_inventory.update', $material->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <!-- Category -->
                <div class="col-md-6">
                    <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                        <option value="" disabled>Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $material->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Material Name -->
                <div class="col-md-6">
                    <label for="material_name" class="form-label fw-semibold">Material Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('material_name') is-invalid @enderror" id="material_name" name="material_name" value="{{ $material->material_name }}" placeholder="Enter material name" required>
                    @error('material_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Total Stocks and Unit of Measurement in One Line -->
                <div class="col-md-6">
                    <div class="row g-3">
                        <!-- Total Stocks -->
                        <div class="col-md-6">
                            <label for="total_stocks" class="form-label fw-semibold">No. of Stocks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('total_stocks') is-invalid @enderror" id="total_stocks" name="total_stocks" value="{{ $material->total_stocks }}" placeholder="e.g., 5000" required min="0">
                            @error('total_stocks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit of Measurement -->
                        <div class="col-md-6">
                            <label for="unit_of_measurement" class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit_of_measurement') is-invalid @enderror" id="unit_of_measurement" name="unit_of_measurement" required>
                                <option value="" disabled>Select a Unit</option>
                                @foreach($unitsOfMeasurement as $key => $unit)
                                    <option value="{{ $key }}" {{ $material->unit == $key ? 'selected' : '' }}>
                                        {{ $unit }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_of_measurement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Cost Per Unit -->
                <div class="col-md-6">
                    <label for="cost_per_unit" class="form-label fw-semibold">Cost Per Unit (₱) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('cost_per_unit') is-invalid @enderror" id="cost_per_unit" name="cost_per_unit" value="{{ $material->cost_per_unit }}" placeholder="e.g., 50.00" required>
                    @error('cost_per_unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Low Stock Limit -->
                <div class="col-md-6">
                    <label for="low_stock_limit" class="form-label fw-semibold">Low Stock Limit</label>
                    <input type="number" class="form-control @error('low_stock_limit') is-invalid @enderror" id="low_stock_limit" name="low_stock_limit" value="{{ $material->low_stock_limit }}" placeholder="e.g., 100" min="0">
                    @error('low_stock_limit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Material</button>
                <a href="{{ route('admin.materialInventory') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection