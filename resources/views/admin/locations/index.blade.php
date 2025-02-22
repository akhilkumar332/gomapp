@extends('layouts.admin')

@section('title', 'Manage Locations')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Locations</h1>
        <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Location
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Shop Name</th>
                            <th>Address</th>
                            <th>GhanaPostGPS Code</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $location)
                            <tr>
                                <td>{{ $location->shop_name }}</td>
                                <td>{{ $location->address }}</td>
                                <td>{{ $location->ghana_post_gps_code }}</td>
                                <td>
                                    <span class="badge {{ $location->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($location->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this location?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No locations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
