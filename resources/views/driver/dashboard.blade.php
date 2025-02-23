@extends('layouts.driver')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-primary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-map text-primary" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ $zones->count() }}</h3>
                            <p class="text-muted mb-0">Assigned Zones</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="rounded-circle bg-success bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="mdi mdi-check-circle text-success" style="font-size: 24px;"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ $completedToday }}</h3>
                            <p class="text-muted mb-0">Completed Today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
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

    <!-- Assigned Zones -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Assigned Zones</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                @foreach($zones as $zone)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ $zone->name }}</h5>
                                <p class="text-muted">{{ $zone->description }}</p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Active Locations</span>
                                    <span class="badge bg-primary">{{ $zone->locations->count() }}</span>
                                </div>

                                <a href="{{ route('driver.zones.show', $zone) }}" class="btn btn-primary w-100">
                                    View Locations
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Activities</h5>
            <a href="{{ route('driver.activities') }}" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Activity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $activity)
                            <tr>
                                <td>{{ $activity->created_at->diffForHumans() }}</td>
                                <td>{{ $activity->location ? $activity->location->shop_name : 'N/A' }}</td>
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
// Initialize any driver-specific JavaScript here
document.addEventListener('DOMContentLoaded', function() {
    // Example: Update driver location periodically
    function updateLocation() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                fetch('/api/driver/location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    })
                });
            });
        }
    }

    // Update location every 5 minutes
    setInterval(updateLocation, 300000);
    updateLocation(); // Initial update
});
</script>
@endpush
