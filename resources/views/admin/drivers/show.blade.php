@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="avatar-lg me-3 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                <span class="h3 text-primary mb-0">{{ strtoupper(substr($driver->name, 0, 2)) }}</span>
            </div>
            <div>
                <h4 class="mb-1">{{ $driver->name }}</h4>
                <div class="text-muted">Driver ID: {{ $driver->id }}</div>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-primary me-2">
                <i class="bx bx-edit me-1"></i>Edit Driver
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom mb-4">
        <li class="nav-item">
            <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile">
                <i class="bx bx-user me-1"></i>Profile
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="stats-tab" data-bs-toggle="tab" href="#stats">
                <i class="bx bx-chart me-1"></i>Statistics
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Profile Tab -->
        <div class="tab-pane fade show active" id="profile">
            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm me-3 bg-light rounded-circle">
                                        <i class="bx bx-envelope text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Email Address</div>
                                        <div>{{ $driver->email }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm me-3 bg-light rounded-circle">
                                        <i class="bx bx-phone text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Phone Number</div>
                                        <div>{{ $driver->phone_number }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm me-3 bg-light rounded-circle">
                                        <i class="bx bx-current-location text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Last Active</div>
                                        <div>
                                            @if($driver->last_location_update)
                                                {{ $driver->last_location_update->format('M d, Y H:i') }}
                                                <span class="text-muted">({{ $driver->last_location_update->diffForHumans() }})</span>
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-light rounded-circle">
                                        <i class="bx bx-check-circle text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted">Status</div>
                                        <div>
                                            <span class="badge {{ 
                                                $driver->status === 'active' ? 'bg-success' : 
                                                ($driver->status === 'suspended' ? 'bg-warning' : 'bg-danger') 
                                            }} rounded-pill">
                                                <i class="bx {{ 
                                                    $driver->status === 'active' ? 'bx-check' : 
                                                    ($driver->status === 'suspended' ? 'bx-pause' : 'bx-x') 
                                                }} me-1"></i>
                                                {{ ucfirst($driver->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Zones -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Assigned Zones</h5>
                        </div>
                        <div class="card-body">
                            @if($driver->zones->count() > 0)
                                <div class="row g-3">
                                    @foreach($driver->zones as $zone)
                                        <div class="col-md-6">
                                            <a href="{{ route('admin.zones.show', $zone) }}" 
                                               class="card h-100 border-0 shadow-sm hover-shadow text-decoration-none">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-3 bg-info-subtle rounded-circle">
                                                            <i class="bx bx-map text-info fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $zone->name }}</h6>
                                                            <div class="small text-muted">
                                                                {{ $zone->active_locations_count }} active
                                                                @if($zone->completed_locations_count > 0)
                                                                    • {{ $zone->completed_locations_count }} completed
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <div class="avatar-md mx-auto mb-3 bg-light rounded-circle">
                                        <i class="bx bx-map-alt text-secondary display-6"></i>
                                    </div>
                                    <h6>No Zones Assigned</h6>
                                    <p class="text-muted mb-0">This driver has not been assigned to any zones yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Deliveries -->
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Recent Deliveries</h5>
                </div>
                <div class="card-body">
                    @if($driver->completedLocations()->exists())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Location</th>
                                        <th>Completed</th>
                                        <th>Duration</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driver->completedLocations()->latest('completed_at')->take(5)->get() as $location)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.locations.show', $location) }}" 
                                                   class="text-decoration-none">
                                                    <div class="fw-medium text-dark">{{ $location->shop_name }}</div>
                                                    <small class="text-muted">{{ Str::limit($location->address, 50) }}</small>
                                                </a>
                                            </td>
                                            <td>
                                                <div data-bs-toggle="tooltip" 
                                                     title="{{ $location->completed_at->format('M d, Y H:i') }}">
                                                    {{ $location->completed_at->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($location->started_at && $location->completed_at)
                                                    {{ $location->started_at->diffInMinutes($location->completed_at) }} mins
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($location->payment_received)
                                                    <div class="text-success">
                                                        <i class="bx bx-check-circle me-1"></i>
                                                        ₵{{ number_format($location->payment_amount_received, 2) }}
                                                    </div>
                                                @else
                                                    <div class="text-danger">
                                                        <i class="bx bx-x-circle me-1"></i>Pending
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success rounded-pill">
                                                    <i class="bx bx-check me-1"></i>Completed
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="avatar-md mx-auto mb-3 bg-light rounded-circle">
                                <i class="bx bx-package text-secondary display-6"></i>
                            </div>
                            <h6>No Deliveries Yet</h6>
                            <p class="text-muted mb-0">This driver hasn't completed any deliveries yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Tab -->
        <div class="tab-pane fade" id="stats">
            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="card-title mb-0 text-white">Total Deliveries</h6>
                                </div>
                                <div class="avatar-md bg-primary-subtle rounded-circle">
                                    <i class="bx bx-package text-primary display-6"></i>
                                </div>
                            </div>
                            <h3 class="mb-2 text-white">{{ $stats['total_deliveries'] }}</h3>
                            <div class="small text-white">
                                @php
                                    $deliveryChange = $stats['deliveries_7d'] - ($stats['deliveries_30d'] / 4);
                                    $deliveryChangePercent = $stats['deliveries_30d'] > 0 
                                        ? ($deliveryChange / ($stats['deliveries_30d'] / 4)) * 100 
                                        : 0;
                                @endphp
                                <span class="text-white">
                                    <i class="bx {{ $deliveryChange >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                                    {{ abs(number_format($deliveryChangePercent, 1)) }}%
                                </span>
                                vs last week
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="card-title mb-0 text-white">Total Collections</h6>
                                </div>
                                <div class="avatar-md bg-success-subtle rounded-circle">
                                    <i class="bx bx-money text-success display-6"></i>
                                </div>
                            </div>
                            <h3 class="mb-2 text-white">₵{{ number_format($stats['total_collections'], 2) }}</h3>
                            <div class="small text-white">
                                @php
                                    $collectionChange = $stats['collections_7d'] - ($stats['collections_30d'] / 4);
                                    $collectionChangePercent = $stats['collections_30d'] > 0 
                                        ? ($collectionChange / ($stats['collections_30d'] / 4)) * 100 
                                        : 0;
                                @endphp
                                <span class="text-white">
                                    <i class="bx {{ $collectionChange >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i>
                                    {{ abs(number_format($collectionChangePercent, 1)) }}%
                                </span>
                                vs last week
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="card-title mb-0 text-white">Average Per Day</h6>
                                </div>
                                <div class="avatar-md bg-info-subtle rounded-circle">
                                    <i class="bx bx-chart text-info display-6"></i>
                                </div>
                            </div>
                            <h3 class="mb-2 text-white">{{ number_format($stats['avg_deliveries_per_day'], 1) }}</h3>
                            <div class="small text-white">Deliveries per day this month</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="card-title mb-0 text-white">Active Hours</h6>
                                </div>
                                <div class="avatar-md bg-warning-subtle rounded-circle">
                                    <i class="bx bx-time text-warning display-6"></i>
                                </div>
                            </div>
                            <div class="mb-2">
                                <select class="form-select form-select-sm bg-warning-subtle text-dark border-0" 
                                        id="activeHoursFilter">
                                    <option value="day">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month" selected>This Month</option>
                                </select>
                            </div>
                            <h3 class="mb-0 text-white" id="activeHoursValue">{{ $stats['active_hours'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Delivery Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="deliveryChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Time Metrics</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>Average Time Per Delivery (7d)</div>
                                    <div class="fw-medium">{{ $stats['avg_time_per_delivery_7d'] }}</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>Average Time Per Delivery (30d)</div>
                                    <div class="fw-medium">{{ $stats['avg_time_per_delivery_30d'] }}</div>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-3">On-Time Delivery Rate</h6>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>Last 7 Days</div>
                                        <div>{{ number_format($stats['on_time_rate_7d'], 1) }}%</div>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: {{ $stats['on_time_rate_7d'] }}%">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>Last 30 Days</div>
                                        <div>{{ number_format($stats['on_time_rate_30d'], 1) }}%</div>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: {{ $stats['on_time_rate_30d'] }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zone Distribution -->
            <div class="card mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Zone Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="zoneChart" height="300"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        @foreach($charts['zone_distribution']['labels'] as $index => $zone)
                                            <tr>
                                                <td>
                                                    <i class="bx bx-map-pin me-2 text-primary"></i>
                                                    {{ $zone }}
                                                </td>
                                                <td class="text-end">
                                                    {{ $charts['zone_distribution']['data'][$index] }}
                                                    <small class="text-muted">
                                                        ({{ number_format(($charts['zone_distribution']['data'][$index] / $charts['zone_distribution']['total']) * 100, 1) }}%)
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

    // Handle active hours filter
    const activeHoursFilter = document.getElementById('activeHoursFilter');
    const activeHoursValue = document.getElementById('activeHoursValue');

    if (activeHoursFilter) {
        activeHoursFilter.addEventListener('change', function() {
            const period = this.value;
            const loadingValue = activeHoursValue.textContent;
            activeHoursValue.innerHTML = '<div class="spinner-border spinner-border-sm text-light" role="status"></div>';
            
            fetch(`/admin/drivers/{{ $driver->id }}/active-hours?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    activeHoursValue.textContent = data.hours;
                })
                .catch(error => {
                    console.error('Error:', error);
                    activeHoursValue.textContent = loadingValue;
                    showToast({
                        title: 'Error',
                        message: 'Failed to update active hours',
                        type: 'error'
                    });
                });
        });
    }

    // Initialize Charts
    const ctx = document.getElementById('deliveryChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Completed',
                    data: @json($stats['deliveries_7d_daily'] ?? array_fill(0, 7, 0)),
                    borderColor: '#0d6efd',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    const zoneCtx = document.getElementById('zoneChart');
    if (zoneCtx) {
        new Chart(zoneCtx, {
            type: 'doughnut',
            data: {
                labels: @json($charts['zone_distribution']['labels']),
                datasets: [{
                    data: @json($charts['zone_distribution']['data']),
                    backgroundColor: [
                        '#0d6efd', '#198754', '#ffc107', '#dc3545',
                        '#6610f2', '#fd7e14', '#20c997', '#0dcaf0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
