@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Admin Dashboard</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Zones</h5>
                    <p class="card-text display-4">{{ \App\Models\Zone::count() }}</p>
                    <a href="{{ route('admin.zones.index') }}" class="btn btn-light">Manage Zones</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Locations</h5>
                    <p class="card-text display-4">{{ \App\Models\Location::count() }}</p>
                    <a href="{{ route('admin.locations.index') }}" class="btn btn-light">Manage Locations</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Drivers</h5>
                    <p class="card-text display-4">{{ \App\Models\User::where('role', 'driver')->count() }}</p>
                    <a href="{{ route('admin.drivers.index') }}" class="btn btn-light">Manage Drivers</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach(\App\Models\ActivityLog::with('user')->latest()->take(5)->get() as $log)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $log->description }}</h6>
                                    <small>{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                                <small class="text-muted">
                                    By {{ $log->user->name ?? 'Unknown' }} ({{ $log->device_type }})
                                </small>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-primary">View All Activity</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Logins</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach(\App\Models\LoginLog::with('user')->latest()->take(5)->get() as $log)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $log->user->name ?? 'Unknown' }}</h6>
                                    <small>{{ $log->login_at->diffForHumans() }}</small>
                                </div>
                                <small class="text-muted">
                                    From {{ $log->ip_address }} ({{ \App\Models\LoginLog::getDeviceType($log->user_agent) }})
                                </small>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.login-logs.index') }}" class="btn btn-outline-primary">View All Logins</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: none;
}
.list-group-item {
    border: none;
    border-radius: 5px;
    margin-bottom: 5px;
    background-color: #f8f9fa;
}
.display-4 {
    font-size: 2.5rem;
    font-weight: 300;
    line-height: 1.2;
}
</style>
@endpush
@endsection
