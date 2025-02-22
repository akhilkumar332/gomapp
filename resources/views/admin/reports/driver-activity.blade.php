@extends('layouts.admin')

@section('title', 'Driver Activity Report')

@section('content')
<div class="container">
    <h1 class="mb-4">Driver Activity Report</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <label for="driver" class="form-label">Select Driver</label>
            <select class="form-select" id="driver" name="driver_id">
                <option value="">All Drivers</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
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
                <i class="fas fa-chart-line me-1"></i> Generate Report
            </button>
        </div>
    </div>

    <div id="reportResults" class="mt-4">
        <!-- Report results will be displayed here -->
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
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

        $('#generateReport').on('click', function() {
            const driverId = $('#driver').val();
            const dateRange = $('#date_range').val();

            $.ajax({
                url: '/api/reports/driver-activity',
                method: 'GET',
                data: {
                    driver_id: driverId,
                    date_range: dateRange
                },
                success: function(data) {
                    let html = '<h5>Activity Report</h5><ul class="list-group">';
                    data.forEach(activity => {
                        html += `<li class="list-group-item">${activity.description} on ${activity.created_at}</li>`;
                    });
                    html += '</ul>';
                    $('#reportResults').html(html);
                },
                error: function(xhr) {
                    $('#reportResults').html('<div class="alert alert-danger">Error generating report.</div>');
                }
            });
        });
    });
</script>
@endpush

@endsection
