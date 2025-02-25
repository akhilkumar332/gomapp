@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Locations</h5>
            <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i>Add New Location
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Zone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locations as $location)
                            <tr>
                                <td>{{ $location->shop_name }}</td>
                                <td>{{ $location->address }}</td>
                                <td>
                                    @if($location->zone)
                                        <a href="{{ route('admin.zones.show', $location->zone) }}" class="text-decoration-none">
                                            {{ $location->zone->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">No Zone Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $location->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($location->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.locations.show', $location) }}" class="btn btn-sm btn-info me-1">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-sm btn-primary me-1">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this location?')">
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
                {{ $locations->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
