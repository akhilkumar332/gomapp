@extends('errors.layout')

@section('code', '503')

@section('title', 'Service Unavailable')

@section('icon')
<i class="mdi mdi-cloud-off-outline icon"></i>
@endsection

@section('message')
Our service is temporarily unavailable.
<br>
We're working hard to get things back to normal.

@if(isset($retryAfter))
    <div class="retry-info" style="margin-top: 1rem; padding: 1rem; background: #F3F4F6; border-radius: 0.5rem;">
        <div style="font-size: 0.875rem; color: #6B7280;">Expected resolution in:</div>
        <div style="font-size: 1.25rem; font-weight: 600; color: var(--primary-color);">
            <span id="countdown">{{ $retryAfter }}</span> seconds
        </div>
        <div style="font-size: 0.875rem; color: #6B7280; margin-top: 0.5rem;">
            Estimated time: <span id="estimatedTime">{{ now()->addSeconds($retryAfter)->format('H:i') }}</span>
        </div>
    </div>
@endif

@if(app()->environment('local', 'staging'))
    <div class="debug-info" style="margin-top: 1rem; padding: 1rem; background: #FEF3C7; color: #92400E; border-radius: 0.5rem; text-align: left; font-size: 0.875rem;">
        <div style="font-weight: 600; margin-bottom: 0.5rem;">Debug Information:</div>
        @if(isset($exception))
            <div>Message: {{ $exception->getMessage() }}</div>
            <div>File: {{ basename($exception->getFile()) }}:{{ $exception->getLine() }}</div>
        @endif
        @if(isset($errorReference))
            <div>Reference: {{ $errorReference }}</div>
        @endif
    </div>
@endif
@endsection

@section('actions')
<button onclick="window.location.reload()" class="btn btn-primary">
    <i class="mdi mdi-refresh"></i>
    Try Again
</button>

<a href="https://status.{{ request()->getHost() }}" target="_blank" class="btn btn-secondary">
    <i class="mdi mdi-chart-line"></i>
    Check System Status
</a>

@auth
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">
            <i class="mdi mdi-history"></i>
            View System Logs
        </a>
    @endif
@endauth
@endsection

@section('scripts')
@if(isset($retryAfter))
<script>
    // Countdown timer
    let timeLeft = {{ $retryAfter }};
    const countdownElement = document.getElementById('countdown');
    
    const countdown = setInterval(() => {
        timeLeft--;
        countdownElement.textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            window.location.reload();
        }
    }, 1000);

    // Add a visual progress bar
    const progressBar = document.createElement('div');
    progressBar.style.width = '100%';
    progressBar.style.height = '4px';
    progressBar.style.backgroundColor = '#E5E7EB';
    progressBar.style.borderRadius = '2px';
    progressBar.style.marginTop = '0.5rem';
    progressBar.style.overflow = 'hidden';

    const progress = document.createElement('div');
    progress.style.width = '100%';
    progress.style.height = '100%';
    progress.style.backgroundColor = 'var(--primary-color)';
    progress.style.transition = 'width linear 1s';

    progressBar.appendChild(progress);
    document.querySelector('.retry-info').appendChild(progressBar);

    // Update progress bar
    const updateProgress = () => {
        const percentage = (timeLeft / {{ $retryAfter }}) * 100;
        progress.style.width = percentage + '%';
    };

    setInterval(updateProgress, 1000);
    updateProgress();

    // Check status periodically
    const checkStatus = async () => {
        try {
            const response = await fetch('/api/health-check');
            if (response.ok) {
                window.location.reload();
            }
        } catch (error) {
            console.log('Service still unavailable');
        }
    };

    // Check status every 10 seconds
    setInterval(checkStatus, 10000);
</script>
@endif

@if(app()->environment('local', 'staging'))
<script>
    // Auto-refresh in development/staging
    setTimeout(() => {
        window.location.reload();
    }, 30000);
</script>
@endif
@endsection
