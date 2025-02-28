@extends('layout.sales')

@section('title', 'Credit Sales')

@section('content')

<h1 class="mt-4">Credit Sales</h1>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('sales.transaction') }}">Home</a></li>
    <li class="breadcrumb-item active">Credit Sales</li>
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
                        <button class="btn btn-primary btn-sm view-sale" 
                            data-id="{{ $sale->id }}" 
                            data-po="{{ $sale->po_number }}" 
                            data-customer="{{ $sale->customer->full_name }}"
                            data-date="{{ $sale->created_at->format('Y-m-d H:i A') }}"
                            data-url="{{ route('sales.getItems', $sale->id) }}"> 
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>     
        </table>
    </div>
</div>

<!-- MODAL FOR VIEWING TRANSACTION ITEMS -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSaleModalLabel">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>SO Number:</strong> <span id="modalPoNumber"></span></p>
                <p><strong>Customer Name:</strong> <span id="modalCustomerName"></span></p>
                <p><strong>Date:</strong> <span id="modalDate"></span></p>
                <hr>
                <h5>Items</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modalItemsBody">
                        <tr><td colspan="4" class="text-center">No items loaded</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.body.addEventListener("click", function(event) {
            if (event.target.closest(".view-sale")) {
                let button = event.target.closest(".view-sale");

                let poNumber = button.dataset.po;
                let customerName = button.dataset.customer;
                let date = button.dataset.date;
                let url = button.dataset.url;

                document.getElementById("modalPoNumber").textContent = poNumber;
                document.getElementById("modalCustomerName").textContent = customerName;
                document.getElementById("modalDate").textContent = date;

                let itemsBody = document.getElementById("modalItemsBody");
                itemsBody.innerHTML = "<tr><td colspan='4' class='text-center'>Loading...</td></tr>";

                let modal = new bootstrap.Modal(document.getElementById("viewSaleModal"));
                modal.show();

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        itemsBody.innerHTML = "";
                        if (data.length === 0) {
                            itemsBody.innerHTML = "<tr><td colspan='4' class='text-center'>No items found</td></tr>";
                        } else {
                            data.forEach(item => {
                                let row = `<tr>
                                    <td>${item.product_name}</td>
                                    <td>${item.quantity}</td>
                                    <td>₱${parseFloat(item.price).toFixed(2)}</td>
                                    <td>₱${parseFloat(item.subtotal).toFixed(2)}</td>
                                </tr>`;
                                itemsBody.innerHTML += row;
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching sale items:", error);
                        itemsBody.innerHTML = "<tr><td colspan='4' class='text-center text-danger'>Failed to load items</td></tr>";
                    });
            }
        });
    });
</script>

@endsection
