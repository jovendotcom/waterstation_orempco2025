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



    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Sales History
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>SO Date</th>
                        <th>SO Number</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td>{{ $sale->created_at->format('m-d-Y h:i:s a') }}</td>
                            <td>{{ $sale->po_number }}</td>
                            <td>
                                @if ($sale->remarks == 'Not Paid')
                                    <span class="badge bg-danger">{{ $sale->remarks }}</span>
                                @else 
                                    <span class="badge bg-success">{{ $sale->remarks }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="#" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
