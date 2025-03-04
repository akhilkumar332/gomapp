 @extends('layouts.admin')

@push('styles')
<style>
    /* Tooltip Icon Styles */
    .tooltip-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        font-style: normal;
        font-size: 12px;
        font-weight: 600;
        color: #6366F1;
        background-color: #EEF2FF;
        border: 1.5px solid #818CF8;
        border-radius: 50%;
        cursor: help;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Position tooltip icons */
    .position-relative .tooltip-icon {
        top: 12px;
        right: 12px;
    }

    .card-title .tooltip-icon {
        margin-left: 8px;
        position: relative;
        top: -1px;
    }

    /* Performance metrics tooltips */
    .card-body .d-flex .tooltip-icon {
        margin-left: 12px;
        margin-top: 0;
    }

    /* Ensure proper spacing in flex containers */
    .justify-content-between {
        gap: 8px;
    }

.tooltip-icon:hover {
    color: #4F46E5;
    background-color: #E0E7FF;
    border-color: #6366F1;
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
}

/* Ensure consistent placement in different contexts */
.card-title .tooltip-icon {
    margin-top: 1px;
}

.d-flex .tooltip-icon {
    margin-top: 2px;
}

h6 .tooltip-icon {
    margin-top: 0;
}

/* Custom tooltip style */
.tooltip {
    opacity: 0;
}

.tooltip .tooltip-inner {
    background-color: #1E293B;
    color: #F8FAFC;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.5;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    max-width: 250px;
    text-align: left;
    border-radius: 6px;
    letter-spacing: 0.1px;
}

.tooltip .tooltip-arrow::before {
    border-top-color: #1E293B;
}

/* Animation for tooltip */
.tooltip.fade {
    transition: opacity 0.15s linear;
}

.tooltip.show {
    animation: tooltipFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes tooltipFadeIn {
    0% {
        opacity: 0;
        transform: translateY(4px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.loading-overlay.active {
    display: flex;
}

.error-message {
    display: none;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    border-radius: 0.375rem;
    background-color: rgba(239, 68, 68, 0.1);
    color: rgb(239, 68, 68);
    font-size: 0.875rem;
}

.error-message.active {
    display: block;
}

:root {
    --primary-color-rgb: 139, 92, 246;    /* #8B5CF6 */
    --info-color-rgb: 59, 130, 246;       /* #3B82F6 */
    --success-color-rgb: 16, 185, 129;    /* #10B981 */
    --warning-color-rgb: 245, 158, 11;    /* #F59E0B */
    --danger-color-rgb: 239, 68, 68;      /* #EF4444 */
}

.chart-container {
    position: relative;
    height: calc(100% - 2rem); /* Subtract padding */
    width: 100%;
    min-height: 400px;
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

/* Business Insights Styles */
.list-group-flush .list-group-item {
    padding: 1rem;
    border-left: none;
    border-right: none;
    transition: all 0.2s;
}

.list-group-flush .list-group-item:hover {
    background-color: #F8FAFC;
}

.list-group-item:first-child {
    border-top: none;
}

.display-4 {
    font-size: 3rem;
    font-weight: 600;
    line-height: 1.2;
    color: var(--primary-color);
}

.progress {
    background-color: #E2E8F0;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Satisfaction colors */
.text-success {
    color: #10B981 !important;
}

.text-danger {
    color: #EF4444 !important;
}

.text-primary {
    color: #6366F1 !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid" id="dashboard-content">
    <!-- Last Updated Timestamp -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Dashboard Overview</h4>
        <div class="d-flex align-items-center">
            <small class="text-muted me-3" id="lastUpdated"></small>
            <button class="btn btn-sm btn-outline-primary" id="refreshDashboard">
                <i class="mdi mdi-refresh me-1"></i>
                <span>Refresh All</span>
            </button>
        </div>
    </div>

    <!-- Error Message -->
    <div class="error-message" id="dashboardError">
        An error occurred while updating the dashboard. Retrying...
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4" id="metricsCards">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="metricsLoading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-primary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-map text-primary" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Total number of zones available in the system">i</span>
                            <h3 class="mb-1" data-metric="totalZones">{{ $totalZones }}</h3>
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
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Number of drivers currently active and available for deliveries">i</span>
                            <h3 class="mb-1" data-metric="activeDrivers">{{ $activeDrivers }}</h3>
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
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Total number of registered delivery locations in the system">i</span>
                            <h3 class="mb-1" data-metric="totalLocations">{{ $totalLocations }}</h3>
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
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Total amount collected from deliveries today">i</span>
                            <h3 class="mb-1" data-metric="todayCollections">₵{{ number_format($todayCollections, 2) }}</h3>
                            <p class="text-muted mb-0">Today's Collections</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New KPI Cards -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-info bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-clock-alert text-info" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Number of deliveries that are scheduled but not yet completed">i</span>
                            <h3 class="mb-1" data-metric="pendingDeliveries">{{ $pendingDeliveries ?? 0 }}</h3>
                            <p class="text-muted mb-0">Pending Deliveries</p>
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
                            <span class="rounded-circle bg-danger bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-timer-alert text-danger" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Number of deliveries that have exceeded their scheduled delivery time">i</span>
                            <h3 class="mb-1" data-metric="overdueDeliveries">{{ $overdueDeliveries ?? 0 }}</h3>
                            <p class="text-muted mb-0">Overdue Deliveries</p>
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
                                <i class="mdi mdi-cash-multiple text-success" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Total revenue generated from all completed deliveries">i</span>
                            <h3 class="mb-1" data-metric="totalRevenue">₵{{ number_format($totalRevenue ?? 0, 2) }}</h3>
                            <p class="text-muted mb-0">Total Revenue</p>
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
                            <span class="rounded-circle bg-primary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-chart-areaspline text-primary" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Average revenue earned per completed delivery">i</span>
                            <h3 class="mb-1" data-metric="averageRevenuePerDelivery">₵{{ number_format($averageRevenuePerDelivery ?? 0, 2) }}</h3>
                            <p class="text-muted mb-0">Avg. Revenue/Delivery</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Business KPI Cards -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-success bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-account-group text-success" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Percentage of active drivers currently engaged in deliveries">i</span>
                            <h3 class="mb-1" data-metric="driver_utilization_rate">{{ $performanceMetrics['driver_utilization_rate'] ?? 0 }}%</h3>
                            <p class="text-muted mb-0">Driver Utilization</p>
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
                            <span class="rounded-circle bg-danger bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-clock-alert text-danger" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Percentage of deliveries that experienced delays">i</span>
                            <h3 class="mb-1" data-metric="delivery_delay_rate">{{ $performanceMetrics['delivery_delay_rate'] ?? 0 }}%</h3>
                            <p class="text-muted mb-0">Delay Rate</p>
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
                                <i class="mdi mdi-trending-up text-info" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Percentage change in revenue compared to previous period">i</span>
                            <h3 class="mb-1" data-metric="revenue_growth_rate">
                                <span class="{{ ($performanceMetrics['revenue_growth_rate'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($performanceMetrics['revenue_growth_rate'] ?? 0) >= 0 ? '+' : '' }}{{ $performanceMetrics['revenue_growth_rate'] ?? 0 }}%
                                </span>
                            </h3>
                            <p class="text-muted mb-0">Revenue Growth</p>
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
                                <i class="mdi mdi-speedometer text-warning" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Average revenue generated per minute of delivery time">i</span>
                            <h3 class="mb-1" data-metric="efficiency_index">₵{{ number_format($performanceMetrics['efficiency_index'] ?? 0, 2) }}</h3>
                            <p class="text-muted mb-0">Revenue/Minute</p>
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
                            <span class="rounded-circle bg-primary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-chart-bar text-primary" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 position-relative">
                            <span class="tooltip-icon position-absolute top-0 end-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Percentage change in delivery volume compared to previous period">i</span>
                            <h3 class="mb-1" data-metric="delivery_volume_growth">
                                <span class="{{ ($performanceMetrics['delivery_volume_trend']['growth'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($performanceMetrics['delivery_volume_trend']['growth'] ?? 0) >= 0 ? '+' : '' }}{{ $performanceMetrics['delivery_volume_trend']['growth'] ?? 0 }}%
                                </span>
                            </h3>
                            <p class="text-muted mb-0">Volume Growth</p>
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
                    <div class="d-flex align-items-center gap-2">
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
                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" id="refreshChart">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Visual representation of delivery performance and collections over time">i</span>
                    </div>
                </div>
                <div class="card-body p-0 h-100">
                    <div class="chart-container p-3">
                        <canvas id="deliveryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Performance Metrics</h5>
                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Key performance indicators showing delivery success, timing, and efficiency">i</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="text-muted mb-0">Delivery Success Rate</h6>
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Percentage of deliveries successfully completed out of total deliveries">i</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $performanceMetrics['delivery_success_rate'] }}%"></div>
                        </div>
                        <p class="mt-2 mb-0">{{ $performanceMetrics['delivery_success_rate'] }}% Success Rate</p>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="text-muted mb-0">On-Time Delivery Rate</h6>
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Percentage of deliveries completed within scheduled time">i</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $performanceMetrics['on_time_delivery_rate'] }}%"></div>
                        </div>
                        <p class="mt-2 mb-0">{{ $performanceMetrics['on_time_delivery_rate'] }}% On-Time Rate</p>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="text-muted mb-0">Average Delivery Time</h6>
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Average time taken to complete a delivery from pickup to drop-off">i</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="display-6 me-2">{{ round($performanceMetrics['average_delivery_time']) }}</span>
                            <span class="text-muted">minutes</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="text-muted mb-0">Efficiency Index</h6>
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Revenue generated per minute of delivery time">i</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="display-6 me-2">₵{{ number_format($performanceMetrics['efficiency_index'], 2) }}</span>
                            <span class="text-muted">/min</span>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="text-muted mb-0">Weekly Trend</h6>
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Week-over-week comparison of delivery performance with percentage change">i</span>
                        </div>
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

    <!-- Business Insights Section -->
    <div class="row g-4 mb-4">
        <!-- Top Performing Zones -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Top Performing Zones</h5>
                    <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Zones with highest delivery volumes and revenue">i</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach(($topZones ?? []) as $zone)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $zone['name'] ?? 'Zone Name' }}</h6>
                                    <small class="text-muted">{{ $zone['deliveries'] ?? 0 }} deliveries</small>
                                </div>
                                <div class="text-end">
                                    <div class="text-primary">₵{{ number_format($zone['revenue'] ?? 0, 2) }}</div>
                                    <small class="text-{{ ($zone['growth'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                        {{ ($zone['growth'] ?? 0) >= 0 ? '+' : '' }}{{ $zone['growth'] ?? 0 }}%
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Satisfaction -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Customer Satisfaction</h5>
                    <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Customer feedback and satisfaction metrics">i</span>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="display-4 mb-2">{{ number_format($customerSatisfaction['rating'] ?? 0, 1) }}</div>
                        <div class="text-muted">Average Rating</div>
                    </div>
                    <div class="d-flex justify-content-around text-center">
                        <div>
                            <div class="h5 mb-0" data-satisfaction="positive">{{ $customerSatisfaction['positive'] ?? 0 }}%</div>
                            <small class="text-success">Positive</small>
                        </div>
                        <div>
                            <div class="h5 mb-0" data-satisfaction="neutral">{{ $customerSatisfaction['neutral'] ?? 0 }}%</div>
                            <small class="text-muted">Neutral</small>
                        </div>
                        <div>
                            <div class="h5 mb-0" data-satisfaction="negative">{{ $customerSatisfaction['negative'] ?? 0 }}%</div>
                            <small class="text-danger">Negative</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Health -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Business Health</h5>
                    <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Key business health indicators and trends">i</span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted">Customer Retention</div>
                            <div class="h6 mb-0" data-value="retention_rate">{{ $businessHealth['retention_rate'] ?? 0 }}%</div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" data-progress="retention_rate" style="width: {{ $businessHealth['retention_rate'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted">Driver Availability</div>
                            <div class="h6 mb-0" data-value="driver_availability">{{ $businessHealth['driver_availability'] ?? 0 }}%</div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" data-progress="driver_availability" style="width: {{ $businessHealth['driver_availability'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted">System Uptime</div>
                            <div class="h6 mb-0" data-value="system_uptime">{{ $businessHealth['system_uptime'] ?? 0 }}%</div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar" data-progress="system_uptime" style="width: {{ $businessHealth['system_uptime'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables and initialization
let currentChart = null;
let refreshTimer = null;
const REFRESH_INTERVAL = 30000; // 30 seconds

// Initialize chart data structure
window.chartData = {
    deliveries: {
        labels: {!! json_encode($deliveryChart['labels']) !!},
        datasets: [{
            label: 'Completed Deliveries',
            data: {!! json_encode($deliveryChart['completed']) !!},
            borderColor: 'rgb(139, 92, 246)',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'white',
            pointBorderColor: 'rgb(139, 92, 246)',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderJoinStyle: 'round',
            spanGaps: true,
            segment: {
                borderColor: ctx => ctx.p0.parsed.y === 0 && ctx.p1.parsed.y === 0 ? 'transparent' : undefined
            }
        }, {
            label: 'Total Deliveries',
            data: {!! json_encode($deliveryChart['total']) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'white',
            pointBorderColor: 'rgb(59, 130, 246)',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderJoinStyle: 'round',
            spanGaps: true,
            segment: {
                borderColor: ctx => ctx.p0.parsed.y === 0 && ctx.p1.parsed.y === 0 ? 'transparent' : undefined
            }
        }]
    },
    collections: {
        labels: {!! json_encode($deliveryChart['labels']) !!},
        datasets: [{
            label: 'Collections (₵)',
            data: {!! json_encode($deliveryChart['collections']) !!},
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'white',
            pointBorderColor: 'rgb(16, 185, 129)',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderJoinStyle: 'round',
            spanGaps: true,
            segment: {
                borderColor: ctx => ctx.p0.parsed.y === 0 && ctx.p1.parsed.y === 0 ? 'transparent' : undefined
            }
        }]
    }
};

function updateLastUpdated(timestamp) {
    const lastUpdated = document.getElementById('lastUpdated');
    const date = new Date(timestamp);
    lastUpdated.textContent = `Last updated: ${date.toLocaleTimeString()}`;
}

function showLoading(show = true) {
    const refreshBtn = document.getElementById('refreshDashboard');
    const icon = refreshBtn.querySelector('.mdi-refresh');
    const loadingOverlay = document.getElementById('metricsLoading');
    const errorMessage = document.getElementById('dashboardError');
    
    refreshBtn.disabled = show;
    if (show) {
        icon.classList.add('mdi-spin');
        loadingOverlay.classList.add('active');
        errorMessage.classList.remove('active');
    } else {
        icon.classList.remove('mdi-spin');
        loadingOverlay.classList.remove('active');
    }
}

function showError(show = true) {
    const errorMessage = document.getElementById('dashboardError');
    errorMessage.classList.toggle('active', show);
}

function animateValue(element, start, end, duration = 500) {
    if (start === end) return;
    const range = end - start;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const value = Math.floor(start + (range * progress));
        if (typeof end === 'number') {
            element.textContent = value.toLocaleString();
        } else {
            element.textContent = end;
        }
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Update functions
function updateBasicMetrics(metrics) {
    try {
        Object.keys(metrics).forEach(key => {
            const element = document.querySelector(`[data-metric="${key}"]`);
            if (element) {
                const currentValue = parseFloat(element.textContent.replace(/[^0-9.-]+/g, '')) || 0;
                const newValue = metrics[key];
                
                if (key.includes('revenue') || key === 'todayCollections') {
                    element.textContent = `₵${newValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                } else {
                    animateValue(element, currentValue, newValue);
                }
            }
        });
    } catch (error) {
        console.error('Error updating basic metrics:', error);
    }
}

function updatePerformanceMetrics(performance) {
    try {
        // Update success rate progress bar
        if (performance.delivery_success_rate !== undefined) {
            const rateElement = document.querySelector('.progress-bar');
            if (rateElement) {
                rateElement.style.width = `${performance.delivery_success_rate}%`;
                document.querySelector('.progress + p').textContent = 
                    `${performance.delivery_success_rate}% Success Rate`;
            }
        }

        // Update new KPIs
        if (performance.driver_utilization_rate !== undefined) {
            updateMetricValue('driver_utilization_rate', performance.driver_utilization_rate, '%');
        }

        if (performance.delivery_delay_rate !== undefined) {
            updateMetricValue('delivery_delay_rate', performance.delivery_delay_rate, '%');
        }

        if (performance.revenue_growth_rate !== undefined) {
            const element = document.querySelector('[data-metric="revenue_growth_rate"] span');
            if (element) {
                const value = performance.revenue_growth_rate;
                element.className = value >= 0 ? 'text-success' : 'text-danger';
                element.textContent = `${value >= 0 ? '+' : ''}${value}%`;
            }
        }

        if (performance.efficiency_index !== undefined) {
            updateMetricValue('efficiency_index', performance.efficiency_index, '', '₵');
        }

        if (performance.delivery_volume_trend !== undefined) {
            const element = document.querySelector('[data-metric="delivery_volume_growth"] span');
            if (element) {
                const growth = performance.delivery_volume_trend.growth;
                element.className = growth >= 0 ? 'text-success' : 'text-danger';
                element.textContent = `${growth >= 0 ? '+' : ''}${growth}%`;
            }
        }
    } catch (error) {
        console.error('Error updating performance metrics:', error);
    }
}

function updateMetrics(data) {
    try {
        // Update basic metrics
        updateBasicMetrics(data.basicMetrics);
        
        // Update performance metrics
        updatePerformanceMetrics(data.performanceMetrics);
        
        // Update chart data
        updateChartData(data);

        // Update business insights
        if (data.businessInsights) {
            // Update top zones
            if (data.businessInsights.topZones) {
                const topZonesContainer = document.querySelector('.list-group-flush');
                if (topZonesContainer) {
                    topZonesContainer.innerHTML = data.businessInsights.topZones.map(zone => `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${zone.name || 'Zone Name'}</h6>
                                    <small class="text-muted">${zone.deliveries || 0} deliveries</small>
                                </div>
                                <div class="text-end">
                                    <div class="text-primary">₵${(zone.revenue || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
                                    <small class="text-${(zone.growth || 0) >= 0 ? 'success' : 'danger'}">
                                        ${(zone.growth || 0) >= 0 ? '+' : ''}${zone.growth || 0}%
                                    </small>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            }

            // Update customer satisfaction
            if (data.businessInsights.customerSatisfaction) {
                const cs = data.businessInsights.customerSatisfaction;
                document.querySelector('.display-4').textContent = (cs.rating || 0).toFixed(1);
                document.querySelector('[data-satisfaction="positive"]').textContent = `${cs.positive || 0}%`;
                document.querySelector('[data-satisfaction="neutral"]').textContent = `${cs.neutral || 0}%`;
                document.querySelector('[data-satisfaction="negative"]').textContent = `${cs.negative || 0}%`;
            }

            // Update business health
            if (data.businessInsights.businessHealth) {
                const bh = data.businessInsights.businessHealth;
                updateProgressBar('retention_rate', bh.retention_rate);
                updateProgressBar('driver_availability', bh.driver_availability);
                updateProgressBar('system_uptime', bh.system_uptime);
            }
        }
    } catch (error) {
        console.error('Error updating metrics:', error);
    }
}

// Helper function to update progress bars
function updateProgressBar(metric, value) {
    const progressBar = document.querySelector(`[data-progress="${metric}"]`);
    const valueDisplay = document.querySelector(`[data-value="${metric}"]`);
    if (progressBar && valueDisplay) {
        progressBar.style.width = `${value || 0}%`;
        valueDisplay.textContent = `${value || 0}%`;
    }
}

// Helper function to update metric values with proper formatting
function updateMetricValue(metric, value, suffix = '', prefix = '') {
    try {
        const element = document.querySelector(`[data-metric="${metric}"]`);
        if (element) {
            const currentValue = parseFloat(element.textContent.replace(/[^0-9.-]+/g, '')) || 0;
            if (typeof value === 'number' && !isNaN(value)) {
                if (prefix || suffix) {
                    element.textContent = `${prefix}${value.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}${suffix}`;
                } else {
                    animateValue(element, currentValue, value);
                }
            }
        }
    } catch (error) {
        console.error('Error updating metric value:', error);
    }
}

    // Update chart data
    function updateChartData(data) {
        try {
            if (currentChart && data.deliveryChart) {
                const activeView = document.querySelector('[data-view].active')?.dataset.view;
                if (activeView) {
                    // Update data while preserving chart configuration
                    currentChart.data.labels = data.deliveryChart.labels || [];
                    if (activeView === 'deliveries') {
                        currentChart.data.datasets[0].data = data.deliveryChart.completed || [];
                        currentChart.data.datasets[1].data = data.deliveryChart.total || [];
                    } else {
                        currentChart.data.datasets[0].data = data.deliveryChart.collections || [];
                    }
                    currentChart.update('active');
                }
            }
        } catch (error) {
            console.error('Error updating chart data:', error);
            // If update fails, reinitialize the chart
            if (currentChart) {
                currentChart.destroy();
                const activeView = document.querySelector('[data-view].active')?.dataset.view;
                if (activeView) {
                    initChart(activeView);
                }
            }
        }
    }

    // Update metrics and data
    function updateMetrics(data) {
        try {
            // Update basic metrics
            updateBasicMetrics(data.basicMetrics);
            
            // Update performance metrics
            updatePerformanceMetrics(data.performanceMetrics);
            
            // Update chart data
            updateChartData(data);
        } catch (error) {
            console.error('Error updating metrics:', error);
        }
    }

function refreshDashboard() {
    showLoading(true);
    
    fetch('/admin/dashboard/metrics')
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                updateMetrics(response.data);
                updateLastUpdated(response.data.last_updated);
            } else {
                console.error('Failed to fetch metrics:', response.message);
                showError(true);
            }
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
            showError(true);
        })
        .finally(() => {
            showLoading(false);
        });
}

// Remove duplicate declaration since it's already declared at the top

function initChart(view) {
    try {
        const ctx = document.getElementById('deliveryChart')?.getContext('2d');
        if (!ctx) {
            console.error('Chart context not found');
            return;
        }

        if (currentChart) {
            currentChart.destroy();
        }

        currentChart = new Chart(ctx, {
            type: 'line',
            data: window.chartData[view],
            options: {
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart'
                },
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                layout: {
                    padding: {
                        top: 20,
                        right: 20,
                        bottom: 10,
                        left: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (view === 'collections') {
                                    label += '₵' + context.parsed.y.toLocaleString();
                                } else {
                                    label += context.parsed.y.toLocaleString();
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#64748b',
                            padding: 8,
                            callback: function(value) {
                                if (view === 'collections') {
                                    return '₵' + value.toLocaleString();
                                }
                                return value.toLocaleString();
                            }
                        },
                        min: 0,
                        suggestedMax: function(context) {
                            const values = context.chart.data.datasets.reduce((acc, dataset) => {
                                return acc.concat(dataset.data);
                            }, []);
                            const max = Math.max(...values);
                            return max + (max * 0.1); // Add 10% padding
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.4,
                        borderWidth: 2,
                        fill: true,
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) {
                                return null;
                            }
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            if (context.datasetIndex === 0) {
                                gradient.addColorStop(0, 'rgba(139, 92, 246, 0.2)');
                                gradient.addColorStop(1, 'rgba(139, 92, 246, 0)');
                            } else if (context.datasetIndex === 1) {
                                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
                                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
                            } else {
                                gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
                                gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
                            }
                            return gradient;
                        }
                    },
                    point: {
                        radius: 3,
                        hoverRadius: 5,
                        backgroundColor: 'white',
                        borderWidth: 2
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: false
                }
            }
        });
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
}

// Handle page visibility changes
function handleVisibilityChange() {
    if (document.hidden) {
        // Clear the refresh interval when page is not visible
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    } else {
        // Refresh immediately and restart the interval when page becomes visible
        if (!refreshTimer) {
            refreshDashboard();
            refreshTimer = setInterval(refreshDashboard, REFRESH_INTERVAL);
        }
    }
}

// Cleanup function
function cleanup() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
    }
    document.removeEventListener('visibilitychange', handleVisibilityChange);
}

document.addEventListener('DOMContentLoaded', function() {
    try {
    // Initialize tooltips with enhanced configuration
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover focus',
            animation: true,
            delay: { show: 100, hide: 200 },
            offset: [0, 8],
            placement: 'top',
            popperConfig: function(defaultBsPopperConfig) {
                return {
                    ...defaultBsPopperConfig,
                    modifiers: [
                        ...defaultBsPopperConfig.modifiers,
                        {
                            name: 'computeStyles',
                            options: {
                                gpuAcceleration: true,
                            },
                        }
                    ],
                }
            }
        });
    });

        // Initialize chart data object if not exists
        window.chartData = window.chartData || {
            deliveries: {
                labels: [],
                datasets: []
            },
            collections: {
                labels: [],
                datasets: []
            }
        };

        // Initialize chart
        initChart('deliveries');
        
        // Initial data load
        refreshDashboard();
        
        // Set up auto-refresh
        refreshTimer = setInterval(refreshDashboard, REFRESH_INTERVAL);
        
        // Set up visibility change handler
        document.addEventListener('visibilitychange', handleVisibilityChange);
        
        // Clean up on page unload
        window.addEventListener('unload', cleanup);
        
        // Manual refresh button
        document.getElementById('refreshDashboard')?.addEventListener('click', () => {
            try {
                // Clear existing timer
                if (refreshTimer) {
                    clearInterval(refreshTimer);
                }
                
                // Refresh immediately
                refreshDashboard();
                
                // Reset the timer
                refreshTimer = setInterval(refreshDashboard, REFRESH_INTERVAL);
            } catch (error) {
                console.error('Error in refresh button handler:', error);
            }
        });
        
        // Initialize view toggle buttons
        document.querySelectorAll('[data-view]').forEach(button => {
            button.addEventListener('click', function() {
                try {
                    document.querySelectorAll('[data-view]').forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    initChart(this.dataset.view);
                } catch (error) {
                    console.error('Error in view toggle handler:', error);
                }
            });
        });
        
        // Refresh chart button
        document.getElementById('refreshChart')?.addEventListener('click', async function() {
            const button = this;
            const icon = button.querySelector('.mdi-refresh');
            
            try {
                // Show loading state
                button.disabled = true;
                icon.classList.add('mdi-spin');
                
                const response = await fetch('/admin/dashboard/metrics');
                const data = await response.json();
                
                if (data.success && data.data.deliveryChart) {
                    const activeView = document.querySelector('[data-view].active')?.dataset.view;
                    if (activeView) {
                        // Update chart data
                        // Update data while preserving styling
                        if (activeView === 'deliveries') {
                            currentChart.data.datasets[0].data = data.data.deliveryChart.completed;
                            currentChart.data.datasets[1].data = data.data.deliveryChart.total;
                        } else {
                            currentChart.data.datasets[0].data = data.data.deliveryChart.collections;
                        }
                        currentChart.data.labels = data.data.deliveryChart.labels;
                        currentChart.update('active');
                    }
                }
            } catch (error) {
                console.error('Error refreshing chart:', error);
            } finally {
                // Reset loading state
                button.disabled = false;
                icon.classList.remove('mdi-spin');
            }
        });
        
    } catch (error) {
        console.error('Error in DOMContentLoaded:', error);
    }
});
</script>
@endpush
