@extends('layout.sales')

@section('title', 'Customer\'s List')

@section('content')

<h1 class="mt-4">Customer List</h1>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('sales.transaction') }}">Home</a></li>
    <li class="breadcrumb-item active">Customer List</li>
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

<!-- Add New Customer & Export Buttons -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <div>
        <!-- Add New Customer -->
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus me-1"></i> Add New Customer
        </button>

        <!-- Add Outside Customer -->
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addOutsideCustomerModal">
            <i class="fas fa-user-plus me-1"></i> Add Outside Customer
        </button>

        <!-- Export Dropdown -->
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-export me-1"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('customers.export', ['format' => 'excel']) }}"><i class="fas fa-file-excel me-1"></i> Export as Excel</a></li>
                <li><a class="dropdown-item" href="{{ route('customers.export', ['format' => 'pdf']) }}"><i class="fas fa-file-pdf me-1"></i> Export as PDF</a></li>
            </ul>
        </div>
    </div>
</div>


<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Customer List
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Customer Full Name</th>
                    <th>Department</th>
                    <th>Customer Type</th>
                    <th>Member</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $key => $customer)
                    <tr>
                        <td>{{ $customer->employee_id ?? '-' }}</td>
                        <td>{{ $customer->full_name }}</td>
                        <td>{{ $customer->department }}</td>
                        <td>{{ $customer->type }}</td>
                        <td>
                            @if($customer->membership_status === 'Member')
                                <span class="badge bg-success">Member</span>
                            @else
                                <span class="badge bg-danger">Non-Member</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>     
        </table>
    </div>
</div>


<!-- Add Customer Modal (Employee & Department) -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="modal-body">
                    <!-- Type Selection (Radio Buttons) -->
                    <div class="mb-3">
                        <label class="form-label">Type of Customer</label>
                        <div class="form-check">
                            <input class="form-check-input @error('type') is-invalid @enderror" type="radio" name="type" id="typeEmployee" value="Employee" {{ old('type', 'Employee') == 'Employee' ? 'checked' : '' }}>
                            <label class="form-check-label" for="typeEmployee">Employee</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input @error('type') is-invalid @enderror" type="radio" name="type" id="typeDepartment" value="Department" {{ old('type') == 'Department' ? 'checked' : '' }}>
                            <label class="form-check-label" for="typeDepartment">Department</label>
                        </div>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Membership Status (Radio Buttons) -->
                    <div id="membershipStatus" class="mb-3 d-none">
                        <label class="form-label">Membership Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="membership_status" id="member" value="Member" {{ old('membership_status', 'Member') == 'Member' ? 'checked' : '' }}>
                            <label class="form-check-label" for="member">Member</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="membership_status" id="nonMember" value="Non-Member" {{ old('membership_status') == 'Non-Member' ? 'checked' : '' }}>
                            <label class="form-check-label" for="nonMember">Non-Member</label>
                        </div>
                        @error('membership_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Department Input -->
                    <div id="departmentField" class="d-none">
                        <div class="mb-3">
                            <label for="departmentInput" class="form-label">Department Name</label>
                            <input type="text" class="form-control @error('department') is-invalid @enderror" id="departmentInput" name="department" placeholder="Enter department name" value="{{ old('department') }}">
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Employee Fields -->
                    <div id="employeeFields" class="d-none">
                        <div class="mb-3">
                            <label for="employeeId" class="form-label">Employee ID</label>
                            <input type="text" class="form-control @error('employee_id') is-invalid @enderror" id="employeeId" name="employee_id" placeholder="Enter employee ID" value="{{ old('employee_id') }}" required>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="lastName" name="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="firstName" name="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="middleInitial" class="form-label">Middle Initial</label>
                                <input type="text" maxlength="1" class="form-control @error('middle_initial') is-invalid @enderror" id="middleInitial" name="middle_initial" placeholder="M.I." value="{{ old('middle_initial') }}">
                                @error('middle_initial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="employeeDepartment" class="form-label">Department</label>
                            <select class="form-select @error('department') is-invalid @enderror" id="employeeDepartment" name="department">
                                <option value="" disabled {{ old('department') ? '' : 'selected' }}>Select a department</option>
                                <option value="NON-MEMBER" {{ old('department') == 'NON-MEMBER' ? 'selected' : '' }}>NON-MEMBER</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department }}" {{ old('department') == $department ? 'selected' : '' }}>{{ $department }}</option>
                                @endforeach
                            </select>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Outside Customer Modal -->
<div class="modal fade" id="addOutsideCustomerModal" tabindex="-1" aria-labelledby="addOutsideCustomerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addOutsideCustomerModalLabel">Add Outside Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('customers.storeOutside') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Membership Status</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="outside_membership_status" id="outsideMember" value="Member" disabled>
                                <label class="form-check-label" for="outsideMember">Member</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="outside_membership_status" id="outsideNonMember" value="Non-Member" {{ old('outside_membership_status', 'Non-Member') == 'Non-Member' ? 'checked' : '' }}>
                                <label class="form-check-label" for="outsideNonMember">Non-Member</label>
                            </div>
                        @error('outside_membership_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="outsideLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control @error('outside_last_name') is-invalid @enderror" id="outsideLastName" name="outside_last_name" placeholder="Last Name" value="{{ old('outside_last_name') }}" required>
                            @error('outside_last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="outsideFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control @error('outside_first_name') is-invalid @enderror" id="outsideFirstName" name="outside_first_name" placeholder="First Name" value="{{ old('outside_first_name') }}" required>
                            @error('outside_first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="outsideMiddleInitial" class="form-label">Middle Initial</label>
                            <input type="text" maxlength="1" class="form-control @error('outside_middle_initial') is-invalid @enderror" id="outsideMiddleInitial" name="outside_middle_initial" placeholder="M.I." value="{{ old('outside_middle_initial') }}">
                            @error('outside_middle_initial')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.onload = function () {
    const typeEmployee = document.getElementById('typeEmployee');
    const typeDepartment = document.getElementById('typeDepartment');
    const departmentField = document.getElementById('departmentField');
    const employeeFields = document.getElementById('employeeFields');
    const membershipStatus = document.getElementById('membershipStatus');

    function handleTypeSelection() {
        membershipStatus.classList.add('d-none');
        departmentField.classList.add('d-none');
        employeeFields.classList.add('d-none');

        if (typeDepartment && typeDepartment.checked) {
            departmentField.classList.remove('d-none');
            membershipStatus.classList.remove('d-none');
        } else if (typeEmployee && typeEmployee.checked) {
            employeeFields.classList.remove('d-none');
            membershipStatus.classList.remove('d-none');
        }
    }

    handleTypeSelection();

    if (typeEmployee) typeEmployee.addEventListener('change', handleTypeSelection);
    if (typeDepartment) typeDepartment.addEventListener('change', handleTypeSelection);

    // Show the correct modal if there are validation errors
    @if ($errors->any())
        @if ($errors->hasBag('outside'))
            // Show Outside User Modal
            const addOutsideUserModal = new bootstrap.Modal(document.getElementById('addOutsideUserModal'));
            addOutsideUserModal.show();
        @else
            // Show Regular Add User Modal
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();
        @endif
    @endif
};

</script>

@endsection