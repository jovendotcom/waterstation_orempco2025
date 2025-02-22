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
            <button class="btn btn-primary me-3" id="filterBtn">Filter</button>
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
                    <th>Staff Name</th>
                    <th>Customer Name</th>
                    <th>SO Number</th>
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
                
            </tbody>     
        </table>
    </div>
</div>

@endsection
