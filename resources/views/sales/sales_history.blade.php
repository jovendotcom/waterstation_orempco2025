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
<div class="modal fade" id="salesHistoryModal" tabindex="-1" aria-labelledby="salesHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salesHistoryModalLabel">Sales Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>SO Date:</strong> <span id="modalSoDate"></span></p>
                <p><strong>SO Number:</strong> <span id="modalSoNumber"></span></p>

                <!-- Add a section for the items -->
                <h5>Items:</h5>
                <ul id="modalItemList">
                    <!-- Items will be populated here -->
                </ul>
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
            const saleItems = this.getAttribute("data-items");
            console.log("Sale items raw data:", saleItems); // Debugging

            try {
                const items = JSON.parse(saleItems);
                console.log("Parsed items:", items); // Debugging

                // Set SO date and number
                document.getElementById("modalSoDate").textContent = this.getAttribute("data-so-date");
                document.getElementById("modalSoNumber").textContent = this.getAttribute("data-so-number");

                // Clear the list
                const itemList = document.getElementById("modalItemList");
                itemList.innerHTML = '';

                if (items.length === 0) {
                    itemList.innerHTML = '<li>No items found</li>';
                    return;
                }

                // Append items
                items.forEach(item => {
                    const li = document.createElement("li");
                    li.textContent = `${item.product_name} - ${item.quantity} x ${item.price} = ${item.subtotal}`;
                    itemList.appendChild(li);
                });

            } catch (e) {
                console.error("Error parsing items:", e);
                document.getElementById("modalItemList").innerHTML = '<li>Error loading items</li>';
            }
        });
    });
}

// Observe changes to the table body
const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
        if (mutation.type === "childList") {
            console.log("Table data changed. Re-attaching event listeners...");
            setupSaleButtons(); // Re-bind event listeners to new elements
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
