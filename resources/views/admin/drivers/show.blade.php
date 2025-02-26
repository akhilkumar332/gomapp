@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Driver Details</h5>
            <div>
                <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-primary me-2">
                    <i class="mdi mdi-pencil me-1"></i>Edit Driver
                </a>
                <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Personal Information</h6>
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 200px;">Name</th>
                            <td>{{ $driver->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $driver->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $driver->phone_number }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge {{ $driver->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($driver->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Last Active</th>
                            <td>
                                @if($driver->last_location_update)
                                    {{ $driver->last_location_update->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <h6 class="mb-3">Assigned Zones</h6>
                    @if($driver->zones->count() > 0)
                        <div class="list-group">
                            @foreach($driver->zones as $zone)
                                <a href="{{ route('admin.zones.show', $zone) }}" class="list-group-item list-group-item-action">
                                    {{ $zone->name }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No zones assigned</p>
                    @endif
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="mb-3">Recent Deliveries</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($driver->completedLocations()->latest('completed_at')->take(5)->get() as $location)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.locations.show', $location) }}" class="text-decoration-none">
                                                {{ $location->shop_name }}
                                            </a>
                                        </td>
                                        <td>{{ $location->completed_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                        <td>
                                            @if($location->payment_received)
                                                <span class="text-success">₵{{ number_format($location->payment_amount_received, 2) }}</span>
                                            @else
                                                <span class="text-danger">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent deliveries</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
