@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Performance Metrics</h5>
            <div>
                <button class="btn btn-primary me-2" onclick="refreshData()">
                    <i class="mdi mdi-refresh me-1"></i>Refresh Data
                </button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back to Reports
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Date Range Filter -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="mdi mdi-calendar"></i></span>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                        <span class="input-group-text">to</span>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                        <button class="btn btn-primary" onclick="filterByDate()">Filter</button>
                    </div>
                </div>
            </div>

            <!-- Performance Summary -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary bg-opacity-10 h-100">
                        <div class="card-body">
                            <h6 class="card-title text-primary">Delivery Success Rate</h6>
                            <h2 class="mb-2">{{ number_format($metrics['success_rate'], 1) }}%</h2>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: {{ $metrics['success_rate'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-success bg-opacity-10 h-100">
                        <div class="card-body">
                            <h6 class="card-title text-success">Average Collections</h6>
                            <h2 class="mb-2">₵{{ number_format($metrics['avg_collections'], 2) }}</h2>
                            <p class="text-muted mb-0">Per Delivery</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-info bg-opacity-10 h-100">
                        <div class="card-body">
                            <h6 class="card-title text-info">On-Time Deliveries</h6>
                            <h2 class="mb-2">{{ number_format($metrics['ontime_rate'], 1) }}%</h2>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-info" style="width: {{ $metrics['ontime_rate'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-warning bg-opacity-10 h-100">
                        <div class="card-body">
                            <h6 class="card-title text-warning">Average Response Time</h6>
                            <h2 class="mb-2">{{ number_format($metrics['avg_response_time'], 1) }}</h2>
                            <p class="text-muted mb-0">Minutes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Driver Performance -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Driver Performance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Deliveries</th>
                                    <th>Success Rate</th>
                                    <th>Collections</th>
                                    <th>Average Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($driverMetrics as $driver)
                                <tr>
                                    <td>{{ $driver->name }}</td>
                                    <td>{{ $driver->total_deliveries }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: {{ $driver->success_rate }}%"></div>
                                            </div>
                                            <span>{{ number_format($driver->success_rate, 1) }}%</span>
                                        </div>
                                    </td>
                                    <td>₵{{ number_format($driver->total_collections, 2) }}</td>
                                    <td>{{ number_format($driver->avg_delivery_time, 1) }} mins</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Zone Performance -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Zone Performance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Zone</th>
                                    <th>Total Deliveries</th>
                                    <th>Success Rate</th>
                                    <th>Total Collections</th>
                                    <th>Active Drivers</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zoneMetrics as $zone)
                                <tr>
                                    <td>{{ $zone->name }}</td>
                                    <td>{{ $zone->total_deliveries }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: {{ $zone->success_rate }}%"></div>
                                            </div>
                                            <span>{{ number_format($zone->success_rate, 1) }}%</span>
                                        </div>
                                    </td>
                                    <td>₵{{ number_format($zone->total_collections, 2) }}</td>
                                    <td>{{ $zone->active_drivers }}</td>
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

@push('scripts')
<script>
function filterByDate() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    window.location.href = `{{ route('admin.reports.performance') }}?start_date=${startDate}&end_date=${endDate}`;
}

function refreshData() {
    window.location.reload();
}
</script>
@endpush
@endsection
