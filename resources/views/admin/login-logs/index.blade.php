@extends('layouts.admin')

@section('title', 'Manage Login Logs')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Login Logs</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Device / Browser</th>
                            <th>Login Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loginLogs as $log)
                            <tr>
                                <td>{{ $log->user->name }}</td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ $log->user_agent }}</td>
                                <td>{{ $log->login_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No login logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $loginLogs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
