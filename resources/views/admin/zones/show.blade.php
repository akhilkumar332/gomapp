@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Zone Details</h5>
            <div>
                <a href="{{ route('admin.zones.edit', $zone) }}" class="btn btn-primary me-2">
                    <i class="mdi mdi-pencil me-1"></i>Edit Zone
                </a>
                <a href="{{ route('admin.zones.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Basic Information</h6>
                    <table class="table">
                        <tr>
                            <th style="width: 150px;">Name:</th>
                            <td>{{ $zone->name }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $zone->description }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge {{ $zone->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($zone->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Latitude:</th>
                            <td>{{ $zone->center_lat }}</td>
                        </tr>
                        <tr>
                            <th>Longitude:</th>
                            <td>{{ $zone->center_lng }}</td>
                        </tr>
                        <tr>
                            <th>Radius:</th>
                            <td>{{ $zone->radius }} km</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $zone->created_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $zone->updated_at->format('M d, Y H:i A') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Statistics</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="card bg-primary bg-opacity-10 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="mdi mdi-map-marker text-primary" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h3 class="mb-1">{{ $zone->locations_count }}</h3>
                                            <p class="text-muted mb-0">Total Locations</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card bg-success bg-opacity-10 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="mdi mdi-account-multiple text-success" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h3 class="mb-1">{{ $zone->getActiveDriversCountAttribute() }}</h3>
                                            <p class="text-muted mb-0">Active Drivers</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($zone->locations->count() > 0)
            <div class="mt-4">
                <h6 class="mb-3">Locations in this Zone</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($zone->locations as $location)
                            <tr>
                                <td>{{ $location->shop_name }}</td>
                                <td>{{ $location->address }}</td>
                                <td>
                                    <span class="badge {{ $location->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($location->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.locations.show', $location) }}" class="btn btn-sm btn-info">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if($zone->drivers->count() > 0)
            <div class="mt-4">
                <h6 class="mb-3">Drivers in this Zone</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($zone->drivers as $driver)
                            <tr>
                                <td>{{ $driver->name }}</td>
                                <td>{{ $driver->formatted_phone }}</td>
                                <td>
                                    <span class="badge {{ $driver->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($driver->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.drivers.show', $driver) }}" class="btn btn-sm btn-info">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
