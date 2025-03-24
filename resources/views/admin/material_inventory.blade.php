@extends('layout.admin')

@section('title', 'Material Inventory')

@section('content')
<h1 class="mt-4">Material Inventory</h1>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Material Inventory</li>
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
                <i class="fas fa-file-export me-1"></i> Export Material Inventory
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('materials_inventory.export.excel') }}"><i class="fas fa-file-excel me-1"></i> Export as Excel</a></li>
                <li><a class="dropdown-item" href="{{ route('materials_inventory.export.pdf') }}"><i class="fas fa-file-pdf me-1"></i> Export as PDF</a></li>
            </ul>
        </div>

        <!-- Add Material Button -->
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addStockModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Material
        </button>
    </div>
</div>

<!-- Inventory Table -->
<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Material Inventory
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Material Name</th>
                    <th>Total Stocks</th>
                    <th>Price Per Unit</th>
                    <th>Low Stock Limit</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materials as $material)
                    <tr>
                        <td>{{ $material->category->name ?? 'N/A' }}</td>
                        <td>{{ $material->material_name }}</td>
                        <td>{{ $material->total_stocks }} {{ $material->unit }}</td>
                        <td>₱{{ number_format($material->cost_per_unit, 2) }}</td>
                        <td>{{ $material->low_stock_limit ?? 'N/A' }} {{ $material->unit }}</td>
                        <td>
                            @if($material->total_stocks == 0)
                                <span class="badge bg-danger text-white">Out of Stock</span>
                            @elseif($material->low_stock_limit && $material->total_stocks <= $material->low_stock_limit)
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @else
                                <span class="badge bg-success text-white">In Stock</span>
                            @endif
                        </td>
                        <td>
                            <!-- Action Buttons -->
                            <a href="{{ route('materials_inventory.edit', $material->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('materials_inventory.destroy', $material->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this material?')">
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

<!-- Add New Material Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('materials_inventory.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addStockModalLabel"><i class="fa-solid fa-plus me-2"></i>Add New Material</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Note for Unit Conversion -->
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> If the inputted stocks are in liters (L) or kilograms (kg), please convert them into milliliters (ml) or grams (g) respectively. For example:
                        <ul class="mt-2">
                            <li>1 Liter (L) = 1000 Milliliters (ml)</li>
                            <li>1 Kilogram (kg) = 1000 Grams (g)</li>
                        </ul>
                    </div>

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

                    <!-- Rest of the form fields -->
                    <div class="row g-3">
                        <!-- Category -->
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <input type="text" class="form-control @error('material_name') is-invalid @enderror" id="material_name" name="material_name" value="{{ old('material_name') }}" placeholder="Enter material name" required>
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
                                    <input type="number" class="form-control @error('total_stocks') is-invalid @enderror" id="total_stocks" name="total_stocks" value="{{ old('total_stocks') }}" placeholder="e.g., 5000" required min="0">
                                    @error('total_stocks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Unit of Measurement -->
                                <div class="col-md-6">
                                    <label for="unit_of_measurement" class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                                    <select class="form-select @error('unit_of_measurement') is-invalid @enderror" id="unit_of_measurement" name="unit_of_measurement" required>
                                        <option value="" disabled {{ old('unit_of_measurement') ? '' : 'selected' }}>Select a Unit</option>
                                        @foreach($unitsOfMeasurement as $key => $unit)
                                            <option value="{{ $key }}" {{ old('unit_of_measurement') == $key ? 'selected' : '' }}>
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
                            <input type="number" step="0.01" class="form-control @error('cost_per_unit') is-invalid @enderror" id="cost_per_unit" name="cost_per_unit" value="{{ old('cost_per_unit') }}" placeholder="e.g., 50.00" required>
                            @error('cost_per_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Low Stock Limit -->
                        <div class="col-md-6">
                            <label for="low_stock_limit" class="form-label fw-semibold">Low Stock Limit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('low_stock_limit') is-invalid @enderror" id="low_stock_limit" name="low_stock_limit" value="{{ old('low_stock_limit') }}" placeholder="e.g., 100" min="0" required>
                            @error('low_stock_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Material</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Reminder Modal -->
<div class="modal fade" id="stockReminderModal" tabindex="-1" aria-labelledby="stockReminderModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="stockReminderModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Stock Reminder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold">The following materials require your attention:</p>
                <ul class="list-group">
                    @foreach($materials as $material)
                        @if($material->total_stocks == 0 || ($material->low_stock_limit && $material->total_stocks <= $material->low_stock_limit))
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold">{{ $material->material_name }}</span> ({{ $material->category->name ?? 'N/A' }})
                                </div>
                                <div>
                                    @if($material->total_stocks == 0)
                                        <span class="badge bg-danger text-white">Out of Stock</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @endif
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@if($materials->contains(function($material) {
    return $material->total_stocks == 0 || ($material->low_stock_limit && $material->total_stocks <= $material->low_stock_limit);
}))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var stockReminderModal = new bootstrap.Modal(document.getElementById('stockReminderModal'));
            stockReminderModal.show();
        });
    </script>
@endif

<!-- Auto Open Modal if Validation Fails -->
@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('addStockModal'));
            myModal.show();
        });
    </script>
@endif

@endsection