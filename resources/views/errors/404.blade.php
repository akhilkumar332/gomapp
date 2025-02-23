@extends('errors.layouts.error')

@section('title', 'Page Not Found')
@section('error-color', '#6c757d')
@section('icon', 'bx-search-alt')
@section('icon-animation', 'pulse')

@section('message')
{{ $exception->getMessage() ?: 'The page you are looking for could not be found.' }}
@endsection

@section('content')
<div class="error-details">
    <h3>What might have happened?</h3>
    <ul>
        <li>The page may have been moved or deleted</li>
        <li>You might have typed the address incorrectly</li>
        <li>The link you clicked may be outdated</li>
        <li>You might not have permission to view this page</li>
    </ul>
</div>

@if(app()->environment('local'))
    <div class="debug-info">
        <h3>Debug Information</h3>
        <pre>{{ request()->url() }}</pre>
        @if($exception->getFile())
            <p>{{ $exception->getFile() }}:{{ $exception->getLine() }}</p>
        @endif
    </div>
@endif
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
    margin-top: 2rem;
    padding: 1rem;
    background-color: rgba(108, 117, 125, 0.1);
    border-radius: 0.5rem;
    text-align: left;
}

.debug-info h3 {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.debug-info pre {
    margin: 0;
    padding: 0.5rem;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 0.25rem;
    font-size: 0.875rem;
    overflow-x: auto;
}

.debug-info p {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #6c757d;
}

@media (prefers-color-scheme: dark) {
    .debug-info {
        background-color: rgba(108, 117, 125, 0.2);
    }

    .debug-info pre {
        background-color: rgba(0, 0, 0, 0.2);
    }
}
@endsection

@section('scripts')
@if(app()->environment('local'))
<script>
    // Log 404 errors in local environment
    console.error('404 Error:', {
        url: window.location.href,
        referrer: document.referrer,
        userAgent: navigator.userAgent
    });
</script>
@endif
@endsection
