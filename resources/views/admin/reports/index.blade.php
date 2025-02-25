@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-primary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-truck-delivery text-primary" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ $totalDeliveries }}</h3>
                            <p class="text-muted mb-0">Total Deliveries</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-success bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-currency-usd text-success" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">₵{{ number_format($totalCollections, 2) }}</h3>
                            <p class="text-muted mb-0">Total Collections</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-info bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-account-multiple text-info" style="font-size: 24px;"></i>
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

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-warning bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-map-marker text-warning" style="font-size: 24px;"></i>
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
    </div>

    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Available Reports</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Performance Report</h6>
                                    <p class="card-text text-muted">View detailed performance metrics for drivers and zones.</p>
                                    <a href="{{ route('admin.reports.performance') }}" class="btn btn-primary">
                                        <i class="mdi mdi-chart-bar me-1"></i>View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Activity Log</h6>
                                    <p class="card-text text-muted">Track all system activities and user actions.</p>
                                    <a href="{{ route('admin.reports.activity') }}" class="btn btn-primary">
                                        <i class="mdi mdi-history me-1"></i>View Activities
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Export Data</h6>
                                    <p class="card-text text-muted">Export delivery and collection data to CSV/Excel.</p>
                                    <a href="{{ route('admin.reports.export') }}" class="btn btn-primary">
                                        <i class="mdi mdi-download me-1"></i>Export Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
