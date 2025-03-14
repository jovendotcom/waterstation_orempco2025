@extends('layout.sales')

@section('title', 'Sales Transaction')

@section('content')
<h1 class="mt-4">Sales Transaction</h1>
<style>
    @media print {
    body {
        font-family: Arial, sans-serif;
        width: 57mm;
        margin: 0;
        padding: 0;
    }
    div {
        width: 57mm;
        font-size: 10px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    td, th {
        text-align: left;
        font-size: 10px;
    }
}

</style>

<div id="alert-container"></div>

<!-- Success and Error Messages -->
@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2 fa-lg"></i>
        <div>
            {{ Session::get('success') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (Session::has('danger'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
        <div>
            {{ Session::get('danger') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (Session::has('warning'))
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
        <div>
            {{ Session::get('warning') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row" style="height: calc(90vh - 120px);">
    <!-- Left Container (70%) -->
    <div class="col-lg-7 d-flex">
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
                        <div class="card h-100 position-relative" data-id="{{ $product->id }}" style="overflow: hidden; box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2); border-radius: 10px;">
                            <!-- Out of Stock Overlay -->
                            @if($product->quantity === 0)
                            <div id="out-of-stock-{{ $product->id }}" class="out-of-stock-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;
                                        background: rgba(255, 255, 255, 0.7); /* Transparent White */
                                        color: red; font-size: 30px; font-weight: 900; 
                                        display: flex; align-items: center; justify-content: center;
                                        text-transform: uppercase; letter-spacing: 2px;
                                        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);">
                                OUT OF STOCK
                            </div>
                            @endif

                            <!-- Display Product Image -->
                            <img src="{{ $product->product_image ? asset('storage/' . $product->product_image) : asset('images/placeholder.png') }}" 
                                class="card-img-top" 
                                alt="{{ $product->product_name }}" 
                                style="max-height: 150px; width: auto; margin: 0 auto; display: block; border-radius: 10px 10px 0 0;">

                            <div class="card-body">
                                <h6 class="card-title">{{ $product->product_name }} {{ $product->size_options }}</h6>
                                <p class="card-text" style="font-weight: bold; color: {{ $product->quantity === 0 ? 'red' : 'green' }};">
                                    Stock Available: <span>{{ $product->quantity ?? 'N/A' }}</span>
                                </p>
                                <p class="card-text" style="font-weight: bold; color: green;">Price: &#8369;{{ number_format($product->price, 2) }}</p>
                                <form action="#" method="POST">
                                    @csrf
                                    <button type="button" class="buy-btn btn btn-success w-100" 
                                        data-id="{{ $product->id }}" 
                                        data-name="{{ $product->product_name }}" 
                                        data-price="{{ $product->price }}"
                                        data-stock="{{ $product->quantity ?? 'N/A' }}" 
                                        data-items-needed="{{ $product->items_needed }}"
                                        data-material-quantities="{{ $product->material_quantities }}"
                                        data-material-quantity-unit-of-measurement="{{ $product->material_quantity_unit_of_measurement }}"
                                        {{ $product->quantity === 0 ? 'disabled' : '' }}>
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
    <div class="col-lg-5 d-flex">
        <div class="card mb-4 flex-fill" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3); position: sticky; top: 100px; height: calc(100vh - 160px); overflow-y: auto;">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Summary
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <!-- PO Number and Date-Time -->
                <div class="mb-3">
                    <p class="mb-1"><strong>SO Number:</strong> <span id="po-number" style="font-weight: bold; color: red;">{{ $poNumber }}</span></p>
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
                                <th>Material(s) Needed</th>
                                <th>Adds on</th>
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

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmIncreaseModal" tabindex="-1" aria-labelledby="confirmIncreaseLabel" aria-hidden="true" data-bs-backdrop ="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmIncreaseLabel">Confirm Quantity Increase</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Why are you increasing the quantity of <strong><span id="materialNameSpan"></span></strong>?</p>

        <div id="reasonSelection">
          <div class="form-check">
            <input class="form-check-input reason-checkbox" type="checkbox" id="reason1" value="Stock Refill">
            <label class="form-check-label" for="reason1">Broken Material</label>
          </div>
          <div class="form-check">
            <input class="form-check-input reason-checkbox" type="checkbox" id="reason2" value="Incorrect Initial Entry">
            <label class="form-check-label" for="reason2">Incorrect Initial Entry</label>
          </div>
          <div class="form-check">
            <input class="form-check-input reason-checkbox" type="checkbox" id="reason3" value="Additional Requirement">
            <label class="form-check-label" for="reason3">Additional Requirement</label>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmIncreaseBtn" disabled>Yes, Increase</button>
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
let cart = []; // Array of objects { id, name, price, qty, subtotal, stock }

function addToCart(productId, productName, productPrice, itemsNeededJson, stockAvailable, materialQuantitiesJson, materialQuantityUnitsJson) {
    console.log("Adding to cart:", { productId, productName, productPrice, itemsNeededJson, stockAvailable, materialQuantitiesJson, materialQuantityUnitsJson });

    if (!productId || !productName || isNaN(productPrice) || productPrice <= 0) {
        console.error("Invalid product data", { productId, productName, productPrice });
        alert("Invalid product data! Check the console (F12).");
        return;
    }

    // Parse JSON data
    let itemsNeeded = itemsNeededJson ? JSON.parse(itemsNeededJson) : {};
    let materialQuantities = materialQuantitiesJson ? JSON.parse(materialQuantitiesJson) : {};
    let materialQuantityUnits = materialQuantityUnitsJson ? JSON.parse(materialQuantityUnitsJson) : {};

    // Initialize materials with quantities and units from the database
    let materials = {};
    for (let key in itemsNeeded) {
        let materialName = itemsNeeded[key];
        materials[materialName] = {
            quantity: materialQuantities[key] || 1, // Quantity from the database
            unit: materialQuantityUnits[key] || 'unit' // Unit of measurement from the database
        };
    }

    // Check if product already exists in cart
    let product = cart.find(item => item.id === productId);
    let currentQtyInCart = product ? product.qty : 0;
    let newQty = currentQtyInCart + 1;

    if (stockAvailable !== "N/A") {
        stockAvailable = parseInt(stockAvailable, 10);
        if (!isNaN(stockAvailable) && newQty > stockAvailable) {
            alert(`Insufficient stock for ${productName}. Available stock: ${stockAvailable}`);
            return;
        }
    }

    if (product) {
        product.qty = newQty;
        product.subtotal = product.price * product.qty;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            qty: 1,
            subtotal: productPrice,
            materials: materials, // Material quantities and units from the database
            materialAdjustments: Object.keys(materials).reduce((acc, key) => {
                acc[key] = 1; // Initialize with the product quantity
                return acc;
            }, {}), // Adjusted quantities for the cart
            stock: stockAvailable
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

        let materialsListHTML = "";

        if (item.stock === "N/A" && Object.keys(item.materials).length > 0) {
            for (let materialName in item.materials) {
                let materialQty = item.materials[materialName].quantity; // Quantity from the database
                let materialUnit = item.materials[materialName].unit; // Unit of measurement from the database
                let adjustedQty = item.materialAdjustments[materialName] || item.qty; // Adjusted quantity (default to product quantity)

                // Calculate the total material quantity based on product quantity
                let totalMaterialQty = materialQty * adjustedQty;

                materialsListHTML += `
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span>${materialName} - ${totalMaterialQty} ${materialUnit}: </span> <!-- Material Name, Total Quantity, and Unit -->
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span id="item-adjusted-${item.id}-${materialName}" style="min-width: 30px; text-align: center;">${adjustedQty}</span>
                            <button class="btn btn-sm btn-success" onclick="changeMaterialAdjustment(${item.id}, '${materialName}', 1)">+</button>
                        </div>
                    </div>
                `;
            }
        } else {
            materialsListHTML = `<span>None</span>`;
        }

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
            <td>${materialsListHTML}</td>  <!-- Display Materials Needed -->
        `;
        cartItemsContainer.appendChild(row);
    });

    document.getElementById("total-items").textContent = totalItems;
    document.getElementById("total-amount").textContent = totalAmount.toFixed(2);
}

function changeMaterialAdjustment(productId, materialName, change) {
    if (change === 1) {
        // Show the modal for increasing quantity
        showIncreaseModal(productId, materialName);
    } else {
        // Directly decrease the quantity (no modal needed)
        let product = cart.find(item => item.id === productId);
        if (product) {
            if (!product.materialAdjustments) product.materialAdjustments = {};
            if (!product.materialAdjustments[materialName]) product.materialAdjustments[materialName] = 1;

            let newAdjustedQty = product.materialAdjustments[materialName] + change;
            if (newAdjustedQty < 1) newAdjustedQty = 1;

            product.materialAdjustments[materialName] = newAdjustedQty;
            updateCartUI();
        }
    }
}

function showIncreaseModal(productId, materialName) {
    // Set the material name in the modal
    document.getElementById("materialNameSpan").textContent = materialName;

    // Clear any previously selected reasons
    document.querySelectorAll(".reason-checkbox").forEach(checkbox => {
        checkbox.checked = false;
    });

    // Disable the "Yes, Increase" button initially
    document.getElementById("confirmIncreaseBtn").disabled = true;

    // Add event listeners to the checkboxes
    document.querySelectorAll(".reason-checkbox").forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            // Enable the "Yes, Increase" button if at least one reason is selected
            const isAnyReasonSelected = Array.from(document.querySelectorAll(".reason-checkbox")).some(cb => cb.checked);
            document.getElementById("confirmIncreaseBtn").disabled = !isAnyReasonSelected;
        });
    });

    // Handle the "Yes, Increase" button click
    document.getElementById("confirmIncreaseBtn").onclick = function () {
        // Increase the material quantity
        let product = cart.find(item => item.id === productId);
        if (product) {
            if (!product.materialAdjustments) product.materialAdjustments = {};
            if (!product.materialAdjustments[materialName]) product.materialAdjustments[materialName] = 1;

            product.materialAdjustments[materialName] += 1;
            updateCartUI();
        }

        // Close the modal
        bootstrap.Modal.getInstance(document.getElementById("confirmIncreaseModal")).hide();
    };

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById("confirmIncreaseModal"));
    modal.show();
}

function changeQty(productId, change) {
    let product = cart.find(item => item.id === productId);

    if (product) {
        let stockAvailable = document.querySelector(`.buy-btn[data-id="${productId}"]`).dataset.stock;
        stockAvailable = parseInt(stockAvailable, 10); // Convert stock to integer

        if (product.stock !== "N/A" && product.qty + change > stockAvailable) {
            alert(`Not enough stock available! Only ${stockAvailable} item(s) left.`);
            return; // Stop the function if stock is not enough
        }

        // Update product quantity
        product.qty += change;

        if (product.qty <= 0) {
            // Remove product from cart if quantity is 0
            cart = cart.filter(item => item.id !== productId);
        } else {
            // Update subtotal
            product.subtotal = product.price * product.qty;

            // Adjust material adjustments to match the product quantity
            for (let materialName in product.materialAdjustments) {
                product.materialAdjustments[materialName] = product.qty;
            }
        }

        // Update the UI
        updateCartUI();
    }
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
                const itemsNeeded = this.dataset.itemsNeeded; // Get items_needed JSON
                let stockAvailable = this.dataset.stock; // Get stock (can be "N/A")
                const materialQuantities = this.dataset.materialQuantities; // Get material_quantities JSON
                const materialQuantityUnits = this.dataset.materialQuantityUnitOfMeasurement; // Get material_quantity_unit_of_measurement JSON

                console.log("Parsed Values:", { productId, productName, productPrice, itemsNeeded, stockAvailable, materialQuantities, materialQuantityUnits });

                if (Number.isNaN(productId) || productId <= 0 || !productName || Number.isNaN(productPrice) || productPrice <= 0) {
                    console.error("Invalid product data", { productId, productName, productPrice, stockAvailable });
                    alert("Invalid product data! Check the console (F12).");
                    return;
                }

                addToCart(productId, productName, productPrice, itemsNeeded, stockAvailable, materialQuantities, materialQuantityUnits);
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
                if (response.success) {
                    showAlert(response.message, 'success');

                    // Ensure transactionNo and cashierName are properly passed
                    const transactionNo = response.transaction_no ||
                        'N/A'; // Default to 'N/A' if undefined
                    const cashierName =
                        '{{ Auth::guard('sales')->user()->full_name; }}'; // Assuming this is the correct way to get the cashier's name

                    // Trigger invoice generation and printing
                    generateSalesInvoice({
                        cashierName: cashierName,
                        dateTime: new Date().toLocaleString(),
                        poNumber: $('#po-number').text(),
                        cart: cart,
                        totalItems: $('#total-items').text(),
                        totalAmount: $('#total-amount').text(),
                        paymentMethod: paymentMethod,
                        chargeTo: chargeTo,
                        amountTendered: amountTendered,
                        changeAmount: $('#change').val() 
                    });
                    resetSummary();
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function (xhr) {
                console.error("Transaction failed:", xhr.responseJSON);
                alert("Transaction failed: " + xhr.responseJSON.message);
            }
        });
    });

    function resetSummary() {
        // Update product quantities in real-time
        cart.forEach(item => {
            const productId = item.id;
            const quantityPurchased = item.qty;

            // Find the product card and update its quantity
            const productCard = document.querySelector(`.card[data-id="${productId}"]`);
            if (productCard) {
                const stockElement = productCard.querySelector('.card-text span');
                if (stockElement) {
                    const currentQuantity = parseInt(stockElement.textContent, 10);
                    const updatedQuantity = currentQuantity - quantityPurchased;

                    // Update the quantity display
                    stockElement.textContent = updatedQuantity;

                    // Update the stock text color based on the updated quantity
                    const stockText = stockElement.closest('p');
                    if (stockText) {
                        stockText.style.color = updatedQuantity === 0 ? 'red' : 'green';
                    }

                    // Disable the "Buy" button if the product is out of stock
                    const buyButton = productCard.querySelector('.buy-btn');
                    if (buyButton) {
                        if (updatedQuantity === 0) {
                            buyButton.disabled = true;
                            buyButton.textContent = 'Out of Stock';
                        } else {
                            buyButton.disabled = false;
                            buyButton.textContent = 'Buy';
                        }
                    }

                    // Show or hide the "Out of Stock" overlay based on the updated quantity
                    const outOfStockOverlay = productCard.querySelector('.out-of-stock-overlay');
                    if (outOfStockOverlay) {
                        if (updatedQuantity === 0) {
                            outOfStockOverlay.style.display = 'flex'; // Show the overlay
                        } else {
                            outOfStockOverlay.style.display = 'none'; // Hide the overlay
                        }
                    }
                }
            }
        });

        // Reset the cart and other fields
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


    // Function to show alert messages with auto-dismiss and optional page reload
    function showAlert(message, type) {
        console.log("Alert Message:", message, "Type:", type); // Debugging

        const alertId = 'alert-' + Date.now();
        let icon = (type === 'success') ? 'check-circle' :
                   (type === 'danger') ? 'exclamation-triangle' :
                   'info-circle';

        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${icon} me-2"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('#alert-container').html(alertHtml);

        setTimeout(() => $('#' + alertId).fadeOut(), 2000);
    }

    function generateSalesInvoice(data) {
    const {
        cashierName, dateTime, poNumber, cart, totalItems,
        totalAmount, paymentMethod, chargeTo, amountTendered, changeAmount
    } = data;

    let printContent = `
        <div style="font-family: Arial, sans-serif; width: 100%; margin: 0; padding: 0; text-align: left; font-size: 10px;">
            <p style="text-align: center; font-size: 12px;">
                <strong>ORMECO EMPLOYEES MULTI-PURPOSE COOPERATIVE (OREMPCO)</strong><br>
                Sta. Isabel, Calapan City Oriental Mindoro<br>
                CDA Resgistration No.: 9520-04002679<br>
                NVAT-Exempt TIN: 004-175-226-000
            </p>
            <hr>
            <p><strong>Sales Order Number:</strong> ${poNumber}</p>
            <p><strong>Waterstation Staff:</strong> ${cashierName}</p>
            <p><strong>Date/Time:</strong> ${dateTime}</p>
            <hr>
            <table style="width: 100%;">
                <tr>
                    <th style="text-align: left;">Item</th>
                    <th style="text-align: right;">Qty</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Sub-Total</th>
                </tr>
    `;

    cart.forEach(item => {
        printContent += `
                <tr>
                    <td>${item.name}</td>
                    <td style="text-align: right;">${item.qty}</td>
                    <td style="text-align: right;">₱${parseFloat(item.price).toFixed(2)}</td>
                    <td style="text-align: right;">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                </tr>
        `;
    });

    printContent += `
            </table>
            <hr>
            <p><strong>Total Items:</strong> <span style="float: right;">${totalItems}</span></p>
            <p><strong>Total Amount:</strong> <span style="float: right;">₱${totalAmount}</span></p>
            <p><strong>Payment Method:</strong> <span style="float: right;">${paymentMethod}</span></p>
            <p><strong>Status:</strong> <span style="float: right;">${paymentMethod === 'credit' ? 'Not Paid' : 'Paid'}</span></p>
    `;

    if (paymentMethod === 'cash') {
        printContent += `
            <p><strong>Amount Tendered:</strong> <span style="float: right;">₱${parseFloat(amountTendered).toFixed(2)}</span></p>
            <p><strong>Change:</strong> <span style="float: right;">₱${parseFloat(changeAmount).toFixed(2)}</span></p>
        `;
    } else if (paymentMethod === 'credit') {
        printContent += `
            <p><strong>Charge To:</strong> <span style="float: right;">${chargeTo}</span></p>
        `;
    }

    printContent += `
            <hr>
            <p style="text-align: center; font-size: 10px;">
                This is your Sales Invoice
            </p>
            <p style="text-align: center; font-size: 9px;">
                Thank you for shopping with us!
            </p>
        </div>
    `;

    const printWindow = window.open('', '', 'width=400,height=600');
    printWindow.document.write('<html><head><title>Sales Invoice</title><style>');
    printWindow.document.write(`
        @media print {
            body, div {
                font-family: Arial, sans-serif;
                width: 100%;
                margin: 0;
                padding: 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            td, th {
                font-size: 10px;
                padding: 2px 0;
            }
        }
    `);
    printWindow.document.write('</style></head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

});
</script>

@endsection
