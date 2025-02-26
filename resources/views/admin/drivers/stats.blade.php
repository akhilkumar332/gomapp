@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Driver Statistics: {{ $driver->name }}</h5>
            <div>
                <a href="{{ route('admin.drivers.show', $driver) }}" class="btn btn-secondary me-2">
                    <i class="mdi mdi-account me-1"></i>Profile
                </a>
                <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Deliveries</h6>
                            <h2 class="mb-0">{{ $stats['total_deliveries'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Collections</h6>
                            <h2 class="mb-0">₵{{ number_format($stats['total_collections'], 2) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Average Per Day</h6>
                            <h2 class="mb-0">{{ $stats['avg_deliveries_per_day'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-title">Active Hours</h6>
                            <h2 class="mb-0">{{ $stats['active_hours'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Trends Chart -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Delivery Trends (Last 30 Days)</h6>
                            <canvas id="deliveryTrendsChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Zone Distribution</h6>
                            <canvas id="zoneDistributionChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collections and Activity -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Collections Over Time</h6>
                            <canvas id="collectionsChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Activity Hours</h6>
                            <canvas id="activityChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-4">Performance Metrics</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Last 7 Days</th>
                                    <th>Last 30 Days</th>
                                    <th>All Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Completed Deliveries</td>
                                    <td>{{ $stats['deliveries_7d'] }}</td>
                                    <td>{{ $stats['deliveries_30d'] }}</td>
                                    <td>{{ $stats['total_deliveries'] }}</td>
                                </tr>
                                <tr>
                                    <td>Collections (₵)</td>
                                    <td>{{ number_format($stats['collections_7d'], 2) }}</td>
                                    <td>{{ number_format($stats['collections_30d'], 2) }}</td>
                                    <td>{{ number_format($stats['total_collections'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Average Time Per Delivery</td>
                                    <td>{{ $stats['avg_time_per_delivery_7d'] }}</td>
                                    <td>{{ $stats['avg_time_per_delivery_30d'] }}</td>
                                    <td>{{ $stats['avg_time_per_delivery_all'] }}</td>
                                </tr>
                                <tr>
                                    <td>On-Time Delivery Rate</td>
                                    <td>{{ number_format($stats['on_time_rate_7d'], 1) }}%</td>
                                    <td>{{ number_format($stats['on_time_rate_30d'], 1) }}%</td>
                                    <td>{{ number_format($stats['on_time_rate_all'], 1) }}%</td>
                                </tr>
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
    // Delivery Trends Chart
    new Chart(document.getElementById('deliveryTrendsChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($charts['delivery_trends']['labels']) !!},
            datasets: [{
                label: 'Deliveries',
                data: {!! json_encode($charts['delivery_trends']['data']) !!},
                borderColor: '#4723D9',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Zone Distribution Chart
    new Chart(document.getElementById('zoneDistributionChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($charts['zone_distribution']['labels']) !!},
            datasets: [{
                data: {!! json_encode($charts['zone_distribution']['data']) !!},
                backgroundColor: ['#4723D9', '#28a745', '#17a2b8', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Collections Chart
    new Chart(document.getElementById('collectionsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($charts['collections']['labels']) !!},
            datasets: [{
                label: 'Collections (₵)',
                data: {!! json_encode($charts['collections']['data']) !!},
                backgroundColor: '#28a745'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Activity Chart
    new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($charts['activity']['labels']) !!},
            datasets: [{
                label: 'Active Hours',
                data: {!! json_encode($charts['activity']['data']) !!},
                borderColor: '#ffc107',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush
