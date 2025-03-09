@extends('layout.admin')

@section('title', 'Category Management')

@section('content')

<h1 class="mt-4">Category Management</h1>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Category</li>
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

<!-- Two-Column Layout -->
<div class="row">
    <!-- Left Column: Category Management -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Category</h5>
            </div>
            <div class="card-body">
                <!-- Add Category Form -->
                <form action="{{ route('admin.category.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <!-- Category Name Input -->
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="categoryName" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>

                <!-- Edit Category Form -->
                <form id="editCategoryForm" action="{{ route('admin.category.update') }}" method="POST" class="mt-4 d-none">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editCategoryId" name="id">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Edit Category Name</label>
                        <input type="text" class="form-control @error('name', 'category') is-invalid @enderror" id="editCategoryName" name="name" required autocomplete="off">
                        @error('name', 'category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-warning">Update Category</button>
                    <button type="button" class="btn btn-secondary" onclick="cancelEditCategory()">Cancel</button>
                </form>

                <!-- Category List -->
                <div class="mt-4">
                    <h6>Category List</h6>
                    <ul class="list-group">
                        @foreach($categories as $category)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $category->name }}
                                <div>
                                    <button class="btn btn-sm btn-primary" onclick="editCategory({{ $category->id }}, '{{ $category->name }}')">Edit</button>
                                    <form action="{{ route('admin.category.destroy', $category->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Subcategory Management -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Subcategory</h5>
            </div>
            <div class="card-body">
                <!-- Add Subcategory Form -->
                <form action="{{ route('admin.subcategory.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="subcategoryName" class="form-label">Subcategory Name</label>
                        <!-- Subcategory Name Input -->
                        <input type="text" class="form-control @error('sub_name') is-invalid @enderror" id="subcategoryName" name="sub_name" value="{{ old('sub_name') }}" required>
                        @error('sub_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="categorySelect" class="form-label">Parent Category</label>
                        <select class="form-select @error('category_id', 'subcategory') is-invalid @enderror" id="categorySelect" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id', 'subcategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Add Subcategory</button>
                </form>

                <!-- Edit Subcategory Form -->
                <form id="editSubcategoryForm" action="{{ route('admin.subcategory.update') }}" method="POST" class="mt-4 d-none">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editSubcategoryId" name="id">
                    <div class="mb-3">
                        <label for="editSubcategoryName" class="form-label">Edit Subcategory Name</label>
                        <input type="text" class="form-control @error('sub_name', 'subcategory') is-invalid @enderror" id="editSubcategoryName" name="sub_name" required>
                        @error('sub_name', 'subcategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="editCategorySelect" class="form-label">Parent Category</label>
                        <select class="form-select @error('category_id', 'subcategory') is-invalid @enderror" id="editCategorySelect" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id', 'subcategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-warning">Update Subcategory</button>
                    <button type="button" class="btn btn-secondary" onclick="cancelEditSubcategory()">Cancel</button>
                </form>

                <!-- Subcategory List -->
                <div class="mt-4">
                    <h6>Subcategory List</h6>
                    <ul class="list-group">
                        @foreach($subcategories as $subcategory)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $subcategory->sub_name }} ({{ $subcategory->category->name }})
                                <div>
                                    <button class="btn btn-sm btn-primary" onclick="editSubcategory({{ $subcategory->id }}, '{{ $subcategory->sub_name }}', {{ $subcategory->category_id }})">Edit</button>
                                    <form action="{{ route('admin.subcategory.destroy', $subcategory->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Edit Functionality -->
<script>
    // Edit Category
    function editCategory(id, name) {
        document.getElementById('editCategoryForm').classList.remove('d-none');
        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
    }

    function cancelEditCategory() {
        document.getElementById('editCategoryForm').classList.add('d-none');
    }

    // Edit Subcategory
    function editSubcategory(id, name, categoryId) {
        document.getElementById('editSubcategoryForm').classList.remove('d-none');
        document.getElementById('editSubcategoryId').value = id;
        document.getElementById('editSubcategoryName').value = name;
        document.getElementById('editCategorySelect').value = categoryId;
    }

    function cancelEditSubcategory() {
        document.getElementById('editSubcategoryForm').classList.add('d-none');
    }
</script>

@endsection