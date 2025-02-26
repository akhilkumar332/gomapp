@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Drivers</h5>
            <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i>Add New Driver
            </a>
        </div>
        <div class="card-body">
            <form id="search-form" action="{{ route('admin.drivers.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="name" 
                               placeholder="Search by name" value="{{ request('name') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="email" class="form-control" name="email" 
                               placeholder="Search by email" value="{{ request('email') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="phone" 
                               placeholder="Search by phone" value="{{ request('phone') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-magnify me-1"></i>Search
                        </button>
                    </div>
                </div>
            </form>

            <div id="driver-list" class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $driver)
                            <tr>
                                <td>{{ $driver->name }}</td>
                                <td>{{ $driver->email }}</td>
                                <td>{{ $driver->phone_number }}</td>
                                <td>
                                    <span class="badge {{ $driver->status === 'active' ? 'bg-success' : ($driver->status === 'suspended' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst($driver->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.drivers.show', $driver) }}" class="btn btn-sm btn-info me-1">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-sm btn-primary me-1">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.drivers.destroy', $driver) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this driver?')">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $drivers->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchForm = document.getElementById('search-form');
        const driverList = document.getElementById('driver-list');

        function ensureHttps(url) {
            // Create a URL object
            const urlObj = new URL(url, window.location.origin);
            // Force HTTPS
            urlObj.protocol = 'https:';
            return urlObj.toString();
        }

        function performSearch(url) {
            driverList.innerHTML = '<div class="text-center py-4">Loading...</div>';
            
            // Ensure HTTPS URL
            const secureUrl = ensureHttps(url);
            
            fetch(secureUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    console.error('Response status:', response.status);
                    throw new Error('Network response was not OK');
                }
                return response.json();
            })
            .then(data => {
                driverList.innerHTML = data.html;
            })
            .catch(error => {
                driverList.innerHTML = '<div class="text-danger text-center py-4">An error occurred. Please try again.</div>';
                console.error('AJAX Error:', error);
            });
        }

        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new URLSearchParams(new FormData(this));
            performSearch(this.action + '?' + formData.toString());
            return false;
        });

        driverList.addEventListener('click', function (e) {
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                performSearch(paginationLink.href);
                return false;
            }
        });
    });
</script>
@endsection
