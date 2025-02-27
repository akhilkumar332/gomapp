@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">System Status</h4>
            <div class="text-muted">Last updated: {{ $timestamp->diffForHumans() }}</div>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown">
                    <i class="bx bx-export me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.status.export', ['format' => 'json']) }}">
                            <i class="bx bx-code me-2"></i>JSON
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.status.export', ['format' => 'csv']) }}">
                            <i class="bx bx-spreadsheet me-2"></i>CSV
                        </a>
                    </li>
                </ul>
            </div>
            <button type="button" class="btn btn-primary refresh-status">
                <i class="bx bx-refresh me-1"></i>Refresh
            </button>
        </div>
    </div>

    @if(isset($error))
        <div class="alert alert-danger" role="alert">
            <i class="bx bx-error-circle me-1"></i>
            {{ $error }}
        </div>
    @endif

    <!-- Overall Status -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="avatar-md me-3 bg-{{ $overall_status === 'healthy' ? 'success' : ($overall_status === 'warning' ? 'warning' : 'danger') }}-subtle rounded-circle">
                    <i class="bx bx-{{ $overall_status === 'healthy' ? 'check' : ($overall_status === 'warning' ? 'error' : 'x') }} text-{{ $overall_status === 'healthy' ? 'success' : ($overall_status === 'warning' ? 'warning' : 'danger') }} display-6"></i>
                </div>
                <div>
                    <h5 class="mb-1">System Status: {{ ucfirst($overall_status) }}</h5>
                    <div class="text-muted">All systems are being monitored in real-time</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row g-4">
        <!-- API Status -->
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="card-title mb-0">API Status</h6>
                        <div class="avatar-sm bg-primary-subtle rounded-circle">
                            <i class="bx bx-code-alt text-primary display-6"></i>
                        </div>
                    </div>
                    @if(isset($status['api']))
                        <div class="mb-3">
                            <span class="badge bg-{{ $status['api']['status'] === 'healthy' ? 'success' : 'danger' }} rounded-pill">
                                {{ ucfirst($status['api']['status']) }}
                            </span>
                        </div>
                        <div class="text-muted mb-0">
                            {{ $status['api']['total_endpoints'] ?? 0 }} Total Endpoints
                        </div>
                        <button type="button" class="btn btn-link p-0 mt-2 view-api-details">
                            View Details<i class="bx bx-right-arrow-alt ms-1"></i>
                        </button>
                    @else
                        <div class="text-muted">API status not available</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Database Status -->
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="card-title mb-0">Database Status</h6>
                        <div class="avatar-sm bg-success-subtle rounded-circle">
                            <i class="bx bx-data text-success display-6"></i>
                        </div>
                    </div>
                    @if(isset($status['database']))
                        <div class="mb-3">
                            <span class="badge bg-{{ $status['database']['status'] === 'healthy' ? 'success' : 'danger' }} rounded-pill">
                                {{ ucfirst($status['database']['status']) }}
                            </span>
                        </div>
                        @foreach($status['database']['connections'] as $name => $connection)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-muted">{{ ucfirst($name) }}</div>
                                <span class="badge bg-{{ $connection['status'] === 'healthy' ? 'success' : 'danger' }}-subtle text-{{ $connection['status'] === 'healthy' ? 'success' : 'danger' }}">
                                    {{ isset($connection['connection_time']) ? $connection['connection_time'] . 'ms' : 'Error' }}
                                </span>
                            </div>
                        @endforeach
                        <button type="button" class="btn btn-link p-0 mt-2 view-db-details">
                            View Details<i class="bx bx-right-arrow-alt ms-1"></i>
                        </button>
                    @else
                        <div class="text-muted">Database status not available</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cache Status -->
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="card-title mb-0">Cache Status</h6>
                        <div class="avatar-sm bg-info-subtle rounded-circle">
                            <i class="bx bx-memory-card text-info display-6"></i>
                        </div>
                    </div>
                    @if(isset($status['cache']))
                        <div class="mb-3">
                            <span class="badge bg-{{ $status['cache']['status'] === 'healthy' ? 'success' : 'danger' }} rounded-pill">
                                {{ ucfirst($status['cache']['status']) }}
                            </span>
                        </div>
                        <div class="text-muted">
                            Driver: {{ ucfirst($status['cache']['driver']) }}
                        </div>
                    @else
                        <div class="text-muted">Cache status not available</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="card-title mb-0">System Status</h6>
                        <div class="avatar-sm bg-warning-subtle rounded-circle">
                            <i class="bx bx-server text-warning display-6"></i>
                        </div>
                    </div>
                    @if(isset($status['system']))
                        <div class="mb-3">
                            <span class="badge bg-{{ $status['system']['status'] === 'healthy' ? 'success' : 'danger' }} rounded-pill">
                                {{ ucfirst($status['system']['status']) }}
                            </span>
                        </div>
                        @if(isset($status['system']['memory']))
                            <div class="mb-2">
                                <div class="text-muted mb-1">Memory Usage</div>
                                <div class="progress" style="height: 6px;">
                                    @php
                                        $memoryUsage = $status['system']['memory']['usage_percentage'] ?? 0;
                                    @endphp
                                    <div class="progress-bar bg-{{ $memoryUsage > 90 ? 'danger' : ($memoryUsage > 70 ? 'warning' : 'success') }}"
                                         role="progressbar"
                                         style="width: {{ $memoryUsage }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ number_format($memoryUsage, 1) }}% Used</small>
                            </div>
                            <div class="text-muted small">
                                Current: {{ formatBytes($status['system']['memory']['current']) }}<br>
                                Peak: {{ formatBytes($status['system']['memory']['peak']) }}
                            </div>
                        @endif
                        <button type="button" class="btn btn-link p-0 mt-2 view-system-details">
                            View Details<i class="bx bx-right-arrow-alt ms-1"></i>
                        </button>
                    @else
                        <div class="text-muted">System status not available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- API Endpoints -->
    <div class="card mt-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">API Endpoints</h5>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="showAllEndpoints">
                    <label class="form-check-label" for="showAllEndpoints">Show All</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(isset($status['api']) && isset($status['api']['groups']))
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20px;"></th>
                                <th>Endpoint</th>
                                <th>Methods</th>
                                <th>Group</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($status['api']['groups'] as $group => $details)
                                @foreach($details['endpoints'] as $endpoint)
                                    <tr>
                                        <td>
                                            <i class="bx bx-code-curly text-primary"></i>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $endpoint['uri'] }}</div>
                                            @if($endpoint['name'])
                                                <small class="text-muted">{{ $endpoint['name'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($endpoint['methods'] as $method)
                                                <span class="badge bg-{{ 
                                                    $method === 'GET' ? 'success' : 
                                                    ($method === 'POST' ? 'primary' : 
                                                    ($method === 'PUT' ? 'warning' : 
                                                    ($method === 'DELETE' ? 'danger' : 'secondary')))
                                                }} rounded-pill me-1">
                                                    {{ $method }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ ucfirst($group) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="bx bx-check me-1"></i>Active
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <div class="avatar-md mx-auto mb-3 bg-light rounded-circle">
                        <i class="bx bx-code-alt text-secondary display-6"></i>
                    </div>
                    <h6>No API Endpoints Available</h6>
                    <p class="text-muted mb-0">No API endpoints were found or could be monitored.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- API Details Modal -->
<div class="modal fade" id="apiDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">API Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="apiDetailsContent">Loading...</div>
            </div>
        </div>
    </div>
</div>

<!-- Database Details Modal -->
<div class="modal fade" id="dbDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Database Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="dbDetailsContent">Loading...</div>
            </div>
        </div>
    </div>
</div>

<!-- System Details Modal -->
<div class="modal fade" id="systemDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">System Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="systemDetailsContent">Loading...</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh button handler
    const refreshBtn = document.querySelector('.refresh-status');
    refreshBtn.addEventListener('click', function() {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Refreshing...';

        fetch('{{ route("admin.status.refresh") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="bx bx-refresh me-1"></i>Refresh';
        });
    });

    // View details handlers
    document.querySelector('.view-api-details')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('apiDetailsModal'));
        modal.show();
        
        fetch('{{ route("admin.status.api-details") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('apiDetailsContent').innerHTML = formatApiDetails(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load API details');
                }
            })
            .catch(error => {
                document.getElementById('apiDetailsContent').innerHTML = 
                    `<div class="alert alert-danger">${error.message || 'Error loading API details'}</div>`;
            });
    });

    document.querySelector('.view-db-details')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('dbDetailsModal'));
        modal.show();
        
        fetch('{{ route("admin.status.db-details") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('dbDetailsContent').innerHTML = formatDbDetails(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load database details');
                }
            })
            .catch(error => {
                document.getElementById('dbDetailsContent').innerHTML = 
                    `<div class="alert alert-danger">${error.message || 'Error loading database details'}</div>`;
            });
    });

    document.querySelector('.view-system-details')?.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('systemDetailsModal'));
        modal.show();
        
        fetch('{{ route("admin.status.system-details") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('systemDetailsContent').innerHTML = formatSystemDetails(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load system details');
                }
            })
            .catch(error => {
                document.getElementById('systemDetailsContent').innerHTML = 
                    `<div class="alert alert-danger">${error.message || 'Error loading system details'}</div>`;
            });
    });

    // Show all endpoints toggle
    const showAllEndpoints = document.getElementById('showAllEndpoints');
    const endpointRows = document.querySelectorAll('tbody tr');
    
    showAllEndpoints?.addEventListener('change', function() {
        endpointRows.forEach((row, index) => {
            if (index >= 10) {
                row.style.display = this.checked ? 'table-row' : 'none';
            }
        });
    });

    // Initially hide rows beyond 10
    endpointRows.forEach((row, index) => {
        if (index >= 10) {
            row.style.display = 'none';
        }
    });
});

function formatApiDetails(data) {
    return `
        <div class="table-responsive">
            <table class="table">
                <tbody>
                    <tr>
                        <th>Total Endpoints</th>
                        <td>${data.total_endpoints}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-${data.status === 'healthy' ? 'success' : 'danger'}">
                                ${data.status}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Last Checked</th>
                        <td>${new Date(data.last_checked).toLocaleString()}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function formatDbDetails(data) {
    let html = '<div class="table-responsive"><table class="table">';
    
    for (const [connection, details] of Object.entries(data.connections)) {
        html += `
            <tr>
                <th>${connection.toUpperCase()}</th>
                <td>
                    <span class="badge bg-${details.status === 'healthy' ? 'success' : 'danger'}">
                        ${details.status}
                    </span>
                </td>
                <td>${details.connection_time}ms</td>
            </tr>
        `;
    }
    
    html += '</table></div>';
    return html;
}

function formatSystemDetails(data) {
    return `
        <div class="table-responsive">
            <table class="table">
                <tbody>
                    <tr>
                        <th>Hostname</th>
                        <td>${data.hostname}</td>
                    </tr>
                    <tr>
                        <th>Operating System</th>
                        <td>${data.os}</td>
                    </tr>
                    <tr>
                        <th>PHP Version</th>
                        <td>${data.php_version}</td>
                    </tr>
                    <tr>
                        <th>Server Software</th>
                        <td>${data.server_software}</td>
                    </tr>
                    <tr>
                        <th>CPU Usage</th>
                        <td>
                            <div>1 min: ${data.cpu_usage['1m']}</div>
                            <div>5 min: ${data.cpu_usage['5m']}</div>
                            <div>15 min: ${data.cpu_usage['15m']}</div>
                        </td>
                    </tr>
                    <tr>
                        <th>Memory</th>
                        <td>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-${data.memory.usage_percentage > 90 ? 'danger' : (data.memory.usage_percentage > 70 ? 'warning' : 'success')}"
                                     role="progressbar"
                                     style="width: ${data.memory.usage_percentage}%">
                                </div>
                            </div>
                            <div>Usage: ${data.memory.usage_percentage}%</div>
                            <div>Current: ${formatBytes(data.memory.current)}</div>
                            <div>Peak: ${formatBytes(data.memory.peak)}</div>
                            <div>Limit: ${formatBytes(data.memory.limit)}</div>
                        </td>
                    </tr>
                    <tr>
                        <th>Disk</th>
                        <td>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-${data.disk.usage_percentage > 90 ? 'danger' : (data.disk.usage_percentage > 70 ? 'warning' : 'success')}"
                                     role="progressbar"
                                     style="width: ${data.disk.usage_percentage}%">
                                </div>
                            </div>
                            <div>Usage: ${data.disk.usage_percentage}%</div>
                            <div>Total: ${formatBytes(data.disk.total)}</div>
                            <div>Used: ${formatBytes(data.disk.used)}</div>
                            <div>Free: ${formatBytes(data.disk.free)}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endpush

@php
function formatBytes($bytes) {
    if ($bytes === 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    
    return number_format($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
@endphp
@endsection
