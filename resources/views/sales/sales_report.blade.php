@extends('layout.sales')

@section('title', 'Sales Report')

@section('content')

<h1 class="mt-4">Sales Report</h1>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('sales.transaction') }}">Home</a></li>
    <li class="breadcrumb-item active">Sales Report</li>
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-table me-1"></i>
            Sales Report
        </div>
        <div class="d-flex align-items-center">
            <label for="from_date" class="me-2">From:</label>
            <input type="date" id="from_date" class="form-control me-3">
            
            <label for="to_date" class="me-2">To:</label>
            <input type="date" id="to_date" class="form-control me-3">
            
            <button class="btn btn-success me-2" id="exportPdfBtn" title="Export PDF">
                <i class="fas fa-file-pdf"></i>
            </button>
            
            <button class="btn btn-info" id="exportExcelBtn" title="Export Excel">
                <i class="fas fa-file-excel"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>SO Number</th>
                    <th>Staff Name</th>
                    <th>Customer Name</th>
                    <th>Department</th>
                    <th>Member</th>
                    <th>Item Sold</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Cash/Credit</th>
                    <th>Charge To</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
            @foreach($sales as $sale)
                @foreach($sale->salesItems as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}</td>
                        <td>{{ $sale->po_number }}</td>
                        <td>{{ $sale->staff->full_name }}</td>
                        <td>{{ $sale->customer->full_name }}</td>
                        <td>{{ $sale->customer->department }}</td>
                        <td>{{ $sale->customer->membership_status }}</td>
                        <td>{{ $item->product_name }}</td> 
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->subtotal, 2) }}</td>
                        <td>{{ ucfirst($sale->payment_method) }}</td>
                        <td>{{ $sale->credit_payment_method ?? '-' }}</td>
                        <td>{{ $sale->remarks }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody> 
        </table>
    </div>
</div>


<script>
document.getElementById('exportExcelBtn').addEventListener('click', function () {
    let fromDate = document.getElementById('from_date').value;
    let toDate = document.getElementById('to_date').value;

    if (!fromDate || !toDate) {
        alert('Please select both From and To dates before exporting.');
        return;
    }

    window.location.href = `{{ route('sales.export.excel') }}?from_date=${fromDate}&to_date=${toDate}`;
});
</script>
@endsection