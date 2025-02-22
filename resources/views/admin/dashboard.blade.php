@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard</h1>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Zones</h6>
                    <h2 class="mb-0">{{ $totalZones }}</h2>
                    <small>Active zones in system</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Active Drivers</h6>
                    <h2 class="mb-0">{{ $activeDrivers }}</h2>
                    <small>Currently on duty</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Locations</h6>
                    <h2 class="mb-0">{{ $totalLocations }}</h2>
                    <small>Registered locations</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Today's Payments</h6>
                    <h2 class="mb-0">GHS {{ number_format($todayPayments, 2) }}</h2>
                    <small>Total collections</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Activity Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Activity</h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="activity-feed">
                        @forelse($recentActivities as $activity)
                            <div class="activity-item mb-3">
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                <p class="mb-0">{{ $activity->description }}</p>
                            </div>
                        @empty
                            <p class="text-muted">No recent activities</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Access Reports -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Access Reports</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.reports.driver-activity') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Driver Activity Report
                            <span class="badge bg-primary rounded-pill">{{ $driverActivityCount }}</span>
                        </a>
                        <a href="{{ route('admin.reports.zone-statistics') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Zone Statistics
                            <span class="badge bg-primary rounded-pill">{{ $zoneStatisticsCount }}</span>
                        </a>
                        <a href="{{ route('admin.reports.system-usage') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            System Usage Report
                            <span class="badge bg-primary rounded-pill">{{ $systemUsageCount }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Health</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Server Status</label>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $serverHealth['cpu_usage'] }}%">
                                CPU: {{ $serverHealth['cpu_usage'] }}%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Memory Usage</label>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $serverHealth['memory_usage'] }}%">
                                Memory: {{ $serverHealth['memory_usage'] }}%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Storage</label>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $serverHealth['disk_usage'] }}%">
                                Disk: {{ $serverHealth['disk_usage'] }}%
                            </div>
                        </div>
                    </div>
                    <div class="text-muted">
                        <small>Last updated: {{ $serverHealth['last_checked']->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize activity chart
    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($activityData['labels']),
            datasets: [{
                label: 'System Activity',
                data: @json($activityData['values']),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'System Activity Over Time'
                }
            }
        }
    });
</script>
@endpush

@endsection
