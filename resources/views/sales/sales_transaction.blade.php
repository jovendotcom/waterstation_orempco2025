@extends('layout.sales')

@section('title', 'Sales Transaction')

@section('content')
<h1 class="mt-4">Sales Transaction</h1>

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

<div class="row" style="height: calc(90vh - 120px);">
    <!-- Left Container (70%) -->
    <div class="col-lg-9 d-flex">
        <div class="card mb-4 flex-fill">
            <!-- Fixed Header -->
            <div class="card-header" style="position: sticky; top: 0; z-index: 1; background: white;">
                <i class="fas fa-table me-1"></i>
                Product List
            </div>
            <!-- Scrollable Body -->
            <div class="card-body" style="overflow-y: auto; height: calc(90vh - 160px);">
                <!-- Product Grid -->
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    @foreach($products as $product)
                    <div class="col">
                        <div class="card h-100">
                            <!-- Display Product Image -->
                            <img src="{{ $product->product_image ? asset('storage/' . $product->product_image) : asset('images/placeholder.png') }}" 
                                class="card-img-top" 
                                alt="{{ $product->product_name }}" 
                                style="max-height: 150px; width: auto; margin: 0 auto; display: block;">
                            <div class="card-body">
                                <h5 class="card-title">{{ $product->product_name }}</h5>
                                <p class="card-text">Price: &#8369;{{ number_format($product->price, 2) }}</p>
                                <form action="#" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">Buy</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right Container (30%) -->
<div class="col-lg-3 d-flex">
    <div class="card mb-4 flex-fill" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3); position: sticky; top: 100px; height: calc(100vh - 160px); overflow-y: auto;">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Summary
        </div>
        <div class="card-body d-flex flex-column justify-content-between">
            <!-- PO Number and Date-Time -->
            <div class="mb-3">
                <p class="mb-1"><strong>PO Number:</strong> <span id="po-number" style="font-weight: bold; color: red;">123456</span></p>
                <p class="mb-1"><strong>Date & Time:</strong> <span id="date-time" style="font-weight: bold; color: red;"></span></p>
            </div>
            
            <!-- Customer Dropdown -->
            <div class="mb-3">
                <label for="customer" class="form-label"><strong>Select Customer:</strong></label>
                <select id="customer" name="customer" class="form-select">
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Item Details Table -->
            <div class="table-responsive mb-3" style="height: 200px; overflow-y: auto;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="cart-items">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
            
            <!-- Total Amount -->
            <div class="mb-3">
                <hr>
                <p class="mb-2"><strong>Total Items:</strong> <span id="total-items">0</span></p>
                <p class="mb-2"><strong>Total Amount:</strong> <span style="font-size: 1.2em; font-weight: bold; color: red;">&#8369;<span id="total-amount">0.00</span></span></p>
            </div>
            
            <!-- Payment Method -->
            <div class="mb-3">
                <strong>Payment Method:</strong>
                <div class="d-flex align-items-center">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="payment-method" id="cash" value="cash" checked>
                        <label class="form-check-label" for="cash">Cash</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment-method" id="credit" value="credit">
                        <label class="form-check-label" for="credit">Credit</label>
                    </div>
                </div>
            </div>

            <!-- Cash Payment Fields -->
            <div id="cash-fields" class="mb-3">
                <div class="row">
                    <div class="col">
                        <label for="amount-tendered" class="form-label"><strong>Amount Tendered:</strong></label>
                        <input type="number" id="amount-tendered" class="form-control" min="0" step="0.01" placeholder="Enter amount">
                    </div>
                    <div class="col">
                        <label for="change" class="form-label"><strong>Change:</strong></label>
                        <input type="text" id="change" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <!-- Credit Payment Fields -->
            <div id="credit-fields" class="mb-3" style="display: none;">
                <label for="charge-to" class="form-label"><strong>Charge to:</strong></label>
                <select id="charge-to" class="form-select">
                    <option value="">-- Select Charge --</option>
                    <option value="Account1">Salary Deduction</option>
                    <option value="Account2">Charge to ORMECO</option>
                    <option value="Account3">Charge to OREMPCO</option>
                    <option value="Account4">Charge to KKOPI. Tea</option>
                    <option value="Account5">Charge to La Pasta</option>
                    <option value="Account6">Charge to Canteen</option>
                    <option value="Account7">Charge to Power One</option>
                    <option value="Account8">Charge to Others</option>
                </select>
            </div>

            
            <!-- Proceed Button -->
            <div class="mt-3">
                <form action="#" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">Proceed</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>

    // Initialize Select2
    $(document).ready(function() {
        // Initialize Select2 on select elements
        $('#customer').select2({
            placeholder: "-- Select Customer --",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    });

    // Get current date and time
    function updateDateTime() {
        let now = new Date();

        let options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: true 
        };

        document.getElementById("date-time").textContent = now.toLocaleString('en-US', options);
    }

    // Update time every second
    setInterval(updateDateTime, 1000);
    
    // Initialize on load
    updateDateTime();

    // This is for the payment method toggle and change calculation
    $(document).ready(function () {
        // Payment method toggle
        $('input[name="payment-method"]').change(function () {
            if ($(this).val() === 'cash') {
                $('#cash-fields').show();
                $('#credit-fields').hide();
            } else {
                $('#cash-fields').hide();
                $('#credit-fields').show();
            }
        });

        // Calculate Change
        $('#amount-tendered').on('input', function () {
            let tendered = parseFloat($(this).val()) || 0;
            let totalAmount = parseFloat($('#total-amount').text()) || 0;
            let change = tendered - totalAmount;
            $('#change').val(change >= 0 ? change.toFixed(2) : '0.00');
        });
    });
</script>

@endsection
