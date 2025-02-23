@extends('layouts.driver')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Driver Dashboard</h1>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title bg-primary-subtle rounded">
                                    <i class='bx bx-map text-primary fs-4'></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Today's Deliveries</h6>
                            <h2 class="mb-0">{{ $todayDeliveries }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title bg-success-subtle rounded">
                                    <i class='bx bx-money text-success fs-4'></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Today's Collections</h6>
                            <h2 class="mb-0">₵{{ number_format($todayCollections, 2) }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title bg-info-subtle rounded">
                                    <i class='bx bx-check-circle text-info fs-4'></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Success Rate</h6>
                            <h2 class="mb-0">{{ $successRate }}%</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title bg-warning-subtle rounded">
                                    <i class='bx bx-map-pin text-warning fs-4'></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Assigned Zones</h6>
                            <h2 class="mb-0">{{ $assignedZonesCount }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Performance Chart -->
        <div class="col-xl-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Delivery Performance</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                    <a href="{{ route('driver.activity') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        @forelse($recentActivities as $activity)
                            <div class="activity-item pb-3 mb-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="activity-indicator me-3">
                                        <div class="avatar avatar-xs">
                                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                <i class='bx bx-check'></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="activity-content flex-grow-1">
                                        <p class="mb-0">{{ $activity->description }}</p>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">No recent activity</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Zones -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Assigned Zones</h5>
                    <a href="{{ route('driver.zones') }}" class="btn btn-sm btn-primary">View All Zones</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Zone Name</th>
                                    <th>Locations</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignedZones as $zone)
                                    <tr>
                                        <td>{{ $zone->name }}</td>
                                        <td>{{ $zone->locations_count }} locations</td>
                                        <td>
                                            <span class="badge bg-{{ $zone->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($zone->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('driver.zones.show', $zone) }}" class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No zones assigned</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($performanceData['labels']) !!},
            datasets: [
                {
                    label: 'Successful Deliveries',
                    data: {!! json_encode($performanceData['success']) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true
                },
                {
                    label: 'Failed Deliveries',
                    data: {!! json_encode($performanceData['failed']) !!},
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
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
});
</script>
@endpush

@push('styles')
<style>
.activity-timeline {
    position: relative;
}

.activity-indicator {
    position: relative;
}

.activity-indicator::after {
    content: '';
    position: absolute;
    left: 50%;
    top: 100%;
    transform: translateX(-50%);
    width: 2px;
    height: calc(100% - 10px);
    background-color: #e9ecef;
}

.activity-item:last-child .activity-indicator::after {
    display: none;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
}

.avatar-xs {
    width: 1.5rem;
    height: 1.5rem;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush
