@extends('errors.layout')

@section('code', '429')

@section('title', 'Too Many Requests')

@section('icon')
<i class="mdi mdi-timer-sand icon"></i>
@endsection

@section('message')
You've made too many requests in a short period of time.
<br>
Please wait a moment before trying again.

@if(isset($retryAfter))
    <div class="retry-info" style="margin-top: 1rem; padding: 1rem; background: #F3F4F6; border-radius: 0.5rem;">
        <div style="font-size: 0.875rem; color: #6B7280;">You can try again in:</div>
        <div style="font-size: 1.25rem; font-weight: 600; color: var(--primary-color);">
            <span id="countdown">{{ $retryAfter }}</span> seconds
        </div>
    </div>
@endif
@endsection

@section('actions')
@auth
    @if(auth()->user()->isDriver())
        <a href="{{ route('driver.dashboard') }}" class="btn btn-primary">
            <i class="mdi mdi-view-dashboard"></i>
            Return to Dashboard
        </a>
    @elseif(auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
            <i class="mdi mdi-view-dashboard"></i>
            Return to Dashboard
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
</script>
@endif
@endsection
