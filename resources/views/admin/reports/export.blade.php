@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Export Reports</h5>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left me-1"></i>Back to Reports
            </a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Delivery Report Export -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">Delivery Report</h6>
                            <p class="text-muted mb-4">Export delivery data including status, time, and location details.</p>
                            
                            <form action="{{ route('admin.reports.export.deliveries') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Date Range</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="start_date" required>
                                        <span class="input-group-text">to</span>
                                        <input type="date" class="form-control" name="end_date" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Format</label>
                                    <select class="form-select" name="format">
                                        <option value="csv">CSV</option>
                                        <option value="xlsx">Excel (XLSX)</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Include Fields</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="driver_details" checked>
                                        <label class="form-check-label">Driver Details</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="location_details" checked>
                                        <label class="form-check-label">Location Details</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="collection_details" checked>
                                        <label class="form-check-label">Collection Details</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-download me-1"></i>Export Delivery Report
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Collection Report Export -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">Collection Report</h6>
                            <p class="text-muted mb-4">Export collection data including amounts, dates, and location details.</p>
                            
                            <form action="{{ route('admin.reports.export.collections') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Date Range</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="start_date" required>
                                        <span class="input-group-text">to</span>
                                        <input type="date" class="form-control" name="end_date" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Format</label>
                                    <select class="form-select" name="format">
                                        <option value="csv">CSV</option>
                                        <option value="xlsx">Excel (XLSX)</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Group By</label>
                                    <select class="form-select" name="group_by">
                                        <option value="date">Date</option>
                                        <option value="zone">Zone</option>
                                        <option value="driver">Driver</option>
                                        <option value="location">Location</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-download me-1"></i>Export Collection Report
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Export History -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Recent Exports</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Report Type</th>
                                            <th>Format</th>
                                            <th>Generated By</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentExports as $export)
                                        <tr>
                                            <td>{{ $export->created_at->format('M d, Y H:i A') }}</td>
                                            <td>{{ ucfirst($export->type) }} Report</td>
                                            <td>{{ strtoupper($export->format) }}</td>
                                            <td>{{ $export->user->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $export->status_color }}">
                                                    {{ ucfirst($export->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($export->status === 'completed')
                                                <a href="{{ route('admin.reports.download', $export) }}" class="btn btn-sm btn-primary">
                                                    <i class="mdi mdi-download"></i>
                                                </a>
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
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);

    // Format dates for input fields
    const formatDate = (date) => {
        return date.toISOString().split('T')[0];
    };

    // Set default date ranges for both forms
    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (input.name === 'start_date') {
            input.value = formatDate(thirtyDaysAgo);
        } else if (input.name === 'end_date') {
            input.value = formatDate(today);
        }
    });
});
</script>
@endpush
@endsection
