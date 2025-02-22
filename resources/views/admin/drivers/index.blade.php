@extends('layouts.admin')

@section('title', 'Manage Drivers')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Drivers</h1>
        <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Driver
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            <tr>
                                <td>{{ $driver->name }}</td>
                                <td>{{ $driver->email }}</td>
                                <td>{{ $driver->phone_number }}</td>
                                <td>
                                    <span class="badge {{ $driver->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($driver->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.drivers.destroy', $driver) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this driver?');">
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
                                <td colspan="5" class="text-center">No drivers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
