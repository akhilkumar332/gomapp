@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Delivery Zones</h5>
            <a href="{{ route('admin.zones.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i>Add New Zone
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Locations</th>
                            <th>Drivers</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zones as $zone)
                            <tr>
                                <td>{{ $zone->name }}</td>
                                <td>{{ Str::limit($zone->description, 50) }}</td>
                                <td>{{ $zone->locations_count }} locations</td>
                                <td>{{ $zone->drivers_count }} drivers</td>
                                <td>
                                    <span class="badge {{ $zone->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($zone->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.zones.show', $zone) }}" class="btn btn-sm btn-info me-1">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.zones.edit', $zone) }}" class="btn btn-sm btn-primary me-1">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.zones.destroy', $zone) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this zone?')">
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
                {{ $zones->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
