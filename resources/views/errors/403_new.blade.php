@extends('errors.layouts.error')

@section('title', 'Access Denied')
@section('error-color', '#dc3545')
@section('icon', 'bx-shield-x')
@section('icon-animation', 'shake')

@section('message')
{{ $exception->getMessage() ?: 'You do not have permission to access this page.' }}
@endsection

@section('content')
<div class="error-details">
    <h3>What might be wrong?</h3>
    <ul>
        <li>You may not have the required permissions</li>
        <li>Your session might have expired</li>
        <li>You might be trying to access a restricted area</li>
        <li>You might need to log in with a different account</li>
    </ul>
</div>

@if(app()->environment('local'))
    <div class="debug-info">
        <h3>Debug Information</h3>
        <div class="error-trace">
            <div class="error-location">
                <strong>URL:</strong> {{ request()->url() }}
            </div>
            <div class="error-context">
                <strong>User:</strong> {{ auth()->user()?->email ?? 'Guest' }}<br>
                <strong>Role:</strong> {{ auth()->user()?->role ?? 'None' }}
            </div>
            @if($exception->getFile())
                <div class="error-stack">
                    <strong>Location:</strong>
                    <pre>{{ $exception->getFile() }}:{{ $exception->getLine() }}</pre>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="support-info">
    <p>If you believe this is a mistake, please contact our support team:</p>
    <ul>
        <li>Email: support@delivery-management.com</li>
        <li>Reference ID: {{ Str::random(8) }}</li>
    </ul>
</div>
@endsection

@section('actions')
<a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-outline">
    <i class='bx bx-arrow-back'></i>
    Go Back
</a>
<a href="{{ route('login') }}" class="btn btn-primary">
    <i class='bx bx-log-in'></i>
    Login
</a>
@endsection

@section('additional-styles')
.debug-info {
    margin: 2rem 0;
    padding: 1rem;
    background-color: rgba(220, 53, 69, 0.1);
    border-radius: 0.5rem;
    text-align: left;
}

.debug-info h3 {
    color: #dc3545;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.error-trace {
    font-family: monospace;
    font-size: 0.875rem;
}

.error-location,
.error-context,
.error-stack {
    margin-bottom: 1rem;
    padding: 0.5rem;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 0.25rem;
}

.error-stack pre {
    margin: 0.5rem 0 0;
    white-space: pre-wrap;
}

.support-info {
    margin-top: 2rem;
    padding: 1rem;
    background-color: rgba(220, 53, 69, 0.05);
    border-radius: 0.5rem;
    text-align: left;
}

.support-info p {
    margin-bottom: 0.5rem;
    color: #6c757d;
}

.support-info ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.support-info li {
    margin-bottom: 0.25rem;
    color: #2D3748;
    font-size: 0.875rem;
}

@media (prefers-color-scheme: dark) {
    .debug-info {
        background-color: rgba(220, 53, 69, 0.2);
    }

    .error-location,
    .error-context,
    .error-stack {
        background-color: rgba(0, 0, 0, 0.2);
        color: #cbd5e0;
    }

    .support-info {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .support-info li {
        color: #cbd5e0;
    }
}
@endsection

@section('scripts')
@if(app()->environment('local'))
<script>
    // Log access denied details in local environment
    console.error('403 Error:', {
        url: @json(request()->url()),
        user: @json(auth()->user()?->only(['id', 'email', 'role']) ?? 'Guest'),
        message: @json($exception->getMessage())
    });
</script>
@endif
@endsection
