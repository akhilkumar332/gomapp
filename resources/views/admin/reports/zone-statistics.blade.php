@extends('layouts.admin')

@section('title', 'Zone Statistics Report')

@section('content')
<div class="container">
    <h1 class="mb-4">Zone Statistics Report</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <label for="zone" class="form-label">Select Zone</label>
            <select class="form-select" id="zone" name="zone_id">
                <option value="">All Zones</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="date_range" class="form-label">Date Range</label>
            <input type="text" class="form-control" id="date_range" name="date_range" placeholder="Select date range">
        </div>

        <div class="col-md-4">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-primary w-100" id="generateReport">
                <i class="fas fa-chart-bar me-1"></i> Generate Report
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Zone Activity Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="zoneActivityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Driver Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="driverDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detailed Statistics</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Zone</th>
                            <th>Total Locations</th>
                            <th>Active Drivers</th>
                            <th>Total Deliveries</th>
                            <th>Success Rate</th>
                        </tr>
                    </thead>
                    <tbody id="statisticsTableBody">
                        <!-- Statistics data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
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
        const activityCtx = document.getElementById('zoneActivityChart').getContext('2d');
        const distributionCtx = document.getElementById('driverDistributionChart').getContext('2d');

        const activityChart = new Chart(activityCtx, {
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
                        text: 'Zone Activity Over Time'
                    }
                }
            }
        });

        const distributionChart = new Chart(distributionCtx, {
            type: 'pie',
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
                        text: 'Driver Distribution by Zone'
                    }
                }
            }
        });

        // Handle report generation
        $('#generateReport').on('click', function() {
            const zoneId = $('#zone').val();
            const dateRange = $('#date_range').val();

            $.ajax({
                url: '/api/reports/zone-statistics',
                method: 'GET',
                data: {
                    zone_id: zoneId,
                    date_range: dateRange
                },
                success: function(data) {
                    // Update activity chart
                    activityChart.data = {
                        labels: data.activity.dates,
                        datasets: [{
                            label: 'Deliveries',
                            data: data.activity.deliveries,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                    };
                    activityChart.update();

                    // Update distribution chart
                    distributionChart.data = {
                        labels: data.distribution.labels,
                        datasets: [{
                            data: data.distribution.data,
                            backgroundColor: [
                                'rgb(255, 99, 132)',
                                'rgb(54, 162, 235)',
                                'rgb(255, 205, 86)',
                                'rgb(75, 192, 192)',
                                'rgb(153, 102, 255)'
                            ]
                        }]
                    };
                    distributionChart.update();

                    // Update statistics table
                    let tableHtml = '';
                    data.statistics.forEach(stat => {
                        tableHtml += `
                            <tr>
                                <td>${stat.zone_name}</td>
                                <td>${stat.total_locations}</td>
                                <td>${stat.active_drivers}</td>
                                <td>${stat.total_deliveries}</td>
                                <td>${stat.success_rate}%</td>
                            </tr>
                        `;
                    });
                    $('#statisticsTableBody').html(tableHtml);
                },
                error: function(xhr) {
                    alert('Error generating report. Please try again.');
                }
            });
        });
    });
</script>
@endpush

@endsection
