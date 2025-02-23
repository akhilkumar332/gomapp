@extends('errors.layouts.error')

@section('title', 'Server Error')
@section('error-color', '#dc3545')
@section('icon', 'bx-error')
@section('icon-animation', 'shake')

@section('message')
{{ $exception->getMessage() ?: 'Oops! Something went wrong on our servers.' }}
@endsection

@section('content')
<div class="error-details">
    <h3>What happened?</h3>
    <ul>
        <li>Our servers encountered an unexpected condition</li>
        <li>The system is temporarily unable to handle the request</li>
        <li>Our team has been automatically notified</li>
        <li>We're working to fix the issue quickly</li>
    </ul>
</div>

@if(app()->environment('local'))
    <div class="debug-info">
        <h3>Debug Information</h3>
        <div class="error-trace">
            <div class="error-location">
                <strong>Location:</strong>
                <span>{{ $exception->getFile() }}:{{ $exception->getLine() }}</span>
            </div>
            <div class="error-stack">
                <strong>Stack Trace:</strong>
                <pre>{{ $exception->getTraceAsString() }}</pre>
            </div>
        </div>
    </div>
@endif

<div class="support-info">
    <p>If this problem persists, please contact our support team:</p>
    <ul>
        <li>Email: support@delivery-management.com</li>
        <li>Phone: +233 123 456 789</li>
        <li>Reference ID: {{ Str::random(8) }}</li>
    </ul>
</div>
@endsection

@section('actions')
<a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-outline">
    <i class='bx bx-arrow-back'></i>
    Go Back
</a>
<a href="{{ url('/') }}" class="btn btn-primary">
    <i class='bx bx-home'></i>
    Return Home
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

.error-location {
    margin-bottom: 1rem;
    padding: 0.5rem;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 0.25rem;
}

.error-stack {
    max-height: 200px;
    overflow-y: auto;
}

.error-stack pre {
    margin: 0;
    padding: 0.5rem;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 0.25rem;
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
    .error-stack pre {
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
    // Log error details in local environment
    console.error('500 Error:', {
        message: @json($exception->getMessage()),
        file: @json($exception->getFile()),
        line: @json($exception->getLine()),
        trace: @json($exception->getTraceAsString())
    });
</script>
@endif
@endsection
