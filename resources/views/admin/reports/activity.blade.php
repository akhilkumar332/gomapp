@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Activity Log</h5>
            <div>
                <button class="btn btn-primary me-2" onclick="refreshActivities()">
                    <i class="mdi mdi-refresh me-1"></i>Refresh
                </button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back to Reports
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary {{ request('type') == 'all' || !request('type') ? 'active' : '' }}" 
                                onclick="filterActivities('all')">All</button>
                        <button type="button" class="btn btn-outline-primary {{ request('type') == 'user' ? 'active' : '' }}" 
                                onclick="filterActivities('user')">User Activities</button>
                        <button type="button" class="btn btn-outline-primary {{ request('type') == 'system' ? 'active' : '' }}" 
                                onclick="filterActivities('system')">System Events</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" placeholder="Search activities..." 
                               value="{{ request('search') }}">
                        <button class="btn btn-primary" onclick="searchActivities()">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('M d, Y H:i:s') }}</td>
                            <td>
                                @if($activity->user)
                                    {{ $activity->user->name }}
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>{{ $activity->action }}</td>
                            <td>
                                <span class="text-wrap">{{ $activity->description }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $activity->status_color }}">
                                    {{ ucfirst($activity->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $activity->ip_address }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterActivities(type) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('type', type);
    window.location.href = currentUrl.toString();
}

function searchActivities() {
    const searchTerm = document.getElementById('search').value;
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('search', searchTerm);
    window.location.href = currentUrl.toString();
}

function refreshActivities() {
    window.location.reload();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection
