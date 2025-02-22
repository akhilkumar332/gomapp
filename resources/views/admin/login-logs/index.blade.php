@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1>Login Logs</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.login-logs.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ ucfirst($user->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="date_range" class="form-control" placeholder="Date Range" value="{{ request('date_range') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.login-logs.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Device</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loginLogs as $log)
                            <tr>
                                <td>{{ $log->login_at ? $log->login_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                <td>{{ $log->user->name ?? 'Unknown' }}</td>
                                <td>{{ ucfirst($log->user->role ?? 'unknown') }}</td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                <td>
                                    @if($log->location_data)
                                        {{ $log->location_data['city'] ?? 'Unknown' }}, 
                                        {{ $log->location_data['country'] ?? 'Unknown' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted" title="{{ $log->user_agent }}">
                                        {{ \Str::limit($log->user_agent, 30) ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No login logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $loginLogs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .daterangepicker {
        z-index: 1100 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    $('input[name="date_range"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('input[name="date_range"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@endpush
