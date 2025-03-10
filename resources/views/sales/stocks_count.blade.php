@extends('layout.sales')

@section('title', 'Stocks Count')

@section('content')
<h1 class="mt-4">Stocks Count</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('sales.transaction') }}">Home</a></li>
        <li class="breadcrumb-item active">Stocks Count</li>
    </ol>

    <!-- Success and Error Messages -->
    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fa-lg"></i>
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
        <div class="d-flex gap-2">
            <!-- Export Dropdown -->
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-export me-1"></i> Export Stocks Count
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href=" {{ route('stocks.export', ['format' => 'excel']) }} "><i class="fas fa-file-excel me-1"></i> Export as Excel</a></li>
                    <li><a class="dropdown-item" href=" {{ route('stocks.export', ['format' => 'pdf']) }} "><i class="fas fa-file-pdf me-1"></i> Export as PDF</a></li>
                </ul>
            </div>

            <!-- Add New Stock Button -->
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addStockModal">
                <i class="fa-solid fa-faucet"></i> Add New Stocks
            </button>            
        </div>
    </div>


    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Stocks Count List
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Material Name</th>
                        <th>Qty</th>
                        <th>Cost</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody">
                    @foreach ($stocks as $stock)
                        <tr>
                            <td>{{ $stock->category->name ?? 'N/A' }}</td>
                            <td>{{ $stock->item_name }}</td>
                            <td>{{ $stock->quantity }} {{ $stock->unit_of_measurement }}</td>
                            <td>₱{{ number_format($stock->price, 2) }}</td>
                            <td>
                                @if($stock->quantity > 0)
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateStockModal" data-item-name="{{ $stock->item_name }}" data-quantity="{{ $stock->quantity }}" data-price="{{ $stock->price }}">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">Add New Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('stocks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Category Dropdown -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Item Name -->
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Material Name</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required min="1">
                    </div>

                    <div class="mb-3">
                        <label for="unit_of_measurement" class="form-label">Unit of Measurement</label>
                        <select class="form-select" id="unit_of_measurement" name="unit_of_measurement" required>
                            <option value="">Select a Unit</option>
                            @foreach($unitsOfMeasurement as $key => $unit)
                                <option value="{{ $key }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                        <label for="price" class="form-label">Price of the Material</label>
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div class="modal fade" id="updateStockModal" tabindex="-1" aria-labelledby="updateStockModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStockModalLabel">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('stocks.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="update_item_name" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="update_item_name" name="item_name" required readonly>
                    </div>
                    <div class="mb-3">
                        <label for="update_quantity" class="form-label">Quantity to Add</label>
                        <input type="number" class="form-control" id="update_quantity" name="quantity" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price of the Material</label>
                        <input type="number" class="form-control" id="update_price" name="price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>  
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get the update stock modal
        const updateStockModal = document.getElementById('updateStockModal');

        updateStockModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const itemName = button.getAttribute('data-item-name');
            const quantity = button.getAttribute('data-quantity');
            const price = button.getAttribute('data-price');

            const itemNameInput = updateStockModal.querySelector('#update_item_name');
            const quantityInput = updateStockModal.querySelector('#update_quantity');
            const priceInput = updateStockModal.querySelector('#update_price');

            itemNameInput.value = itemName;
            quantityInput.setAttribute('min', 1); // Ensuring a valid minimum
            quantityInput.value = ''; // Clear previous input
            priceInput.value = ''; // Clear previous input

            // Focus on the quantity input when the modal opens
            setTimeout(() => {
                quantityInput.focus();
            }, 500); // Delay to ensure modal animation completes
        });

        // Initialize MutationObserver
        const stockTableBody = document.getElementById('stockTableBody');

        const observer = new MutationObserver((mutationsList, observer) => {
            mutationsList.forEach(mutation => {
                if (mutation.type === 'childList') {
                    console.log('Table content changed. New stock row added or updated.');
                    // You can add additional code to handle updates if needed
                }
            });
        });

        // Observe the table body for changes in child elements (rows added/removed)
        observer.observe(stockTableBody, {
            childList: true, // Watch for added/removed nodes
            subtree: true     // Watch the entire subtree (all child nodes)
        });
    });
</script>


@endsection
