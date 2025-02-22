@extends('layouts.admin')

@section('title', 'System Usage Report')

@section('content')
<div class="container">
    <h1 class="mb-4">System Usage Report</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <label for="date_range" class="form-label">Date Range</label>
            <input type="text" class="form-control" id="date_range" name="date_range" placeholder="Select date range">
        </div>

        <div class="col-md-4">
            <label for="user_type" class="form-label">User Type</label>
            <select class="form-select" id="user_type" name="user_type">
                <option value="">All Users</option>
                <option value="admin">Admins</option>
                <option value="driver">Drivers</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-primary w-100" id="generateReport">
                <i class="fas fa-chart-line me-1"></i> Generate Report
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User Activity Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="userActivityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Peak Usage Hours</h5>
                </div>
                <div class="card-body">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="metric-card bg-primary text-white p-3 rounded">
                                <h6>Active Users</h6>
                                <h2 id="activeUsers">0</h2>
                                <small>Currently online</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card bg-success text-white p-3 rounded">
                                <h6>Total Sessions</h6>
                                <h2 id="totalSessions">0</h2>
                                <small>In selected period</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card bg-info text-white p-3 rounded">
                                <h6>Avg. Session Duration</h6>
                                <h2 id="avgSessionDuration">0m</h2>
                                <small>Per user</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card bg-warning text-white p-3 rounded">
                                <h6>Error Rate</h6>
                                <h2 id="errorRate">0%</h2>
                                <small>System errors</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detailed Usage Logs</h5>
            <button type="button" class="btn btn-success btn-sm" id="exportLogs">
                <i class="fas fa-file-excel me-1"></i> Export Logs
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="usageLogsTableBody">
                        <!-- Usage logs data will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div id="pagination" class="mt-3">
                <!-- Pagination controls will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .metric-card {
        border-radius: 10px;
        transition: transform 0.2s;
    }
    .metric-card:hover {
        transform: translateY(-5px);
    }
    .metric-card h2 {
        font-size: 2rem;
        margin: 10px 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Initialize charts
        const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
        const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');

        const userActivityChart = new Chart(userActivityCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'User Activity Over Time'
                    }
                }
            }
        });

        const peakHoursChart = new Chart(peakHoursCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Peak Usage Hours'
                    }
                }
            }
        });

        // Handle report generation
        $('#generateReport').on('click', function() {
            const dateRange = $('#date_range').val();
            const userType = $('#user_type').val();

            $.ajax({
                url: '/api/reports/system-usage',
                method: 'GET',
                data: {
                    date_range: dateRange,
                    user_type: userType
                },
                success: function(data) {
                    updateCharts(data);
                    updateMetrics(data);
                    updateUsageLogs(data);
                },
                error: function(xhr) {
                    alert('Error generating report. Please try again.');
                }
            });
        });

        // Handle log export
        $('#exportLogs').on('click', function() {
            const dateRange = $('#date_range').val();
            const userType = $('#user_type').val();
            
            window.location.href = `/api/reports/system-usage/export?date_range=${dateRange}&user_type=${userType}`;
        });

        function updateCharts(data) {
            // Update user activity chart
            userActivityChart.data = {
                labels: data.activity.dates,
                datasets: [{
                    label: 'Active Users',
                    data: data.activity.users,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            };
            userActivityChart.update();

            // Update peak hours chart
            peakHoursChart.data = {
                labels: data.peak_hours.hours,
                datasets: [{
                    label: 'Usage Count',
                    data: data.peak_hours.counts,
                    backgroundColor: 'rgb(54, 162, 235)'
                }]
            };
            peakHoursChart.update();
        }

        function updateMetrics(data) {
            $('#activeUsers').text(data.metrics.active_users);
            $('#totalSessions').text(data.metrics.total_sessions);
            $('#avgSessionDuration').text(data.metrics.avg_session_duration);
            $('#errorRate').text(data.metrics.error_rate);
        }

        function updateUsageLogs(data) {
            let tableHtml = '';
            data.logs.forEach(log => {
                tableHtml += `
                    <tr>
                        <td>${moment(log.timestamp).format('YYYY-MM-DD HH:mm:ss')}</td>
                        <td>${log.user}</td>
                        <td>${log.action}</td>
                        <td>${log.module}</td>
                        <td>${log.duration}</td>
                        <td>
                            <span class="badge bg-${log.status === 'success' ? 'success' : 'danger'}">
                                ${log.status}
                            </span>
                        </td>
                    </tr>
                `;
            });
            $('#usageLogsTableBody').html(tableHtml);
            
            // Update pagination
            $('#pagination').html(data.pagination);
        }
    });
</script>
@endpush

@endsection
