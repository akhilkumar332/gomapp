@extends('layouts.driver')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">My Activities</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Activity</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>IP Address</th>
                            <th>Device</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr>
                                <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
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
                                <td>
                                    @if($activity->location)
                                        <a href="{{ route('driver.locations.show', $activity->location) }}">
                                            {{ $activity->location->shop_name }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $activity->ip_address }}</td>
                                <td>
                                    @php
                                        $deviceType = \App\Models\ActivityLog::getDeviceType($activity->user_agent);
                                        $deviceIcon = [
                                            'mobile' => 'mdi-cellphone',
                                            'tablet' => 'mdi-tablet',
                                            'desktop' => 'mdi-desktop-tower-monitor',
                                            'unknown' => 'mdi-devices'
                                        ][$deviceType];
                                    @endphp
                                    <i class="mdi {{ $deviceIcon }} text-muted" title="{{ ucfirst($deviceType) }}"></i>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    const table = document.querySelector('table');
    if (table) {
        new DataTable(table, {
            order: [[0, 'desc']],
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>>rtip',
        });
    }
});
</script>
@endpush
