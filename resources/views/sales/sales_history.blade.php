@extends('layout.sales')

@section('title', 'Sales History')

@section('content')
<h1 class="mt-4">Sales History</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('sales.transaction') }}">Home</a></li>
        <li class="breadcrumb-item active">Sales History</li>
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

<table id="datatablesSimple" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>SO Date</th>
            <th>SO Number</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->created_at->format('m-d-Y h:i:s a') }}</td>
                <td>{{ $sale->po_number }}</td>
                <td>
                    <button class="btn btn-primary btn-sm view-sale" 
                        data-so-date="{{ $sale->created_at->format('m-d-Y h:i:s a') }}" 
                        data-so-number="{{ $sale->po_number }}" 
                        data-staff="{{ $sale->staff->full_name ?? 'N/A' }}" 
                        data-customer="{{ $sale->customer->full_name ?? 'N/A' }}" 
                        data-payment-method="{{ $sale->payment_method }}"
                        data-total-amount="{{ $sale->total_amount }}" 
                        data-amount-tendered="{{ number_format($sale->amount_tendered, 2) }}" 
                        data-change="{{ number_format($sale->change_amount, 2) }}" 
                        data-charge-to="{{ $sale->credit_payment_method ?? 'N/A' }}" 
                        data-remarks="{{ $sale->remarks ?? 'N/A' }}" 
                        data-items='@json($sale->salesItems)' 
                        data-bs-toggle="modal" data-bs-target="#salesHistoryModal">
                        <i class="fa-solid fa-eye"></i> View
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


<!-- Sales History Modal -->
<div class="modal fade" id="salesHistoryModal" tabindex="-1" aria-labelledby="salesHistoryModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salesHistoryModalLabel">Sales Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>SO Date:</strong> <span id="modalSoDate"></span></p>
                <p><strong>SO Number:</strong> <span id="modalSoNumber"></span></p>
                <p><strong>Staff:</strong> <span id="modalStaff"></span></p> <!-- Staff Name Added -->

                <!-- Items Purchased Section -->
                <h5>Item(s) Purchased:</h5>
                <ul id="modalItemList"></ul>

                <!-- Additional Details -->
                <p><strong>Total Amount Paid:</strong> <span id="modalTotalAmount"></span></p>
                <p><strong>Customer Name:</strong> <span id="modalCustomer"></span></p>
                <p><strong>Payment Method:</strong> <span id="modalPaymentMethod"></span></p>

                <!-- Conditional Fields for Payment Method -->
                <div id="cashDetails" style="display: none;">
                    <p><strong>Amount Tendered:</strong> <span id="modalAmountTendered"></span></p>
                    <p><strong>Change:</strong> <span id="modalChange"></span></p>
                </div>

                <div id="creditDetails" style="display: none;">
                    <p><strong>Charge To:</strong> <span id="modalChargeTo"></span></p>
                </div>

                <p><strong>Remarks:</strong> <span id="modalRemarks"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- JavaScript for Handling Modal Data -->
<script>
// Function to handle button clicks and populate the modal
function setupSaleButtons() {
    document.querySelectorAll(".view-sale").forEach(button => {
        button.addEventListener("click", function () {
            console.log("🛒 View Sale Button Clicked!");

            try {
                // Extract sale details from button attributes
                document.getElementById("modalSoDate").textContent = this.getAttribute("data-so-date") || "N/A";
                document.getElementById("modalSoNumber").textContent = this.getAttribute("data-so-number") || "N/A";
                document.getElementById("modalStaff").textContent = this.getAttribute("data-staff") || "N/A";
                document.getElementById("modalCustomer").textContent = this.getAttribute("data-customer") || "N/A";
                document.getElementById("modalPaymentMethod").textContent = this.getAttribute("data-payment-method") || "N/A";
                document.getElementById("modalTotalAmount").textContent = this.getAttribute("data-total-amount") || "N/A";
                document.getElementById("modalRemarks").textContent = this.getAttribute("data-remarks") || "N/A";

                // Handle amount tendered and change (for Cash Payments)
                const paymentMethod = this.getAttribute("data-payment-method");
                const amountTendered = parseFloat(this.getAttribute("data-amount-tendered") || 0).toFixed(2);
                const changeAmount = parseFloat(this.getAttribute("data-change") || 0).toFixed(2);
                const chargeTo = this.getAttribute("data-charge-to") || "N/A";

                if (paymentMethod === "cash") {
                    document.getElementById("cashDetails").style.display = "block";
                    document.getElementById("creditDetails").style.display = "none";
                    document.getElementById("modalAmountTendered").innerHTML = "&#8369;" + amountTendered;
                    document.getElementById("modalChange").innerHTML = "&#8369;" + changeAmount;
                } else if (paymentMethod === "credit") {
                    document.getElementById("creditDetails").style.display = "block";
                    document.getElementById("cashDetails").style.display = "none";
                    document.getElementById("modalChargeTo").textContent = chargeTo;
                } else {
                    document.getElementById("cashDetails").style.display = "none";
                    document.getElementById("creditDetails").style.display = "none";
                }

                // Handle sales items
                const saleItemsRaw = this.getAttribute("data-items");
                console.log("📦 Raw Sale Items:", saleItemsRaw);

                const itemList = document.getElementById("modalItemList");
                itemList.innerHTML = ''; // Clear previous list

                if (!saleItemsRaw) {
                    itemList.innerHTML = '<li class="text-danger">❌ No items found</li>';
                    return;
                }

                const items = JSON.parse(saleItemsRaw);
                console.log("✅ Parsed Sale Items:", items);

                if (items.length === 0) {
                    itemList.innerHTML = '<li class="text-warning">⚠️ No items available</li>';
                    return;
                }

                // Append items with better formatting
                items.forEach(item => {
                    const li = document.createElement("li");
                    const itemName = item.product_name || "Unknown Item";
                    const quantity = item.quantity || 0;
                    const price = parseFloat(item.price || 0).toFixed(2);
                    const subtotal = parseFloat(item.subtotal || 0).toFixed(2);

                    li.innerHTML = `<strong>${itemName}</strong> - ${quantity} x &#8369;${price} = <span class="text-success">&#8369;${subtotal}</span>`;
                    itemList.appendChild(li);
                });

            } catch (e) {
                console.error("❌ Error parsing sale details:", e);
                document.getElementById("modalItemList").innerHTML = '<li class="text-danger">Error loading items</li>';
            }
        });
    });
}

// Observe changes to the table body (for dynamic updates)
const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
        if (mutation.type === "childList") {
            console.log("🔄 Table data changed. Re-attaching event listeners...");
            setupSaleButtons(); // Re-bind event listeners
        }
    }
});

// Start observing the table body for changes
const tableBody = document.querySelector("#datatablesSimple tbody");
if (tableBody) {
    observer.observe(tableBody, { childList: true, subtree: true });
}

// Run the function initially in case there are already rows
setupSaleButtons();

</script>



@endsection
