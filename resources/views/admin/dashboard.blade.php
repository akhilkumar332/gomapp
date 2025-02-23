@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title bg-primary-subtle rounded">
                                    <i class='bx bx-map text-primary fs-4'></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Total Zones</h6>
                            <h2 class="mb-0">{{ \App\Models\Zone::count() }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title bg-success-subtle rounded">
                                    <i class='bx bx-pin text-success fs-4'></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Total Locations</h6>
                            <h2 class="mb-0">{{ \App\Models\Location::count() }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title bg-info-subtle rounded">
                                    <i class='bx bx-user text-info fs-4'></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Total Drivers</h6>
                            <h2 class="mb-0">{{ \App\Models\User::where('role', 'driver')->count() }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-title bg-warning-subtle rounded">
                                    <i class='bx bx-car text-warning fs-4'></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Active Drivers</h6>
                            <h2 class="mb-0">{{ \App\Models\User::where('role', 'driver')->where('is_online', true)->count() }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        @foreach(\App\Models\ActivityLog::with('user')->latest()->take(5)->get() as $log)
                            <div class="activity-item pb-3 mb-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="activity-indicator me-3">
                                        <div class="avatar avatar-xs">
                                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                <i class='bx bx-check'></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="activity-content flex-grow-1">
                                        <p class="mb-0">{{ $log->description }}</p>
                                        <small class="text-muted">
                                            By {{ $log->user->name ?? 'Unknown' }} • {{ $log->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Logins -->
        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Logins</h5>
                    <a href="{{ route('admin.login-logs.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Device</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\LoginLog::with('user')->latest()->take(5)->get() as $log)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($log->user->name ?? 'Unknown') }}&background=4723D9&color=fff" 
                                                         alt="Avatar" class="rounded-circle">
                                                </div>
                                                <span>{{ $log->user->name ?? 'Unknown' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $log->ip_address }}</td>
                                        <td>{{ \App\Models\LoginLog::getDeviceType($log->user_agent) }}</td>
                                        <td>{{ $log->login_at->diffForHumans() }}</td>
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

@push('styles')
<style>
.avatar {
    position: relative;
    width: 2.375rem;
    height: 2.375rem;
    cursor: pointer;
}

.avatar-sm {
    width: 1.75rem;
    height: 1.75rem;
}

.avatar-xs {
    width: 1.5rem;
    height: 1.5rem;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--first-color);
}

.bg-primary-subtle {
    background-color: rgba(71, 35, 217, 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(23, 162, 184, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.activity-timeline {
    position: relative;
}

.activity-indicator {
    position: relative;
}

.activity-indicator::after {
    content: '';
    position: absolute;
    left: 50%;
    top: 100%;
    transform: translateX(-50%);
    width: 2px;
    height: calc(100% - 10px);
    background-color: #e9ecef;
}

.activity-item:last-child .activity-indicator::after {
    display: none;
}

.table > :not(caption) > * > * {
    padding: 1rem;
}
</style>
@endpush
