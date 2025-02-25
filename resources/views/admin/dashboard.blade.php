@extends('layouts.admin')

@push('styles')
<style>
    :root {
        --primary-color-rgb: 139, 92, 246;  /* #8B5CF6 in RGB */
        --success-color-rgb: 16, 185, 129;  /* #10B981 in RGB */
        --warning-color-rgb: 245, 158, 11;  /* #F59E0B in RGB */
        --danger-color-rgb: 239, 68, 68;    /* #EF4444 in RGB */
        --info-color-rgb: 59, 130, 246;     /* #3B82F6 in RGB */
    }

    /* Loading Overlay */
    #loading-overlay {
        transition: opacity 0.3s ease-in-out;
        backdrop-filter: blur(2px);
    }

    /* Alerts */
    .alert {
        transition: all 0.3s ease-in-out;
        margin-bottom: 1rem;
    }

    .alert.fade {
        opacity: 0;
        transform: translateY(-10px);
    }

    .alert.show {
        opacity: 1;
        transform: translateY(0);
    }

    /* Tables */
    .table tbody {
        transition: opacity 0.2s ease-in-out;
    }

    .table tr {
        transition: background-color 0.2s ease-in-out;
    }

    .table tr:hover {
        background-color: rgba(var(--primary-color-rgb), 0.05);
    }

    /* Buttons */
    .btn-link {
        text-decoration: none;
        transition: color 0.2s ease-in-out;
    }

    .btn-link:hover {
        text-decoration: underline;
    }

    .btn-group .btn {
        transition: all 0.2s ease-in-out;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
    }

    /* Loading Indicators */
    .spinner-border {
        transition: opacity 0.2s ease-in-out;
    }

    .mdi-spin {
        animation: spin 1s linear infinite;
    }

    /* Cards */
    .card {
        transition: all 0.3s ease-in-out;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: rgba(var(--primary-color-rgb), 0.2);
    }

    /* Progress Bars */
    .progress {
        transition: all 0.3s ease-in-out;
        background-color: rgba(var(--primary-color-rgb), 0.1);
    }

    .progress-bar {
        transition: width 0.6s ease-in-out;
    }

    /* Badges */
    .badge {
        transition: all 0.2s ease-in-out;
    }

    /* Animations */
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Chart Components */
    .chart-container {
        position: relative;
        transition: all 0.3s ease-in-out;
        min-height: 300px;
    }

    .chart-container:hover {
        transform: scale(1.01);
    }

    .chart-error {
        transition: opacity 0.3s ease-in-out;
        border-radius: 8px;
        margin: 8px;
        animation: fadeInDown 0.3s ease-out;
        background-color: rgba(var(--danger-color-rgb), 0.1);
    }

    .chart-loader {
        transition: opacity 0.3s ease-in-out;
        backdrop-filter: blur(2px);
        background-color: rgba(255, 255, 255, 0.9);
    }

    /* Interactive Elements */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease-in-out;
    }

    .btn-icon:hover {
        transform: rotate(45deg);
    }

    .btn-icon i {
        font-size: 18px;
    }

    .btn-group .btn {
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease-in-out;
    }

    .btn-group .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: currentColor;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }

    .btn-group .btn:hover::before {
        opacity: 0.1;
    }

    .btn-group .btn.active::before {
        opacity: 0.15;
    }

    /* Animations */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .btn-group .btn {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }

        .btn-icon {
            width: 28px;
            height: 28px;
        }

        .btn-icon i {
            font-size: 16px;
        }

        .chart-container {
            min-height: 250px;
            padding: 0.5rem !important;
        }

        .card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch !important;
            padding: 1rem;
        }

        .card-header .d-flex {
            justify-content: center;
        }

        .card-title {
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .btn-group {
            width: 100%;
            display: flex;
        }

        .btn-group .btn {
            flex: 1;
        }

        .progress {
            height: 8px;
        }
    }

    /* Dark Mode Support (if needed in future) */
    @media (prefers-color-scheme: dark) {
        .chart-loader {
            background-color: rgba(0, 0, 0, 0.75) !important;
        }

        .chart-error {
            background-color: rgba(var(--danger-color-rgb), 0.15) !important;
        }

        .btn-outline-primary {
            border-color: rgba(var(--primary-color-rgb), 0.5);
        }

        .btn-outline-secondary {
            border-color: rgba(255, 255, 255, 0.1);
        }
    }

    /* Print Styles */
    @media print {
        .btn-group, .btn-icon {
            display: none !important;
        }

        .chart-container {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }

    /* Keyboard Shortcuts Modal */
    .modal-backdrop {
        backdrop-filter: blur(2px);
    }

    .modal-content {
        border: none;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        max-width: 500px;
        margin: 0 auto;
    }

    .modal-header {
        border-bottom: 1px solid rgba(var(--primary-color-rgb), 0.1);
        padding: 1.25rem 1.5rem;
    }

    .modal-header .modal-title {
        font-size: 1.125rem;
        color: rgba(var(--primary-color-rgb), 1);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid rgba(var(--primary-color-rgb), 0.1);
        padding: 1.25rem 1.5rem;
    }

    .modal h6 {
        color: var(--secondary-color);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modal .alert-info {
        background-color: rgba(var(--info-color-rgb), 0.1);
        border: none;
        border-radius: 8px;
        color: inherit;
    }

    .list-group-item {
        border: none;
        padding: 0.75rem 0;
    }

    .badge {
        min-width: 48px;
        padding: 0.35rem 0.5rem;
        font-family: 'Inter', monospace;
        font-size: 0.875rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .modal .btn-primary {
        min-width: 100px;
        padding: 0.5rem 1.25rem;
        font-weight: 500;
    }

    /* Focus Styles */
    .modal :focus-visible {
        outline: 2px solid rgba(var(--primary-color-rgb), 0.5);
        outline-offset: 2px;
        border-radius: 4px;
    }

    /* Modal Animations */
    @media (prefers-reduced-motion: no-preference) {
        .modal.fade .modal-dialog {
            transform: scale(0.95) translateY(-10px);
            transition: transform 0.2s ease-out, opacity 0.2s ease-out;
            opacity: 0;
        }

        .modal.show .modal-dialog {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .modal .alert {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    }

    /* Accessibility Improvements */
    .modal-dialog {
        outline: none;
    }

    .modal-content:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.5);
    }

    .modal .badge {
        user-select: none;
    }

    /* Ensure contrast in dark mode */
    @media (prefers-color-scheme: dark) {
        .modal-content {
            background-color: #1a1a1a;
            color: #ffffff;
        }

        .modal .badge.bg-light {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
        }

        .modal .alert-info {
            background-color: rgba(var(--info-color-rgb), 0.15);
            color: #ffffff;
        }

        .modal .text-muted {
            color: #a0a0a0 !important;
        }

        .modal-header, .modal-footer {
            border-color: rgba(255, 255, 255, 0.1);
        }
    }

    /* High Contrast Mode Support */
    @media (forced-colors: active) {
        .badge {
            border: 1px solid CanvasText;
        }
        
        .modal-content {
            border: 1px solid CanvasText;
        }
    }

    /* Motion Sensitivity Support */
    @media (prefers-reduced-motion: reduce) {
        .modal.fade .modal-dialog {
            transition: none;
        }

        .btn-icon:hover {
            transform: none;
        }

        .chart-container:hover {
            transform: none;
        }
    }

    /* Keyboard Shortcuts Button */
    .btn-keyboard-shortcuts {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        z-index: 1;
        opacity: 0.7;
        transition: opacity 0.2s ease-in-out;
    }

    .chart-container:hover .btn-keyboard-shortcuts,
    .btn-keyboard-shortcuts:focus {
        opacity: 1;
    }

    .btn-keyboard-shortcuts .mdi {
        font-size: 1.1rem;
    }

    @media (max-width: 576px) {
        .btn-keyboard-shortcuts {
            position: static;
            margin-bottom: 0.5rem;
            width: 100%;
            opacity: 1;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid" id="dashboard-content">
    <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 1050;">
        <x-loading-spinner size="48px" />
    </div>

    <!-- Error Alert Container -->
    <div id="error-container"></div>
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
                            <button type="button" class="btn btn-sm btn-outline-primary active" onclick="updateChartView('deliveries')" aria-pressed="true">
                                <i class="mdi mdi-truck-delivery me-1" aria-hidden="true"></i>
                                <span>Deliveries</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateChartView('collections')" aria-pressed="false">
                                <i class="mdi mdi-cash me-1" aria-hidden="true"></i>
                                <span>Collections</span>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary ms-2" onclick="refreshChart()" aria-label="Refresh chart data">
                            <i class="mdi mdi-refresh" aria-hidden="true"></i>
                            <span class="visually-hidden">Refresh</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="chart-container p-4" role="region" aria-label="Delivery Performance Chart">
                        <canvas id="deliveryChart" height="300" aria-label="Line chart showing delivery performance" role="img"></canvas>
                        <div class="chart-loader position-absolute top-0 start-0 w-100 h-100 d-none bg-white bg-opacity-75" style="z-index: 1">
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading chart data...</span>
                                </div>
                            </div>
                        </div>
                        <!-- Hidden descriptions for screen readers -->
                        <div class="visually-hidden" aria-live="polite" id="chartDescription">
                            This chart displays delivery performance metrics over time. Use the buttons above to switch between delivery counts and collection amounts.
                        </div>
                        <div class="visually-hidden" id="chartKeyboardHelp">
                            Keyboard shortcuts: 
                            Use Left and Right arrow keys to move between data points.
                            Use Up and Down arrow keys to switch between data series.
                            Use Home and End keys to jump to the first or last data point.
                            Press Tab to focus on the chart, then use arrow keys to explore data.
                        </div>
                        <!-- Keyboard help button -->
                        <button type="button" 
                                class="btn btn-sm btn-link btn-keyboard-shortcuts" 
                                onclick="showKeyboardHelp()"
                                aria-label="Show keyboard shortcuts"
                                aria-haspopup="dialog"
                                data-bs-toggle="tooltip"
                                data-bs-placement="left"
                                title="Press ? for keyboard shortcuts">
                            <i class="mdi mdi-keyboard me-1" aria-hidden="true"></i>
                            <span class="d-none d-sm-inline">Keyboard Shortcuts</span>
                            <span class="badge bg-light text-dark ms-1 d-none d-sm-inline">?</span>
                        </button>
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

    <!-- Zone Performance -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Zone Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Zone</th>
                                    <th>Deliveries</th>
                                    <th>Collections</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($performanceMetrics['collections_by_zone'] as $zone)
                                    <tr>
                                        <td>{{ $zone['name'] }}</td>
                                        <td>{{ $zone['locations_count'] }}</td>
                                        <td>₵{{ number_format($zone['total_collections'], 2) }}</td>
                                        <td>
                                            <div class="progress" style="height: 5px; width: 100px;">
                                                @php
                                                    $performance = $zone['locations_count'] > 0
                                                        ? ($zone['total_collections'] / $zone['locations_count']) * 100
                                                        : 0;
                                                @endphp
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ min($performance, 100) }}%"></div>
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
    </div>

    <!-- Recent Activities -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Activities</h5>
            <button class="btn btn-sm btn-outline-primary" onclick="refreshActivities()">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingOverlay = document.getElementById('loading-overlay');
    const dashboardContent = document.getElementById('dashboard-content');
    
    // Initialize click handlers
    const viewButtons = document.querySelectorAll('.btn-group .btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            viewButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            const view = button.textContent.toLowerCase().includes('deliveries') ? 'deliveries' : 'collections';
            updateChartView(view);
        });
    });

    const refreshButton = document.querySelector('.btn-icon');
    if (refreshButton) {
        refreshButton.addEventListener('click', refreshChart);
    }

    const activitiesRefreshButton = document.querySelector('.btn-outline-primary');
    if (activitiesRefreshButton) {
        activitiesRefreshButton.addEventListener('click', refreshActivities);
    }

    // Initialize dashboard
    Promise.all([
        initializeChart('deliveries'),
        refreshActivities()
    ])
    .catch(error => {
        console.error('Dashboard initialization error:', error);
        // Show error message at the top of the dashboard
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show mb-4';
        errorAlert.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="mdi mdi-alert me-2"></i>
                <div>
                    <strong>Dashboard Error:</strong> Some components failed to load. Please try refreshing the page.
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        dashboardContent.insertBefore(errorAlert, dashboardContent.firstChild);
    })
    .finally(() => {
        // Hide loading overlay with fade effect
        loadingOverlay.style.opacity = '0';
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
        }, 300); // Match this with CSS transition duration
    });

    // Set up auto-refresh for activities
    let activityRefreshInterval = setInterval(refreshActivities, 60000); // Refresh every minute

    // Clear interval when page is hidden
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(activityRefreshInterval);
        } else {
            refreshActivities(); // Refresh immediately when page becomes visible
            activityRefreshInterval = setInterval(refreshActivities, 60000);
        }
    });
});

// Update initializeChart to return a promise

let currentChart = null;
const chartConfig = {
    deliveries: {
        labels: {!! json_encode($deliveryChart['labels']) !!},
        datasets: [{
            label: 'Completed Deliveries',
            data: {!! json_encode($deliveryChart['completed']) !!},
            borderColor: `rgba(var(--primary-color-rgb), 1)`,
            backgroundColor: `rgba(var(--primary-color-rgb), 0.1)`,
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: `rgba(var(--primary-color-rgb), 1)`,
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }, {
            label: 'Total Deliveries',
            data: {!! json_encode($deliveryChart['total']) !!},
            borderColor: `rgba(var(--info-color-rgb), 1)`,
            backgroundColor: `rgba(var(--info-color-rgb), 0.1)`,
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: `rgba(var(--info-color-rgb), 1)`,
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    collections: {
        labels: {!! json_encode($deliveryChart['labels']) !!},
        datasets: [{
            label: 'Collections (₵)',
            data: {!! json_encode($deliveryChart['collections']) !!},
            borderColor: `rgba(var(--success-color-rgb), 1)`,
            backgroundColor: `rgba(var(--success-color-rgb), 0.1)`,
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: `rgba(var(--success-color-rgb), 1)`,
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    }
};

function initializeChart(view) {
    return new Promise((resolve, reject) => {
        try {
            const canvas = document.getElementById('deliveryChart');
            const chartLoader = canvas.closest('.chart-container').querySelector('.chart-loader');
            
            if (!canvas) {
                throw new Error('Chart canvas not found');
            }

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error('Failed to get chart context');
            }
            
            // Show loader
            chartLoader.classList.remove('d-none');
            
            if (currentChart) {
                currentChart.destroy();
            }

            if (!chartConfig[view]) {
                throw new Error(`Invalid chart view: ${view}`);
            }

            // Generate chart description for screen readers
            const chartDescription = document.getElementById('chartDescription');
            const description = view === 'deliveries' 
                ? 'Line chart showing completed deliveries versus total deliveries over the past week'
                : 'Line chart showing collection amounts over the past week';
            chartDescription.textContent = description;

            currentChart = new Chart(ctx, {
                type: 'line',
                data: chartConfig[view],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 12
                                },
                                generateLabels: function(chart) {
                                    const labels = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                    // Add ARIA labels for better accessibility
                                    labels.forEach(label => {
                                        label.text = `${label.text} - Click to ${label.hidden ? 'show' : 'hide'} data`;
                                    });
                                    return labels;
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                family: "'Inter', sans-serif",
                                size: 13
                            },
                            bodyFont: {
                                family: "'Inter', sans-serif",
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
                                        label += '₵' + context.parsed.y.toFixed(2);
                                    } else {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11
                                },
                                callback: function(value) {
                                    if (view === 'collections') {
                                        return '₵' + value.toFixed(2);
                                    }
                                    return value;
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            title: {
                                display: true,
                                text: view === 'collections' ? 'Amount (₵)' : 'Number of Deliveries',
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11
                                }
                            },
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Day of Week',
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart',
                        onProgress: (animation) => {
                            // Update loading status for screen readers
                            if (animation.currentStep < animation.numSteps) {
                                announceToScreenReader('Chart is updating...');
                            }
                        },
                        onComplete: () => {
                            chartLoader.classList.add('d-none');
                            // Announce completion to screen readers
                            const total = view === 'deliveries' 
                                ? chartConfig[view].datasets[0].data.reduce((a, b) => a + b, 0)
                                : chartConfig[view].datasets[0].data.reduce((a, b) => a + b, 0).toFixed(2);
                            const message = view === 'deliveries'
                                ? `Chart updated. Showing ${total} total deliveries over the past week.`
                                : `Chart updated. Showing ₵${total} total collections over the past week.`;
                            announceToScreenReader(message);
                            resolve();
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                        axis: 'x'
                    },
                    elements: {
                        line: {
                            tension: 0.4
                        },
                        point: {
                            radius: 4,
                            hoverRadius: 6,
                            hitRadius: 8
                        }
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: false,
                        animationDuration: 150
                    }
                }
            });
        } catch (error) {
            console.error('Chart initialization error:', error);
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-danger mt-3';
            errorMessage.innerHTML = `
                <i class="mdi mdi-alert me-2"></i>
                Failed to load chart: ${error.message}
            `;
            canvas.parentNode.insertBefore(errorMessage, canvas.nextSibling);
            reject(error);
        }
    });
}

let currentView = 'deliveries';

function updateChartView(view) {
    const buttons = document.querySelectorAll('.btn-group .btn');
    const refreshBtn = document.querySelector('.btn-icon');
    
    if (!['deliveries', 'collections'].includes(view)) {
        console.error('Invalid view type:', view);
        return;
    }

    // Disable buttons during update
    buttons.forEach(btn => btn.disabled = true);
    refreshBtn.disabled = true;

    // Update chart
    initializeChart(view)
        .then(() => {
            // Update button states
            buttons.forEach(btn => {
                const isActive = btn.textContent.toLowerCase().includes(view);
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-pressed', isActive.toString());
            });
            
            // Store current view
            currentView = view;
            
            // Announce to screen readers
            announceToScreenReader(`Chart updated to show ${view}`);
        })
        .catch(error => {
            console.error('Chart update failed:', error);
            showChartError('Failed to update chart. Please try again.');
            announceToScreenReader('Chart update failed');
        })
        .finally(() => {
            // Re-enable buttons
            buttons.forEach(btn => btn.disabled = false);
            refreshBtn.disabled = false;
        });
}

// Add keyboard navigation for chart controls
document.addEventListener('DOMContentLoaded', function() {
    const chartControls = document.querySelector('.btn-group[role="group"]');
    const chartCanvas = document.getElementById('deliveryChart');

    // Initialize tooltips
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        .map(el => new bootstrap.Tooltip(el));

    // Global keyboard shortcut handler
    document.addEventListener('keydown', function(e) {
        // Only handle '?' key when not in an input/textarea
        if (e.key === '?' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            showKeyboardHelp();
        }
    });

    // Cleanup tooltips on page unload
    window.addEventListener('beforeunload', () => {
        tooltips.forEach(tooltip => tooltip.dispose());
    });
    
    if (chartControls) {
        // Handle button group keyboard navigation
        chartControls.addEventListener('keydown', function(e) {
            const buttons = Array.from(this.querySelectorAll('.btn'));
            const currentIndex = buttons.findIndex(btn => btn === document.activeElement);
            
            switch (e.key) {
                case 'ArrowLeft':
                case 'ArrowUp':
                    e.preventDefault();
                    if (currentIndex > 0) {
                        buttons[currentIndex - 1].focus();
                    } else {
                        buttons[buttons.length - 1].focus();
                    }
                    break;
                    
                case 'ArrowRight':
                case 'ArrowDown':
                    e.preventDefault();
                    if (currentIndex < buttons.length - 1) {
                        buttons[currentIndex + 1].focus();
                    } else {
                        buttons[0].focus();
                    }
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    buttons[0].focus();
                    break;
                    
                case 'End':
                    e.preventDefault();
                    buttons[buttons.length - 1].focus();
                    break;
            }
        });
    }

    if (chartCanvas) {
        // Make chart canvas focusable
        chartCanvas.tabIndex = 0;
        chartCanvas.setAttribute('role', 'application');
        chartCanvas.setAttribute('aria-label', 'Interactive chart. Use arrow keys to navigate data points');

        // Handle chart keyboard navigation
        chartCanvas.addEventListener('keydown', function(e) {
            if (!currentChart) return;

            const activeElements = currentChart.getActiveElements();
            let datasetIndex = 0;
            let dataIndex = 0;

            if (activeElements.length) {
                datasetIndex = activeElements[0].datasetIndex;
                dataIndex = activeElements[0].index;
            }

            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    dataIndex = Math.max(0, dataIndex - 1);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    dataIndex = Math.min(currentChart.data.labels.length - 1, dataIndex + 1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    datasetIndex = Math.max(0, datasetIndex - 1);
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    datasetIndex = Math.min(currentChart.data.datasets.length - 1, datasetIndex + 1);
                    break;
                case 'Home':
                    e.preventDefault();
                    dataIndex = 0;
                    break;
                case 'End':
                    e.preventDefault();
                    dataIndex = currentChart.data.labels.length - 1;
                    break;
                default:
                    return;
            }

            // Update active elements
            const meta = currentChart.getDatasetMeta(datasetIndex);
            if (meta.hidden) return;

            currentChart.setActiveElements([{
                datasetIndex,
                index: dataIndex
            }]);

            // Trigger tooltip update
            currentChart.tooltip.setActiveElements([{
                datasetIndex,
                index: dataIndex
            }], {
                x: meta.data[dataIndex].x,
                y: meta.data[dataIndex].y
            });

            // Announce the data point to screen readers
            const dataset = currentChart.data.datasets[datasetIndex];
            const value = dataset.data[dataIndex];
            const label = currentChart.data.labels[dataIndex];
            const message = `${dataset.label}: ${currentView === 'collections' ? '₵' + value.toFixed(2) : value} on ${label}`;
            announceToScreenReader(message);

            currentChart.update('none');
        });

        // Clear active elements when focus is lost
        chartCanvas.addEventListener('blur', function() {
            if (currentChart) {
                currentChart.setActiveElements([]);
                currentChart.update('none');
            }
        });
    }
});

// Helper function to announce messages to screen readers
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('role', 'alert');
    announcement.setAttribute('aria-live', 'assertive');
    announcement.className = 'visually-hidden';
    announcement.textContent = message;
    document.body.appendChild(announcement);
    
    // Remove the element after announcement
    setTimeout(() => {
        announcement.remove();
    }, 3000);
}

function refreshChart() {
    const refreshBtn = document.querySelector('.btn-icon');
    const refreshIcon = refreshBtn.querySelector('.mdi-refresh');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    // Disable all buttons during refresh
    refreshBtn.disabled = true;
    buttons.forEach(btn => btn.disabled = true);
    
    // Add spinning animation
    refreshIcon.classList.add('mdi-spin');

    // Get current view from active button
    const currentView = Array.from(buttons).find(btn => btn.classList.contains('active'))
        ?.textContent.toLowerCase().includes('deliveries') ? 'deliveries' : 'collections';

    if (!currentView) {
        console.error('No active view found');
        return;
    }

    // Refresh chart
    initializeChart(currentView)
        .then(() => {
            announceToScreenReader(`Chart refreshed showing ${currentView}`);
        })
        .catch(error => {
            console.error('Chart refresh failed:', error);
            showChartError('Failed to refresh chart. Please try again.');
            announceToScreenReader('Chart refresh failed');
        })
        .finally(() => {
            // Re-enable buttons and remove spinner
            refreshBtn.disabled = false;
            buttons.forEach(btn => btn.disabled = false);
            refreshIcon.classList.remove('mdi-spin');
        });
}

function showChartError(message) {
    const chartContainer = document.querySelector('.chart-container');
    const existingError = chartContainer.querySelector('.chart-error');
    
    // Remove existing error if any
    if (existingError) {
        existingError.remove();
    }

    // Create and show new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'chart-error position-absolute top-0 start-0 w-100 p-3 text-center text-danger bg-danger bg-opacity-10';
    errorDiv.style.zIndex = '2';
    errorDiv.innerHTML = `
        <div class="d-flex align-items-center justify-content-center">
            <i class="mdi mdi-alert me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn btn-link btn-sm text-danger ms-2" onclick="refreshChart()">
                Try Again
            </button>
        </div>
    `;
    
    chartContainer.appendChild(errorDiv);

    // Auto-hide error after 5 seconds
    setTimeout(() => {
        errorDiv.style.opacity = '0';
        setTimeout(() => errorDiv.remove(), 300);
    }, 5000);
}

function refreshActivities() {
    return new Promise((resolve, reject) => {
        const tbody = document.getElementById('activities-table-body');
        const refreshBtn = document.querySelector('.btn-outline-primary');
        const refreshIcon = refreshBtn?.querySelector('.mdi-refresh');
        const errorContainer = document.getElementById('error-container');
        
        if (!tbody || !refreshBtn || !errorContainer) {
            console.error('Required elements not found');
            return reject(new Error('Required elements not found'));
        }

        // Disable button and show loading state
        refreshBtn.disabled = true;
        if (refreshIcon) refreshIcon.classList.add('mdi-spin');
        tbody.style.opacity = '0.5';

        // Clear previous errors
        errorContainer.innerHTML = '';

        // Helper function to handle API errors
        const handleApiError = (error) => {
            if (error.status === 401) {
                // Redirect to login if unauthenticated
                window.location.href = '/login';
                return;
            }
            throw error;
        };

        // Fetch activities from API
        fetch('/admin/dashboard/activities', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            credentials: 'same-origin'
        })
        .then(async response => {
            if (!response.ok) {
                const error = new Error();
                error.status = response.status;
                error.text = await response.text();
                return handleApiError(error);
            }
            return response.json();
        })
        .then(data => {
            if (!data?.success || !Array.isArray(data?.activities)) {
                throw new Error('Invalid response format');
            }

            // Update table content
            tbody.innerHTML = data.activities.length 
                ? data.activities.map(activity => `
                    <tr>
                        <td>${activity.time}</td>
                        <td>${activity.user}</td>
                        <td>${activity.description}</td>
                        <td>
                            <span class="badge bg-${activity.status_color}">${activity.status}</span>
                        </td>
                    </tr>
                `).join('')
                : `<tr>
                    <td colspan="4" class="text-center text-muted">
                        <i class="mdi mdi-information me-1"></i>
                        No recent activities found
                    </td>
                </tr>`;

            announceToScreenReader('Activities refreshed successfully');
            resolve(data.activities);
        })
        .catch(error => {
            // Log error details
            console.error('Activities refresh failed:', error);
            
            // Customize error message based on status
            const errorMessage = error.status === 403 
                ? 'Access denied. Admin privileges required.'
                : error.status === 401 
                    ? 'Session expired. Please log in again.'
                    : 'Failed to load activities. Please try again.';
            
            // Show error in container
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show mb-4';
            errorAlert.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-alert me-2"></i>
                    <div>
                        <strong>Error:</strong> ${errorMessage}
                        ${error.status !== 401 ? `
                            <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-2" 
                                    onclick="event.preventDefault(); refreshActivities()">
                                Try again
                            </button>
                        ` : ''}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            errorContainer.appendChild(errorAlert);

            // Show error in table with specific message
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger">
                        <i class="mdi mdi-alert me-1"></i>
                        ${errorMessage}
                    </td>
                </tr>
            `;

            // Announce error to screen reader
            announceToScreenReader(errorMessage);

            // If unauthorized, redirect after a short delay
            if (error.status === 401) {
                setTimeout(() => window.location.href = '/login', 2000);
            }

            reject(error);
        })
        .finally(() => {
            // Reset UI state
            refreshBtn.disabled = false;
            if (refreshIcon) refreshIcon.classList.remove('mdi-spin');
            tbody.style.opacity = '1';
        });
    });
}
// Show keyboard shortcuts help
function showKeyboardHelp() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-labelledby', 'keyboardHelpTitle');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center" id="keyboardHelpTitle">
                            <i class="mdi mdi-keyboard me-2" aria-hidden="true"></i>
                            Keyboard Shortcuts
                        </h5>
                        <button type="button" 
                                class="btn-close" 
                                data-bs-dismiss="modal" 
                                aria-label="Close keyboard shortcuts dialog"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group list-group-flush" role="list">
                            <div class="list-group-item" role="listitem">
                                <h6 class="mb-3">Chart Navigation</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-light text-dark me-2" role="img" aria-label="Left and Right arrows">←→</span>
                                    <span>Navigate between data points</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-light text-dark me-2" role="img" aria-label="Up and Down arrows">↑↓</span>
                                    <span>Switch between data series</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-light text-dark me-2">Home</span>
                                    <span>Jump to first data point</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark me-2">End</span>
                                    <span>Jump to last data point</span>
                                </div>
                            </div>
                            <div class="list-group-item" role="listitem">
                                <h6 class="mb-3">Global Shortcuts</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-light text-dark me-2">?</span>
                                    <span>Show/hide this help dialog</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark me-2">Esc</span>
                                    <span>Close dialog or clear chart selection</span>
                                </div>
                            </div>
                            <div class="list-group-item" role="listitem">
                                <div class="alert alert-info mb-0 d-flex align-items-start">
                                    <i class="mdi mdi-information-outline me-2 mt-1" aria-hidden="true"></i>
                                    <div>
                                        <strong>Tip:</strong> Press Tab to focus on the chart, then use these shortcuts to explore data. Press Escape to clear your selection.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" 
                                class="btn btn-primary" 
                                data-bs-dismiss="modal">
                            <i class="mdi mdi-check me-1" aria-hidden="true"></i>
                            Got it
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Store references
        const previousActiveElement = document.activeElement;
        const mainContent = document.getElementById('dashboard-content');
        const getFocusableElements = () => [
            ...modal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            )
        ];

        // Initial focusable elements
        const initialFocusableElements = getFocusableElements();
        const firstFocusable = initialFocusableElements[0];
        
        // Set up modal event listeners
        const modalEvents = {
            'shown.bs.modal': () => {
                firstFocusable.focus();
                announceToScreenReader('Keyboard shortcuts help dialog opened');
                mainContent.setAttribute('aria-hidden', 'true');
                
                // Set initial focus state for screen readers
                modal.querySelector('.modal-title').setAttribute('tabindex', '-1');
                modal.querySelector('.modal-title').focus();
            },
            'hidden.bs.modal': () => {
                mainContent.removeAttribute('aria-hidden');
                modal.remove();
                
                // Safely restore focus
                if (previousActiveElement?.isConnected && 'focus' in previousActiveElement) {
                    previousActiveElement.focus();
                    announceToScreenReader('Keyboard shortcuts dialog closed');
                }
            },
            'keydown': (e) => {
                const focusableElements = getFocusableElements();
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];
                const isTabPressed = e.key === 'Tab';
                const isEscapePressed = e.key === 'Escape';

                // Handle keyboard navigation
                switch (true) {
                    case isEscapePressed:
                        e.preventDefault();
                        bsModal.hide();
                        break;
                        
                    case isTabPressed && e.shiftKey && document.activeElement === firstElement:
                        e.preventDefault();
                        lastElement.focus();
                        break;
                        
                    case isTabPressed && !e.shiftKey && document.activeElement === lastElement:
                        e.preventDefault();
                        firstElement.focus();
                        break;
                }
            },
            'focusin': (e) => {
                // Ensure focus stays within modal
                if (!modal.contains(e.target)) {
                    firstFocusable.focus();
                }
            }
        };

        // Initialize modal with ARIA attributes
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', 'keyboardHelpTitle');

        // Add event listeners
        Object.entries(modalEvents).forEach(([event, handler]) => {
            modal.addEventListener(event, handler);
        });

        // Handle clicks outside modal
        modal.addEventListener('mousedown', (e) => {
            if (e.target === modal) {
                e.preventDefault();
                // Keep focus within modal
                firstFocusable.focus();
            }
        });

        // Initialize tooltips with dynamic titles
        const tooltips = Array.from(modal.querySelectorAll('.badge')).map(badge => 
            new bootstrap.Tooltip(badge, {
                placement: 'top',
                delay: { show: 500, hide: 100 },
                title: badge.textContent === '?' ? 'Press again to close' : badge.textContent
            })
        );

        try {
            // Debounce function for performance
            const debounce = (fn, delay) => {
                let timeoutId;
                return (...args) => {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => fn.apply(null, args), delay);
                };
            };

            // Track modal state to prevent duplicate handlers
            let isProcessingKeyboard = false;

            // Handle keyboard events with debouncing
            const handleKeyboard = debounce((e) => {
                if (isProcessingKeyboard || e.repeat) return;
                
                if (e.key === '?') {
                    isProcessingKeyboard = true;
                    e.preventDefault();
                    
                    try {
                        bsModal.hide();
                    } catch (error) {
                        console.warn('Error hiding modal:', error);
                        // Fallback cleanup if modal hide fails
                        cleanup();
                    } finally {
                        isProcessingKeyboard = false;
                    }
                }
            }, 150); // Debounce delay

            // Setup event listeners with error boundary
            try {
                document.addEventListener('keydown', handleKeyboard);
            } catch (error) {
                console.error('Error setting up keyboard listener:', error);
                // Fallback to basic modal functionality
                modal.querySelector('[data-bs-dismiss="modal"]')?.addEventListener('click', 
                    () => modal?.remove()
                );
            }

            // Create WeakMap for cleanup references
            const cleanupRefs = new WeakMap();
            
            // Cleanup function with memory leak prevention
            const cleanup = () => {
                // Prevent multiple cleanup calls
                if (cleanupRefs.has(modal)) return;
                cleanupRefs.set(modal, true);

                // Prioritized cleanup tasks
                const cleanupTasks = {
                    high: [
                        // Critical cleanup tasks that should run first
                        () => {
                            document.removeEventListener('keydown', handleKeyboard);
                            window.removeEventListener('beforeunload', cleanup);
                        },
                        () => mainContent?.removeAttribute('aria-hidden')
                    ],
                    medium: [
                        // UI cleanup tasks
                        () => {
                            tooltips.forEach(tooltip => {
                                if (tooltip?.dispose) tooltip.dispose();
                            });
                        }
                    ],
                    low: [
                        // Focus management and announcements
                        () => {
                            if (previousActiveElement?.isConnected && 'focus' in previousActiveElement) {
                                requestAnimationFrame(() => {
                                    try {
                                        previousActiveElement.focus();
                                        announceToScreenReader('Keyboard shortcuts dialog closed');
                                    } catch (error) {
                                        console.warn('Focus restoration failed:', error);
                                        document.body.focus();
                                    }
                                });
                            } else {
                                requestAnimationFrame(() => document.body.focus());
                            }
                        }
                    ]
                };

                // Process cleanup tasks in priority order
                const processTasks = async () => {
                    try {
                        // Process tasks in priority order
                        for (const priority of ['high', 'medium', 'low']) {
                            await Promise.all(
                                cleanupTasks[priority].map(task => 
                                    Promise.resolve().then(task).catch(error => 
                                        console.warn(`${priority} priority task failed:`, error)
                                    )
                                )
                            );
                        }

                        // Handle modal removal with transition
                        const removeModalWithTransition = () => new Promise((resolve, reject) => {
                            if (!modal?.isConnected) {
                                resolve();
                                return;
                            }

                            let isRemoved = false;
                            const cleanup = () => {
                                if (isRemoved) return;
                                isRemoved = true;

                                try {
                                    modal.remove();
                                    resolve();
                                } catch (error) {
                                    reject(error);
                                }
                            };

                            // Set up transition handling
                            const handleTransition = (e) => {
                                if (e.target !== modal) return;
                                cleanup();
                            };

                            // Set up fallback timeout
                            const timeoutId = setTimeout(cleanup, 300);

                            // Listen for transition end
                            modal.addEventListener('transitionend', handleTransition, { once: true });
                            modal.addEventListener('transitioncancel', cleanup, { once: true });

                            // Cleanup listeners if modal is removed externally
                            const observer = new MutationObserver((mutations) => {
                                if (!document.contains(modal)) {
                                    clearTimeout(timeoutId);
                                    observer.disconnect();
                                    resolve();
                                }
                            });
                            observer.observe(document.body, { childList: true, subtree: true });
                        });

                        await removeModalWithTransition().catch(error => {
                            console.warn('Modal removal failed, forcing cleanup:', error);
                            try {
                                modal?.parentNode?.removeChild(modal);
                            } catch (e) {
                                console.error('Force removal failed:', e);
                            }
                        });

                    } catch (error) {
                        // Perform emergency cleanup with error context
                        await emergencyCleanup('Critical cleanup error', error);
                    } finally {
                        // Clear any pending animation frames
                        if (window.requestAnimationFrame) {
                            const highestId = window.requestAnimationFrame(() => {});
                            for (let i = 0; i < highestId; i++) {
                                window.cancelAnimationFrame(i);
                            }
                        }
                    }
                };

                // Optimize cleanup by debouncing multiple calls
                const debouncedCleanup = (() => {
                    let timeoutId;
                    let isProcessing = false;
                    
                    return (error) => {
                        if (isProcessing) return;
                        isProcessing = true;

                        clearTimeout(timeoutId);
                        timeoutId = setTimeout(() => {
                            emergencyCleanup('Debounced cleanup error', error)
                                .finally(() => {
                                    isProcessing = false;
                                });
                        }, 50);
                    };
                })();

                // Emergency cleanup helper with performance optimization
                const emergencyCleanup = async (errorContext, error) => {
                    console.error(`${errorContext}:`, error);
                    
                    // Single cleanup queue with prioritized tasks
                    const cleanupQueue = [
                        // Priority 1: Critical DOM cleanup
                        {
                            task: () => modal?.remove(),
                            errorMessage: 'Failed to remove modal'
                        },
                        {
                            task: () => {
                                mainContent?.removeAttribute('aria-hidden');
                                document.querySelectorAll('.tooltip').forEach(el => el.remove());
                            },
                            errorMessage: 'Failed to reset page state'
                        },
                        // Priority 2: Event listeners and references
                        {
                            task: () => {
                                document.removeEventListener('keydown', handleKeyboard);
                                cleanupRefs.delete(modal);
                            },
                            errorMessage: 'Failed to cleanup event listeners'
                        },
                        // Priority 3: Tooltip disposal
                        {
                            task: () => tooltips.forEach(t => t?.dispose?.()),
                            errorMessage: 'Failed to dispose tooltips'
                        },
                        // Priority 4: Focus management
                        {
                            task: () => requestAnimationFrame(() => document.body.focus()),
                            errorMessage: 'Failed to restore focus'
                        }
                    ];

                    // Execute cleanup queue sequentially with error handling
                    for (const { task, errorMessage } of cleanupQueue) {
                        try {
                            await Promise.resolve(task());
                        } catch (e) {
                            console.warn(`${errorMessage}:`, e);
                        }
                    }
                };

                // Start cleanup process with optimized error handling
                processTasks()
                    .catch(error => {
                        // Handle specific error types
                        switch (true) {
                            case error?.name === 'AbortError':
                            case error?.message?.includes('aborted'):
                                return debouncedCleanup(new Error('Cleanup aborted'));
                            
                            case error instanceof DOMException:
                                return emergencyCleanup('DOM manipulation error', error);
                            
                            case error?.message?.includes('timeout'):
                                return emergencyCleanup('Cleanup timeout', error);
                            
                            default:
                                return emergencyCleanup('Fatal error during cleanup', error);
                        }
                    })
                    .finally(() => {
                        // Ensure all cleanup tasks complete
                        const finalCleanup = () => {
                            try {
                                // Clear animations and timers
                                if (window.requestAnimationFrame) {
                                    const highestId = window.requestAnimationFrame(() => {});
                                    for (let i = 0; i < highestId; i++) {
                                        window.cancelAnimationFrame(i);
                                    }
                                }

                                // Force DOM cleanup
                                if (modal?.isConnected) {
                                    modal.parentNode?.removeChild(modal);
                                }
                                document.querySelectorAll('.tooltip').forEach(el => el.remove());
                                
                                // Reset page state
                                mainContent?.removeAttribute('aria-hidden');
                                requestAnimationFrame(() => document.body.focus());
                            } catch (e) {
                                console.error('Final cleanup attempt failed:', e);
                            }
                        };

                        // Execute final cleanup in next microtask
                        queueMicrotask(finalCleanup);
                    });

            // Initialize modal cleanup manager
            const cleanupManager = (() => {
                // Configuration and state
                const config = {
                    maxAttempts: 3,
                    retryDelay: 300,
                    eventOptions: { once: true, passive: true }
                };

                let isProcessing = false;

                // Cleanup tasks with error handling
                const tasks = {
                    async removeModal() {
                        if (!modal?.isConnected) return;
                        await new Promise(resolve => {
                            requestAnimationFrame(() => {
                                try {
                                    modal.remove();
                                } catch (e) {
                                    modal?.parentNode?.removeChild(modal);
                                } finally {
                                    resolve();
                                }
                            });
                        });
                    },

                    async cleanupDOM() {
                        const tooltips = document.querySelectorAll('.tooltip');
                        if (!tooltips.length) return;
                        tooltips.forEach(el => el.remove());
                        mainContent?.removeAttribute('aria-hidden');
                    },

                    async restoreFocus() {
                        await new Promise(resolve => {
                            requestAnimationFrame(() => {
                                try {
                                    document.body.focus();
                                } catch {} // Ignore focus errors
                                resolve();
                            });
                        });
                    }
                };

                // Optimized cleanup execution with exponential backoff
                const executeCleanup = (() => {
                    const backoff = (attempt) => new Promise(resolve => 
                        setTimeout(resolve, config.retryDelay * Math.pow(2, attempt))
                    );

                    const retryTask = async (task, name) => {
                        for (let attempt = 0; attempt < config.maxAttempts; attempt++) {
                            try {
                                await task();
                                return;
                            } catch (error) {
                                const isLastAttempt = attempt === config.maxAttempts - 1;
                                console.warn(
                                    `Task ${name} attempt ${attempt + 1}/${config.maxAttempts} failed:`,
                                    error
                                );
                                if (isLastAttempt) throw error;
                                await backoff(attempt);
                            }
                        }
                    };

                    return async () => {
                        if (isProcessing) return;
                        isProcessing = true;

                        const results = await Promise.allSettled(
                            Object.entries(tasks).map(([name, task]) => 
                                retryTask(task, name)
                            )
                        );

                        const failures = results.filter(r => r.status === 'rejected');
                        if (failures.length) {
                            console.error(
                                `${failures.length} cleanup tasks failed permanently:`,
                                failures.map(f => f.reason)
                            );
                        }

                        isProcessing = false;
                    };
                })();

                // Event manager with error handling
                const eventManager = (() => {
                    // Configuration
                    const config = {
                        events: [
                            { type: 'hidden.bs.modal', target: modal },
                            { type: 'beforeunload', target: window },
                            { type: 'remove', target: modal }
                        ],
                        baseOptions: { once: true }
                    };

                    // Utilities
                    const utils = {
                        state: { running: false },
                        error: (action, type, error) => console.warn(`${action} failed for ${type}:`, error),
                        safeExecute: (fn, action, type) => {
                            try {
                                fn();
                            } catch (error) {
                                utils.error(action, type, error);
                            }
                        }
                    };

                    // Event handling
                    const eventHandler = {
                        getOptions: type => ({
                            ...config.baseOptions,
                            passive: type === 'beforeunload'
                        }),
                        getHandler: type => type === 'remove' ? cleanup : executeCleanup,
                        manage: (action, event) => {
                            utils.safeExecute(
                                () => event.target[`${action}EventListener`](
                                    event.type,
                                    eventHandler.getHandler(event.type),
                                    eventHandler.getOptions(event.type)
                                ),
                                action,
                                event.type
                            );
                        }
                    };

                    // DOM operations
                    const domOps = {
                        cleanup: () => {
                            utils.safeExecute(() => {
                                if (modal?.isConnected) modal.remove();
                                document.querySelectorAll('.tooltip')
                                    .forEach(el => utils.safeExecute(
                                        () => el.remove(),
                                        'remove',
                                        'tooltip'
                                    ));
                            }, 'cleanup', 'DOM');
                        }
                    };

                    // Cleanup function
                    const cleanup = () => {
                        if (utils.state.running) return;
                        utils.state.running = true;

                        queueMicrotask(() => {
                            config.events.forEach(event => eventHandler.manage('remove', event));
                            domOps.cleanup();
                        });
                    };

                    // Initialize
                    config.events.forEach(event => eventHandler.manage('add', event));

                    return { cleanup };
                })();

                return eventManager;
            })();
            };

        } catch (error) {
            console.error('Error setting up keyboard shortcuts modal:', error);
            announceToScreenReader('Error showing keyboard shortcuts. Please try again.');
            modal?.remove();
        }
    }
</script>
@endpush
