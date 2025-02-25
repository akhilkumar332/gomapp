@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Location Details</h5>
            <div>
                <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-primary me-2">
                    <i class="mdi mdi-pencil me-1"></i>Edit Location
                </a>
                <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">
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
                            <td>{{ $location->name }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $location->address }}</td>
                        </tr>
                        <tr>
                            <th>Zone:</th>
                            <td>
                                @if($location->zone)
                                    <a href="{{ route('admin.zones.show', $location->zone) }}" class="text-decoration-none">
                                        {{ $location->zone->name }}
                                    </a>
                                @else
                                    <span class="text-muted">No Zone Assigned</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge {{ $location->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($location->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $location->created_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $location->updated_at->format('M d, Y H:i A') }}</td>
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
                                            <i class="mdi mdi-truck-delivery text-primary" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h3 class="mb-1">{{ $location->deliveries_count ?? 0 }}</h3>
                                            <p class="text-muted mb-0">Total Deliveries</p>
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
                                            <i class="mdi mdi-currency-usd text-success" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h3 class="mb-1">₵{{ number_format($location->total_collections ?? 0, 2) }}</h3>
                                            <p class="text-muted mb-0">Total Collections</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($location->notes)
                    <div class="mt-4">
                        <h6 class="mb-3">Additional Notes</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                {{ $location->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($location->deliveries && $location->deliveries->count() > 0)
            <div class="mt-4">
                <h6 class="mb-3">Recent Deliveries</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Driver</th>
                                <th>Status</th>
                                <th>Collection</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($location->deliveries->take(5) as $delivery)
                            <tr>
                                <td>{{ $delivery->created_at->format('M d, Y H:i A') }}</td>
                                <td>{{ $delivery->driver->name }}</td>
                                <td>
                                    <span class="badge {{ $delivery->status === 'completed' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($delivery->status) }}
                                    </span>
                                </td>
                                <td>₵{{ number_format($delivery->collection_amount, 2) }}</td>
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
