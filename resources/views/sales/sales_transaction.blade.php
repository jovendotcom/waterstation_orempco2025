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
                                <p class="card-text" style="color: green;">Stock Available: <span style="font-weight: bold">{{ $product->quantity ?? 'N/A'}}</span></p>
                                <p class="card-text" style="font-weight: bold; color: green;">Price: &#8369;{{ number_format($product->price, 2) }}</p>
                                <form action="#" method="POST">
                                    @csrf
                                    <button type="button" class="buy-btn btn btn-success w-100" 
                                        data-id="{{ $product->id }}" 
                                        data-name="{{ $product->product_name }}" 
                                        data-price="{{ $product->price }}">
                                        Buy
                                    </button>
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
                    <p class="mb-1"><strong>PO Number:</strong> <span id="po-number" style="font-weight: bold; color: red;">{{ $poNumber }}</span></p>
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
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            <!-- Cart items will be displayed here -->
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
                        <option value="Salary Deduction">Salary Deduction</option>
                        <option value="Charge to ORMECO">Charge to ORMECO</option>
                        <option value="Charge to OREMPCO">Charge to OREMPCO</option>
                        <option value="Charge to KKOPI. Tea">Charge to KKOPI. Tea</option>
                        <option value="Charge to La Pasta">Charge to La Pasta</option>
                        <option value="Charge to Canteen">Charge to Canteen</option>
                        <option value="Charge to Power One">Charge to Power One</option>
                        <option value="Charge to Others">Charge to Others</option>
                    </select>
                </div>

                <!-- Proceed Button -->
                <div class="mt-3">
                    <form action="#" method="POST">
                        @csrf
                        <button type="button" id="proceed-btn" class="btn btn-success w-100">Proceed</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Select2 on customer dropdown
$(document).ready(function() {
    $('#customer').select2({
        placeholder: "-- Select Customer --",
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5'
    });
});

// Update current date and time
function updateDateTime() {
    let now = new Date();
    let options = { 
        year: 'numeric', month: 'long', day: 'numeric', 
        hour: '2-digit', minute: '2-digit', second: '2-digit',
        hour12: true 
    };
    document.getElementById("date-time").textContent = now.toLocaleString('en-US', options);
}
setInterval(updateDateTime, 1000);
updateDateTime();

// Payment method toggle and change calculation
$(document).ready(function () {
    $('input[name="payment-method"]').change(function () {
        if ($(this).val() === 'cash') {
            $('#cash-fields').show();
            $('#credit-fields').hide();
        } else {
            $('#cash-fields').hide();
            $('#credit-fields').show();
        }
    });

    $('#amount-tendered').on('input', function () {
        let tendered = parseFloat($(this).val()) || 0;
        let totalAmount = parseFloat($('#total-amount').text()) || 0;
        let change = tendered - totalAmount;
        $('#change').val(change >= 0 ? change.toFixed(2) : '0.00');
    });
});

// Cart handling
let cart = []; // Array of objects { id, name, price, qty, subtotal }

function addToCart(productId, productName, productPrice) {
    console.log("Adding to cart:", { productId, productName, productPrice });

    if (!productId || !productName || isNaN(productPrice) || productPrice <= 0) {
        console.error("Invalid product data", { productId, productName, productPrice });
        alert("Invalid product data! Check the console (F12).");
        return;
    }

    let product = cart.find(item => item.id === productId);

    if (product) {
        product.qty++;
        product.subtotal = product.price * product.qty;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            qty: 1,
            subtotal: productPrice
        });
    }
    updateCartUI();
}

function updateCartUI() {
    let cartItemsContainer = document.getElementById("cart-items");
    cartItemsContainer.innerHTML = ""; // Clear current list

    let totalItems = 0;
    let totalAmount = 0;

    cart.forEach(item => {
        totalItems += item.qty;
        totalAmount += item.subtotal;

        let row = document.createElement("tr");
        row.innerHTML = `
            <td style="max-width: 150px; word-wrap: break-word; white-space: normal;">${item.name}</td>
            <td class="text-center">
                <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                    <button class="btn btn-sm btn-danger" onclick="changeQty(${item.id}, -1)">-</button>
                    <span style="min-width: 30px; text-align: center;">${item.qty}</span>
                    <button class="btn btn-sm btn-success" onclick="changeQty(${item.id}, 1)">+</button>
                </div>
            </td>
            <td style="white-space: nowrap;">&#8369;${item.price.toFixed(2)}</td>
            <td style="white-space: nowrap;">&#8369;${item.subtotal.toFixed(2)}</td>
        `;
        cartItemsContainer.appendChild(row);
    });

    document.getElementById("total-items").textContent = totalItems;
    document.getElementById("total-amount").textContent = totalAmount.toFixed(2);
}

function changeQty(productId, change) {
    let product = cart.find(item => item.id === productId);
    if (product) {
        product.qty += change;
        if (product.qty <= 0) {
            cart = cart.filter(item => item.id !== productId);
        } else {
            product.subtotal = product.price * product.qty;
        }
    }
    updateCartUI();
}

// Buy button event listener
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".buy-btn").forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            console.log('Button dataset:', this.dataset);

            const productId = parseInt(this.dataset.id, 10);
            const productName = this.dataset.name;
            const productPrice = parseFloat(this.dataset.price);

            console.log("Parsed Values:", { productId, productName, productPrice });

            if (Number.isNaN(productId) || productId <= 0 || !productName || Number.isNaN(productPrice) || productPrice <= 0) {
                console.error("Invalid product data", { productId, productName, productPrice });
                alert("Invalid product data! Check the console (F12).");
                return;
            }

            addToCart(productId, productName, productPrice);
        });
    });
});

// Function to refresh the PO number after a successful transaction
function refreshPoNumber() {
    $.ajax({
        url: "{{ route('sales.newPo') }}",
        type: "GET",
        success: function(response) {
            if(response.po_number) {
                $('#po-number').text(response.po_number);
            }
        },
        error: function(xhr) {
            console.error("Failed to refresh PO number:", xhr);
        }
    });
}

// Proceed button click event with validation
$(document).ready(function () {
    $('#proceed-btn').click(function () {
        // Check if any items are in the cart
        if (cart.length === 0) {
            alert("Please select an item first!");
            return;
        }

        // Validate customer selection
        let customerId = $('#customer').val();
        if (!customerId) {
            alert("Please select a customer.");
            return;
        }

        let paymentMethod = $('input[name="payment-method"]:checked').val();
        let chargeTo = $('#charge-to').val();
        let amountTendered = $('#amount-tendered').val();
        let totalAmount = parseFloat($('#total-amount').text()) || 0;

        // Validate based on payment method
        if (paymentMethod === 'cash') {
            if (!amountTendered) {
                alert("Please enter the amount tendered.");
                return;
            }
            if (parseFloat(amountTendered) < totalAmount) {
                alert("Insufficient amount tendered.");
                return;
            }
        } else if (paymentMethod === 'credit') {
            if (!chargeTo) {
                alert("Please select a charge option for credit payment.");
                return;
            }
        }

        $.ajax({
            url: "{{ route('sales.store') }}", 
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                _token: "{{ csrf_token() }}",
                po_number: $('#po-number').text(),
                customer_id: customerId,
                payment_method: paymentMethod,
                charge_to: paymentMethod === 'credit' ? chargeTo : null,
                amount_tendered: paymentMethod === 'cash' ? amountTendered : null,
                change_amount: paymentMethod === 'cash' ? $('#change').val() : null,
                total_items: $('#total-items').text(),
                total_amount: $('#total-amount').text(),
                cart: cart
            }),
            success: function (response) {
                console.log(response);
                alert(response.message);
                resetSummary();
            },
            error: function (xhr) {
                console.error("Transaction failed:", xhr.responseJSON);
                alert("Transaction failed: " + xhr.responseJSON.message);
            }
        });
    });

    function resetSummary() {
        cart = [];
        updateCartUI();
        $('#customer').val('').trigger('change');
        $('#amount-tendered').val('');
        $('#change').val('');
        $('#total-items').text('0');
        $('#total-amount').text('0.00');
        $('input[name="payment-method"][value="cash"]').prop('checked', true);  // Reset to Cash as default
        $('#cash-fields').show();  // Make sure cash fields are visible
        $('#credit-fields').hide();  // Hide credit fields
        refreshPoNumber(); // Refresh to get a new PO number
    }
});
</script>

@endsection
