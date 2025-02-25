@extends('layouts.admin')

@push('styles')
<style>
:root {
    --primary-color-rgb: 139, 92, 246;    /* #8B5CF6 */
    --info-color-rgb: 59, 130, 246;       /* #3B82F6 */
    --success-color-rgb: 16, 185, 129;    /* #10B981 */
    --warning-color-rgb: 245, 158, 11;    /* #F59E0B */
    --danger-color-rgb: 239, 68, 68;      /* #EF4444 */
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.chart-error {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 2;
    padding: 1rem;
    background: rgba(var(--danger-color-rgb), 0.1);
    color: rgb(var(--danger-color-rgb));
    text-align: center;
    border-radius: 0.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid" id="dashboard-content">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-primary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-map text-primary" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ $totalZones }}</h3>
                            <p class="text-muted mb-0">Total Zones</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-success bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-account-multiple text-success" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ $activeDrivers }}</h3>
                            <p class="text-muted mb-0">Active Drivers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-info bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-map-marker text-info" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ $totalLocations }}</h3>
                            <p class="text-muted mb-0">Total Locations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-warning bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-currency-usd text-warning" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">₵{{ number_format($todayCollections, 2) }}</h3>
                            <p class="text-muted mb-0">Today's Collections</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Delivery Performance</h5>
                    <div class="d-flex align-items-center">
                        <div class="btn-group" role="group" aria-label="Chart view options">
                            <button type="button" class="btn btn-sm btn-outline-primary active" data-view="deliveries">
                                <i class="mdi mdi-truck-delivery me-1"></i>
                                <span>Deliveries</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-view="collections">
                                <i class="mdi mdi-cash me-1"></i>
                                <span>Collections</span>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary ms-2" id="refreshChart">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="chart-container p-4">
                        <canvas id="deliveryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Delivery Success Rate</h6>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $performanceMetrics['delivery_success_rate'] }}%"></div>
                        </div>
                        <p class="mt-2 mb-0">{{ $performanceMetrics['delivery_success_rate'] }}% Success Rate</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Average Delivery Time</h6>
                        <div class="d-flex align-items-center">
                            <span class="display-6 me-2">{{ round($performanceMetrics['average_delivery_time']) }}</span>
                            <span class="text-muted">minutes</span>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-muted mb-2">Weekly Trend</h6>
                        <div class="d-flex align-items-center">
                            @php
                                $weeklyTrend = $performanceMetrics['weekly_trends']->last();
                                $previousWeek = $performanceMetrics['weekly_trends']->first();
                                $trend = $previousWeek && $previousWeek['deliveries'] > 0
                                    ? (($weeklyTrend['deliveries'] - $previousWeek['deliveries']) / $previousWeek['deliveries']) * 100
                                    : 0;
                            @endphp
                            <span class="h4 mb-0 me-2">{{ $weeklyTrend ? $weeklyTrend['deliveries'] : 0 }}</span>
                            @if($trend > 0)
                                <span class="text-success">
                                    <i class="mdi mdi-arrow-up-bold"></i>
                                    {{ number_format(abs($trend), 1) }}%
                                </span>
                            @elseif($trend < 0)
                                <span class="text-danger">
                                    <i class="mdi mdi-arrow-down-bold"></i>
                                    {{ number_format(abs($trend), 1) }}%
                                </span>
                            @else
                                <span class="text-muted">
                                    <i class="mdi mdi-minus"></i>
                                    0%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Activities</h5>
            <button class="btn btn-sm btn-outline-primary" id="refreshActivities">
                <i class="mdi mdi-refresh"></i> Refresh
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="activities-table-body">
                        @foreach($recentActivities as $activity)
                            <tr>
                                <td>{{ $activity->created_at->diffForHumans() }}</td>
                                <td>{{ $activity->user ? $activity->user->name : 'System' }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>
                                    @if($activity->status === 'success')
                                        <span class="badge bg-success">Success</span>
                                    @elseif($activity->status === 'warning')
                                        <span class="badge bg-warning">Warning</span>
                                    @elseif($activity->status === 'error')
                                        <span class="badge bg-danger">Error</span>
                                    @else
                                        <span class="badge bg-secondary">Info</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentChart = null;
const chartData = {
    deliveries: {
        labels: {!! json_encode($deliveryChart['labels']) !!},
        datasets: [{
            label: 'Completed Deliveries',
            data: {!! json_encode($deliveryChart['completed']) !!},
            borderColor: 'rgb(139, 92, 246)',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            borderWidth: 2
        }, {
            label: 'Total Deliveries',
            data: {!! json_encode($deliveryChart['total']) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 2
        }]
    },
    collections: {
        labels: {!! json_encode($deliveryChart['labels']) !!},
        datasets: [{
            label: 'Collections (₵)',
            data: {!! json_encode($deliveryChart['collections']) !!},
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 2
        }]
    }
};

function initChart(view) {
    const ctx = document.getElementById('deliveryChart').getContext('2d');
    
    if (currentChart) {
        currentChart.destroy();
    }

    currentChart = new Chart(ctx, {
        type: 'line',
        data: chartData[view],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (view === 'collections') {
                                return '₵' + value;
                            }
                            return value;
                        }
                    }
                }
            }
        }
    });
}

function refreshActivities() {
    const tbody = document.getElementById('activities-table-body');
    const refreshBtn = document.getElementById('refreshActivities');
    const refreshIcon = refreshBtn.querySelector('.mdi-refresh');
    
    refreshBtn.disabled = true;
    refreshIcon.classList.add('mdi-spin');
    
    fetch('/admin/dashboard/activities')
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = data.activities.map(activity => `
                <tr>
                    <td>${activity.time}</td>
                    <td>${activity.user}</td>
                    <td>${activity.description}</td>
                    <td>
                        <span class="badge bg-${activity.status_color}">${activity.status}</span>
                    </td>
                </tr>
            `).join('');
        })
        .catch(error => console.error('Failed to refresh activities:', error))
        .finally(() => {
            refreshBtn.disabled = false;
            refreshIcon.classList.remove('mdi-spin');
        });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart
    initChart('deliveries');
    
    // View toggle buttons
    document.querySelectorAll('[data-view]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-view]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            initChart(this.dataset.view);
        });
    });
    
    // Refresh buttons
    document.getElementById('refreshChart').addEventListener('click', function() {
        const activeView = document.querySelector('[data-view].active').dataset.view;
        initChart(activeView);
    });
    
    document.getElementById('refreshActivities').addEventListener('click', refreshActivities);
    
    // Auto-refresh activities
    setInterval(refreshActivities, 60000);
});
</script>
@endpush
