@extends('layout.sales')

@section('title', 'Sales')

@section('content')
<h1 class="mt-4">Sales</h1>

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

<div class="row mt-4" style="height: 80vh; overflow: hidden;">
    <!-- Left Column: Product Display -->
    <div class="col-md-8 h-100">
        <div class="card h-100" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-box me-2"></i>Products
                </h5>
            </div>
            <div class="card-body" style="overflow-y: auto; height: calc(100% - 60px);">
                <!-- Product List Grouped by Subcategory -->
                @foreach($categories as $category)
                    <div class="mb-4">
                        <h5 class="mb-3">{{ $category->name }}</h5>
                        @foreach($category->subcategories as $subcategory)
                            <div class="mb-3">
                                <h6 class="mb-2">
                                    <button class="btn btn-link text-decoration-none" data-bs-toggle="collapse" data-bs-target="#subcategory{{ $subcategory->id }}">
                                        {{ $subcategory->sub_name }}
                                    </button>
                                </h6>
                                <div class="collapse show" id="subcategory{{ $subcategory->id }}">
                                    <div class="row row-cols-1 row-cols-md-3 g-4">
                                        @foreach($subcategory->product as $product)
                                            <div class="col">
                                                <div class="card h-100 product-card" data-id="{{ $product->id }}" data-name="{{ $product->product_name }}" data-price="{{ $product->price }}" data-image="{{ asset('storage/' . $product->product_image) }}" data-material-cost="{{ $product->material_cost }}" data-profit="{{ $product->profit }}" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                                                    <img src="{{ asset('storage/' . $product->product_image) }}" class="card-img-top w-100" alt="{{ $product->product_name }}" style="height: 200px; object-fit: contain; cursor: pointer;" onclick="addToCart({{ $product->id }})">
                                                    <div class="card-body">
                                                        <h6 class="card-title">{{ $product->product_name }} {{ $product->size_options }}</h6>
                                                        <p class="card-text">₱{{ number_format($product->price, 2) }}</p>
                                                        <button class="btn btn-primary btn-sm w-100 add-to-cart" data-product-id="{{ $product->id }}">
                                                            <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Summary -->
    <div class="col-md-4 h-100">
        <div class="card h-100" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-receipt me-2"></i>Order Summary
                </h5>
            </div>
            <div class="card-body" style="overflow-y: auto; height: calc(100% - 60px);">
                <!-- Sales Order Number and Real-Time Date & Time -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="mb-1"><strong>SO Number:</strong> <span id="so-number" style="font-weight: bold; color: red;">{{ $soNumber }}</span></p>
                        <span id="realTimeDateTime" class="text-muted">{{ now()->format('M d, Y h:i A') }}</span>
                    </div>
                </div>

                <!-- Customer Dropdown -->
                <div class="mb-3">
                    <label for="customerSelect" class="form-label">Customer</label>
                    <select class="form-select" id="customerSelect">
                        <option selected>Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->full_name }} - {{ $customer->type }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cart Items -->
                <div id="cartItems">
                    <p class="text-muted">No items added yet.</p>
                </div>

                <!-- Total Items and Total Amount -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Items:</span>
                        <span id="totalItems">0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Amount:</span>
                        <span id="totalAmount">₱0.00</span>
                    </div>
                </div>

                                <!-- Payment Method -->
                                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" id="cashPayment" value="cash" checked>
                        <label class="form-check-label" for="cashPayment">Cash</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentMethod" id="creditPayment" value="credit">
                        <label class="form-check-label" for="creditPayment">Credit</label>
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

                <!-- Cash Input and Change Calculation -->
                <div class="mb-3" id="cashInputSection">
                    <label for="cashAmount" class="form-label">Cash Amount</label>
                    <input type="number" class="form-control" id="cashAmount" min="0" step="0.01">
                    <div class="mt-2">
                        <strong>Change:</strong> <span id="changeAmount">₱0.00</span>
                    </div>
                </div>

                <!-- Checkout Button -->
                <button class="btn btn-success w-100 mt-3" id="checkoutButton">
                    <i class="fas fa-cash-register me-1"></i>Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Additional Material Modal -->
<div class="modal fade" id="additionalMaterialModal" tabindex="-1" aria-labelledby="additionalMaterialModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="additionalMaterialModalLabel">
                    <i class="fa-solid fa-plus me-2"></i>Add Additional Material
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Note for Additional Material -->
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Please provide the reason and additional quantity for the material.
                </div>

                <!-- Form for Additional Material -->
                <form id="additionalMaterialForm">
                    <div class="row g-3">
                        <!-- Material Name -->
                        <div class="col-md-6">
                            <label for="materialName" class="form-label fw-semibold">Material Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="materialName" readonly>
                        </div>

                        <!-- Price Per Unit -->
                        <div class="col-md-6">
                            <label for="pricePerUnit" class="form-label fw-semibold">Price Per Unit (₱) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="pricePerUnit" readonly>
                        </div>

                        <!-- Reason -->
                        <div class="col-md-12">
                            <label for="reason" class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reason" placeholder="Enter reason for additional material" required>
                        </div>

                        <!-- Additional Quantity -->
                        <div class="col-md-12">
                            <label for="additionalQuantity" class="form-label fw-semibold">Additional Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="additionalQuantity" min="1" placeholder="Enter additional quantity" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveAdditionalMaterial">
                    <i class="fas fa-save me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Alert Modal -->
<div class="modal fade" id="stockAlertModal" tabindex="-1" aria-labelledby="stockAlertModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="stockAlertModalLabel">Stock Alert</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="stockAlertMessage">
        <!-- Error message from backend will go here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>


<!-- JavaScript for Sales Processing -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = []; // Array to store cart items
    let totalAmount = 0; // Total amount of the order

    // Add to Cart Functionality
    function addToCart(productId) {
        const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
        const productName = productCard.dataset.name;
        const productPrice = parseFloat(productCard.dataset.price);
        const productImage = productCard.dataset.image;
        const materialCost = parseFloat(productCard.dataset.materialCost);
        const profit = parseFloat(productCard.dataset.profit);

        // Check if the product is already in the cart
        const existingItem = cart.find(item => item.id === productId);
        if (existingItem) {
            existingItem.quantity += 1; // Increase quantity if already in cart
        } else {
            cart.push({
                id: productId,
                name: productName,
                price: productPrice,
                image: productImage,
                materialCost: materialCost,
                profit: profit,
                quantity: 1,
                materials: [] // Store materials for this product
            }); // Add new item to cart
        }

        // Fetch materials for the product if not already fetched
        if (cart[cart.length - 1].materials.length === 0) {
            fetchMaterials(productId, cart[cart.length - 1]);
        }

        // Update the cart display
        updateCartDisplay();
    }

    // Fetch Materials for a Product
    function fetchMaterials(productId, cartItem) {
        fetch(`/sales/products/${productId}/materials`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(materials => {
                cartItem.materials = materials;
                updateCartDisplay();
            })
            .catch(error => {
                console.error('Error fetching materials:', error);
            });
    }

    // Update Cart Display
    function updateCartDisplay() {
        const cartItemsContainer = document.getElementById('cartItems');
        const totalAmountElement = document.getElementById('totalAmount');
        const totalItemsElement = document.getElementById('totalItems');
        let cartHTML = '';
        totalAmount = 0;
        let totalItems = 0;

        if (cart.length === 0) {
            cartHTML = '<p class="text-muted">No items added yet.</p>';
        } else {
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                let totalMaterialCost = item.materials.reduce((sum, material) => {
                    let materialCost = material.quantity_used * item.quantity;
                    if (material.additional) {
                        materialCost += material.additional.reduce((sumAdd, add) => sumAdd + (add.quantity * add.pricePerUnit), 0);
                    }
                    return sum + materialCost;
                }, 0);

                // Recalculate profit
                const totalProfit = (item.price * item.quantity) - totalMaterialCost; // Profit = Total Price - Total Material Cost
                totalAmount += itemTotal;
                totalItems += item.quantity;

                cartHTML += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <img src="${item.image}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: cover;" class="me-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">${item.name}</h6>
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary btn-sm quantity-decrease" data-product-id="${item.id}">-</button>
                                            <input type="number" class="form-control quantity-input" value="${item.quantity}" min="1" data-product-id="${item.id}">
                                            <button class="btn btn-outline-secondary btn-sm quantity-increase" data-product-id="${item.id}">+</button>
                                        </div>
                                    </div>
                                    <small class="text-muted">₱${item.price.toFixed(2)} each</small>
                                    <div class="mt-2">
                                        <small class="text-muted"><strong>Subtotal: ₱${itemTotal.toFixed(2)}</strong></small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Materials Used:</small>
                                <ul class="list-unstyled" id="materials-${item.id}">
                                    ${item.materials.map(material => `
                                        <li>
                                            ${material.material_name} - ${material.quantity_used * item.quantity} ${material.unit}
                                            <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#additionalMaterialModal" data-material-id="${material.id}" data-material-name="${material.material_name}" data-price-per-unit="${material.cost_per_unit}">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                            ${material.additional ? material.additional.map(add => `
                                                <div class="ms-4">
                                                    <small>Added ${add.quantity} ${material.unit} (${add.reason}) - Additional Cost: ₱${(add.quantity * add.pricePerUnit).toFixed(2)}</small>
                                                </div>
                                            `).join('') : ''}
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Material Cost: ₱${totalMaterialCost.toFixed(2)}</small><br>
                                <small class="text-muted">Profit: ₱${totalProfit.toFixed(2)}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        cartItemsContainer.innerHTML = cartHTML;
        totalAmountElement.textContent = `₱${totalAmount.toFixed(2)}`;
        totalItemsElement.textContent = totalItems;

        // Attach event listeners to buttons and inputs
        attachEventListeners();
    }

    // Function to attach event listeners
    function attachEventListeners() {
        // Quantity decrease buttons
        document.querySelectorAll('.quantity-decrease').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const cartItem = cart.find(item => item.id === productId);
                if (cartItem && cartItem.quantity > 1) {
                    cartItem.quantity -= 1;
                    updateCartDisplay();
                }
            });
        });

        // Quantity increase buttons
        document.querySelectorAll('.quantity-increase').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const cartItem = cart.find(item => item.id === productId);
                if (cartItem) {
                    cartItem.quantity += 1;
                    updateCartDisplay();
                }
            });
        });

        // Quantity input changes
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const newQuantity = parseInt(this.value);

                // Update the quantity in the cart
                const cartItem = cart.find(item => item.id === productId);
                if (cartItem) {
                    cartItem.quantity = newQuantity;
                }

                // Update the cart display
                updateCartDisplay();
            });
        });
    }

    // Handle the modal for additional materials
    const additionalMaterialModal = document.getElementById('additionalMaterialModal');
    const materialNameInput = document.getElementById('materialName');
    const pricePerUnitInput = document.getElementById('pricePerUnit');
    const reasonInput = document.getElementById('reason');
    const additionalQuantityInput = document.getElementById('additionalQuantity');
    const saveAdditionalMaterialButton = document.getElementById('saveAdditionalMaterial');

    let currentMaterial = null;
    let currentCartItem = null;

    additionalMaterialModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const materialId = button.getAttribute('data-material-id');
        const materialName = button.getAttribute('data-material-name');
        const pricePerUnit = button.getAttribute('data-price-per-unit');

        // Set the modal content
        materialNameInput.value = materialName;
        pricePerUnitInput.value = pricePerUnit;

        // Find the cart item and material
        currentCartItem = cart.find(item => item.materials.some(material => material.id == materialId));
        currentMaterial = currentCartItem.materials.find(material => material.id == materialId);

        // Reset the form
        reasonInput.value = '';
        additionalQuantityInput.value = 1;
    });

    saveAdditionalMaterialButton.addEventListener('click', function() {
        const reason = reasonInput.value;
        const additionalQuantity = parseInt(additionalQuantityInput.value);
        const pricePerUnit = parseFloat(pricePerUnitInput.value);

        if (!reason || isNaN(additionalQuantity) || additionalQuantity < 1) {
            alert('Please fill in all fields correctly.');
            return;
        }

        // Add the additional material details to the material
        if (!currentMaterial.additional) {
            currentMaterial.additional = [];
        }
        currentMaterial.additional.push({
            reason: reason,
            quantity: additionalQuantity,
            pricePerUnit: pricePerUnit
        });

        // Update the cart display
        updateCartDisplay();

        // Close the modal
        const modal = bootstrap.Modal.getInstance(additionalMaterialModal);
        modal.hide();
    });

    // Add to Cart Button Event Listeners
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });

    // Real-Time Date and Time
    function updateRealTimeDateTime() {
        const now = new Date();
        const options = { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
        const formattedDateTime = now.toLocaleString('en-US', options);
        document.getElementById('realTimeDateTime').textContent = formattedDateTime;
    }
    setInterval(updateRealTimeDateTime, 1000); // Update every second

    // Cash Input and Change Calculation
    document.getElementById('cashAmount').addEventListener('input', function() {
        const cashAmount = parseFloat(this.value);
        const totalAmount = parseFloat(document.getElementById('totalAmount').textContent.replace('₱', ''));
        if (!isNaN(cashAmount)) {
            const change = cashAmount - totalAmount;
            document.getElementById('changeAmount').textContent = `₱${change.toFixed(2)}`;
        } else {
            document.getElementById('changeAmount').textContent = '₱0.00';
        }
    });

    // Toggle Cash Input and Credit Fields Based on Payment Method
    document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const cashInputSection = document.getElementById('cashInputSection');
            const creditFields = document.getElementById('credit-fields');

            if (this.value === 'cash') {
                cashInputSection.style.display = 'block'; // Show cash input
                creditFields.style.display = 'none'; // Hide credit fields
            } else if (this.value === 'credit') {
                cashInputSection.style.display = 'none'; // Hide cash input
                creditFields.style.display = 'block'; // Show credit fields
            }
        });
    });

    // Function to refresh the SO number after a successful transaction
    function refreshSoNumber() {
        fetch("{{ route('sales.newSo') }}")
            .then(response => response.json())
            .then(data => {
                if (data.so_number) {
                    document.getElementById('so-number').textContent = data.so_number;
                }
            })
            .catch(error => {
                console.error("Failed to refresh SO number:", error);
            });
    }

    // Checkout Functionality
    document.getElementById('checkoutButton').addEventListener('click', function () {
        // Check if any items are in the cart
        if (cart.length === 0) {
            alert("Please select an item first!");
            return;
        }

        // Validate customer selection
        const customerSelect = document.getElementById('customerSelect');
        const customerId = customerSelect.value;

        if (!customerId || customerId === "Select Customer") {
            alert("Please select a customer.");
            return;
        }

        // Validate payment method and related fields
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        const chargeTo = document.getElementById('charge-to').value;
        const cashAmount = parseFloat(document.getElementById('cashAmount').value) || 0;
        const totalAmount = parseFloat(document.getElementById('totalAmount').textContent.replace('₱', '')) || 0;

        if (paymentMethod === 'cash') {
            if (!cashAmount || isNaN(cashAmount)) {
                alert("Please enter the amount tendered.");
                return;
            }
            if (cashAmount < totalAmount) {
                alert("Insufficient amount tendered.");
                return;
            }
        } else if (paymentMethod === 'credit') {
            if (!chargeTo) {
                alert("Please select a charge option for credit payment.");
                return;
            }
        }

        // Prepare the data for the AJAX request
        const checkoutData = {
            _token: "{{ csrf_token() }}", // Include CSRF token
            so_number: $('#so-number').text(),
            customer_id: customerId,
            payment_method: paymentMethod,
            charge_to: paymentMethod === 'credit' ? chargeTo : null,
            amount_tendered: paymentMethod === 'cash' ? cashAmount : null,
            change_amount: paymentMethod === 'cash' ? (cashAmount - totalAmount) : null,
            total_items: document.getElementById('totalItems').textContent,
            total_amount: totalAmount,
            cart: cart.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: item.quantity,
                materials: item.materials.map(material => ({
                    id: material.id,
                    material_name: material.material_name,
                    quantity_used: material.quantity_used,
                    unit: material.unit,
                    cost_per_unit: material.cost_per_unit,
                    additional: material.additional || []
                }))
            }))
        };

        // Send the AJAX request
        fetch('{{ route("sales.processCheckout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(checkoutData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Show success message

                // Reset the cart and summary
                cart = [];
                updateCartDisplay();
                refreshSoNumber();

                // Reset the customer selection
                document.getElementById('customerSelect').selectedIndex = 0;

                // Reset the payment method to cash
                document.getElementById('cashPayment').checked = true;
                document.getElementById('cashInputSection').style.display = 'block';
                document.getElementById('credit-fields').style.display = 'none';

                // Reset the cash amount and change
                document.getElementById('cashAmount').value = '';
                document.getElementById('changeAmount').textContent = '₱0.00';
            } else {
                // Show modal with error message (like insufficient stock)
                if (data.low_stock && data.materials) {
                    let messageHtml = "<ul>";
                    data.materials.forEach(mat => {
                        messageHtml += `<li><strong>${mat.material_name}</strong> - Available: ${mat.available}, Needed: ${mat.needed}</li>`;
                    });
                    messageHtml += "</ul>";

                    document.getElementById('stockAlertMessage').innerHTML = `
                        <p>The following materials have insufficient stock:</p>
                        ${messageHtml}
                    `;
                    
                    const stockModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
                    stockModal.show();
                    
                    document.getElementById('stockAlertModal').addEventListener('hidden.bs.modal', function () {
                        // Remove leftover backdrops if any
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(backdrop => backdrop.remove());

                        // Remove 'modal-open' class from body if stuck
                        document.body.classList.remove('modal-open');
                        document.body.style.removeProperty('padding-right');
                    });
                } else {
                    // Fallback if it's another kind of error
                    alert(data.message || "An error occurred.");
                }
                const stockModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
                stockModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});
</script>
@endsection