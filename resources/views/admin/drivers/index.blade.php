@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0">Drivers</h5>
            <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>Add New Driver
            </a>
        </div>
        <div class="card-body">
            <form id="search-form" action="{{ route('admin.drivers.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="nameSearch" name="name" 
                                   placeholder="Search by name" value="{{ request('name') }}">
                            <label for="nameSearch">Search by name</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="email" class="form-control" id="emailSearch" name="email" 
                                   placeholder="Search by email" value="{{ request('email') }}">
                            <label for="emailSearch">Search by email</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="phoneSearch" name="phone" 
                                   placeholder="Search by phone" value="{{ request('phone') }}">
                            <label for="phoneSearch">Search by phone</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-floating">
                            <select class="form-select" id="statusSearch" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            <label for="statusSearch">Status</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 h-100">
                            <i class="bx bx-search me-1"></i>Search
                        </button>
                    </div>
                </div>
            </form>

            <div id="driver-list">
                @include('admin.drivers.partials.driver-list')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    const driverList = document.getElementById('driver-list');

    function showLoading() {
        driverList.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading drivers...</div>
            </div>
        `;
    }

    function showError() {
        driverList.innerHTML = `
            <div class="text-center py-5">
                <div class="text-danger mb-2">
                    <i class="bx bx-error-circle display-4"></i>
                </div>
                <h5>Error Loading Drivers</h5>
                <p class="text-muted">Please try again later</p>
                <button onclick="window.location.reload()" class="btn btn-primary">
                    <i class="bx bx-refresh me-1"></i>Retry
                </button>
            </div>
        `;
    }

    function performSearch() {
        const formData = new FormData(searchForm);
        const searchParams = new URLSearchParams(formData);
        
        showLoading();

        // Use the current protocol (http or https)
        const currentUrl = new URL(window.location.href);
        const searchUrl = `${currentUrl.origin}${currentUrl.pathname}`;

        fetch(`${searchUrl}?${searchParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            driverList.innerHTML = data.html;
            // Reinitialize tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showError();
        });
    }

    // Handle form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });

    // Handle input changes with debounce
    let debounceTimer;
    const inputs = searchForm.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 500);
        });
    });

    // Handle pagination clicks
    driverList.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            
            showLoading();

            // Use the current protocol (http or https)
            const url = new URL(link.href);
            const paginationUrl = `${window.location.protocol}//${url.host}${url.pathname}${url.search}`;

            fetch(paginationUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                driverList.innerHTML = data.html;
                // Reinitialize tooltips
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                showError();
            });
        }
    });
});

// Delete confirmation
function confirmDelete(driverId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`delete-form-${driverId}`).submit();
        }
    });
}
</script>
@endpush
@endsection
