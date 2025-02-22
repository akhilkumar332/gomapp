@extends('layouts.driver')

@section('title', 'Driver Dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x me-3"></i>
                <div>
                    Welcome back, {{ Auth::user()->name }}! 
                    You are currently {{ Auth::user()->is_online ? 'Online' : 'Offline' }}.
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Today's Deliveries</h6>
                    <h2 class="mb-0">{{ $todayDeliveries }}</h2>
                    <small>Total deliveries today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Collections</h6>
                    <h2 class="mb-0">GHS {{ number_format($todayCollections, 2) }}</h2>
                    <small>Today's collections</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Success Rate</h6>
                    <h2 class="mb-0">{{ $successRate }}%</h2>
                    <small>Delivery success rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Assigned Zones</h6>
                    <h2 class="mb-0">{{ $assignedZonesCount }}</h2>
                    <small>Active zones</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Assigned Zones -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Assigned Zones</h5>
                    <a href="{{ route('driver.zones') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($assignedZones as $zone)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $zone->name }}</h6>
                                <small class="text-muted">{{ $zone->locations_count }} locations</small>
                            </div>
                            <a href="{{ route('driver.zones.show', $zone) }}" class="btn btn-sm btn-outline-primary">
                                View Details
                            </a>
                        </div>
                        @if(!$loop->last)
                            <hr>
                        @endif
                    @empty
                        <p class="text-muted mb-0">No zones assigned yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activity</h5>
                    <a href="{{ route('driver.activity') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($recentActivities as $activity)
                        <div class="d-flex mb-3">
                            <div class="activity-icon me-3">
                                @switch($activity->type)
                                    @case('delivery')
                                        <i class="fas fa-truck text-primary"></i>
                                        @break
                                    @case('payment')
                                        <i class="fas fa-money-bill text-success"></i>
                                        @break
                                    @default
                                        <i class="fas fa-check-circle text-info"></i>
                                @endswitch
                            </div>
                            <div>
                                <p class="mb-0">{{ $activity->description }}</p>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr>
                        @endif
                    @empty
                        <p class="text-muted mb-0">No recent activity.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Delivery Performance -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Delivery Performance</h5>
                </div>
                <div class="card-body">
                    <canvas id="deliveryChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize delivery performance chart
    const ctx = document.getElementById('deliveryChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($performanceData['labels']),
            datasets: [{
                label: 'Successful Deliveries',
                data: @json($performanceData['success']),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Failed Deliveries',
                data: @json($performanceData['failed']),
                borderColor: 'rgb(255, 99, 132)',
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
                    text: 'Delivery Performance (Last 7 Days)'
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

    // Update online status periodically
    setInterval(function() {
        $.post('/api/driver/status/ping', {
            _token: $('meta[name="csrf-token"]').attr('content')
        });
    }, 60000); // Ping every minute
</script>
@endpush

@endsection
