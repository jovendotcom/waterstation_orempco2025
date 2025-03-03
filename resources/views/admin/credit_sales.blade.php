@extends('layout.admin')

@section('title', 'Credit Sales')

@section('content')

<h1 class="mt-4">Credit Sales</h1>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Credit Sales</li>
</ol>

<!-- CSRF Token for AJAX Requests -->
<meta name="csrf-token" content="{{ csrf_token() }}">

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

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Customer List
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>SO Number</th>
                    <th>Staff Name</th>
                    <th>Customer Name</th>
                    <th>Member/Non-Member</th>
                    <th>Payment Method</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->created_at->format('Y-m-d H:i A') }}</td>
                    <td>{{ $sale->po_number }}</td>
                    <td>{{ $sale->staff->full_name }}</td>
                    <td>{{ $sale->customer->full_name }}</td>
                    <td>{{ $sale->customer->membership_status }}</td>
                    <td>{{ ucfirst($sale->payment_method) }}</td>
                    <td>
                        @if($sale->remarks === 'Paid')
                            <span class="badge bg-success">Paid</span>
                        @else
                            <span class="badge bg-danger">Not Paid</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm pay-sale"
                            data-id="{{ $sale->id }}" 
                            data-customer="{{ $sale->customer->full_name }}">
                            <i class="fas fa-money-bill-wave"></i> Pay
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>     
        </table>
    </div>
</div>

<!-- Payment Confirmation Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Confirm Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark this sale as <strong>Paid</strong>?</p>
                <p><strong>Customer:</strong> <span id="confirmCustomerName"></span></p>
                <input type="hidden" id="confirmSaleId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmPayBtn">Yes, Mark as Paid</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    document.body.addEventListener("click", function (event) {
        if (event.target.closest(".pay-sale")) {
            let button = event.target.closest(".pay-sale");
            let saleId = button.dataset.id;
            let customerName = button.dataset.customer;

            document.getElementById("confirmSaleId").value = saleId;
            document.getElementById("confirmCustomerName").textContent = customerName;

            let modal = new bootstrap.Modal(document.getElementById("paymentModal"));
            modal.show();
        }
    });

    document.getElementById("confirmPayBtn").addEventListener("click", function () {
        let saleId = document.getElementById("confirmSaleId").value;
        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

        fetch(`/admin/sales/pay/${saleId}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ status: "Paid" }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Find the row for the updated sale
                let row = document.querySelector(`button[data-id='${saleId}']`).closest("tr");

                // Find the remarks column and update it
                let remarksCell = row.querySelector("td:nth-child(7) span"); // 7th column = Remarks
                remarksCell.classList.remove("bg-danger");
                remarksCell.classList.add("bg-success");
                remarksCell.textContent = "Paid";

                // Disable the Pay button after marking as Paid
                let payButton = row.querySelector(".pay-sale");
                payButton.classList.add("disabled");
                payButton.setAttribute("disabled", "true");
                payButton.innerHTML = `<i class="fas fa-check"></i> Paid`;

                // Hide modal
                let modal = bootstrap.Modal.getInstance(document.getElementById("paymentModal"));
                modal.hide();
            } else {
                alert("Payment update failed!");
            }
        })
        .catch(error => console.error("Error updating payment:", error));
    });
});
</script>
@endsection
