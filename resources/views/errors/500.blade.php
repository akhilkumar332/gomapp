@extends('errors.layout')

@section('code', '500')

@section('title', 'Server Error')

@section('icon')
<i class="mdi mdi-server-off icon"></i>
@endsection

@section('message')
We're experiencing some technical difficulties.
<br>
Our team has been notified and is working to fix the issue.
@if(isset($errorReference))
<br><br>
Please quote this reference number when contacting support:
<div class="error-reference">{{ $errorReference }}</div>
@endif
@endsection

@section('actions')
<a href="mailto:{{ config('app.support_email', 'support@example.com') }}" class="btn btn-secondary">
    <i class="mdi mdi-email"></i>
    Contact Support
</a>

@auth
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-primary">
            <i class="mdi mdi-history"></i>
            View Activity Logs
        </a>
    @endif
@endauth
@endsection

@section('scripts')
@if(app()->environment('local', 'staging'))
<script>
    // Auto-refresh the page after 30 seconds in development
    setTimeout(function() {
        window.location.reload();
    }, 30000);
</script>
@endif
@endsection
