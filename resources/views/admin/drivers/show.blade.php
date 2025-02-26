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
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile">
                        <i class="mdi mdi-account me-1"></i>Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="stats-tab" data-bs-toggle="tab" href="#stats">
                        <i class="mdi mdi-chart-bar me-1"></i>Statistics
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
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

                <!-- Stats Tab -->
                <div class="tab-pane fade" id="stats">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Total Deliveries</h6>
                                            <h3 class="mb-0">{{ $stats['total_deliveries'] }}</h3>
                                        </div>
                                        <div class="display-4">
                                            <i class="mdi mdi-truck-delivery"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Total Collections</h6>
                                            <h3 class="mb-0">₵{{ number_format($stats['total_collections'], 2) }}</h3>
                                        </div>
                                        <div class="display-4">
                                            <i class="mdi mdi-cash"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Average Per Day</h6>
                                            <h3 class="mb-0">{{ $stats['avg_deliveries_per_day'] }}</h3>
                                        </div>
                                        <div class="display-4">
                                            <i class="mdi mdi-chart-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex flex-column">
                                        <h6 class="card-title">Active Hours</h6>
                                        <select class="form-select form-select-sm mb-2 text-dark" id="activeHoursFilter">
                                            <option value="day">Today</option>
                                            <option value="week">This Week</option>
                                            <option value="month" selected>This Month</option>
                                        </select>
                                        <h3 class="mb-0" id="activeHoursValue">{{ $stats['active_hours'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Delivery Performance</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td>Last 7 Days</td>
                                                    <td class="text-end">{{ $stats['deliveries_7d'] }}</td>
                                                    <td style="width: 50%">
                                                        <div class="progress">
                                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                                style="width: {{ ($stats['deliveries_7d'] / max($stats['deliveries_30d'], 1)) * 100 }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Last 30 Days</td>
                                                    <td class="text-end">{{ $stats['deliveries_30d'] }}</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                style="width: {{ ($stats['deliveries_30d'] / max($stats['total_deliveries'], 1)) * 100 }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Collections Performance</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td>Last 7 Days</td>
                                                    <td class="text-end">₵{{ number_format($stats['collections_7d'], 2) }}</td>
                                                    <td style="width: 50%">
                                                        <div class="progress">
                                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                                style="width: {{ ($stats['collections_7d'] / max($stats['collections_30d'], 1)) * 100 }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Last 30 Days</td>
                                                    <td class="text-end">₵{{ number_format($stats['collections_30d'], 2) }}</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                style="width: {{ ($stats['collections_30d'] / max($stats['total_collections'], 1)) * 100 }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zone Distribution and Time Metrics -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Zone Distribution</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                                @foreach($charts['zone_distribution']['labels'] as $index => $zone)
                                                    <tr>
                                                        <td>{{ $zone }}</td>
                                                        <td class="text-end">{{ $charts['zone_distribution']['data'][$index] }}</td>
                                                        <td style="width: 50%">
                                                            <div class="progress">
                                                                <div class="progress-bar bg-info" role="progressbar" 
                                                                    style="width: {{ ($charts['zone_distribution']['data'][$index] / max($charts['zone_distribution']['total'], 1)) * 100 }}%">
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Time Metrics</h6>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td>Avg Time Per Delivery (7d)</td>
                                                    <td>{{ $stats['avg_time_per_delivery_7d'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Avg Time Per Delivery (30d)</td>
                                                    <td>{{ $stats['avg_time_per_delivery_30d'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td>On-Time Delivery Rate (7d)</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                style="width: {{ $stats['on_time_rate_7d'] }}%">
                                                                {{ number_format($stats['on_time_rate_7d'], 1) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>On-Time Delivery Rate (30d)</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                style="width: {{ $stats['on_time_rate_30d'] }}%">
                                                                {{ number_format($stats['on_time_rate_30d'], 1) }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle active hours filter change
    document.getElementById('activeHoursFilter').addEventListener('change', function() {
        const driverId = {{ $driver->id }};
        const period = this.value;
        
        fetch(`/admin/drivers/${driverId}/active-hours?period=${period}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('activeHoursValue').textContent = data.hours;
            });
    });
});
</script>
@endpush
@endsection
