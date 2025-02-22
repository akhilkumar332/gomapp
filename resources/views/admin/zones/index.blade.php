@extends('layouts.admin')

@section('title', 'Manage Zones')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Zones</h1>
        <a href="{{ route('admin.zones.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Zone
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
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones as $zone)
                            <tr>
                                <td>{{ $zone->name }}</td>
                                <td>{{ $zone->description }}</td>
                                <td>
                                    <span class="badge {{ $zone->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($zone->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.zones.edit', $zone) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.zones.destroy', $zone) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this zone?');">
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
                                <td colspan="4" class="text-center">No zones found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
