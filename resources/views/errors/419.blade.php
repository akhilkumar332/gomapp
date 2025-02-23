@extends('errors.layout')

@section('code', '419')

@section('title', 'Page Expired')

@section('icon')
<i class="mdi mdi-clock-alert icon"></i>
@endsection

@section('message')
Your session has expired due to inactivity.
<br>
Please refresh the page and try again.
@endsection

@section('actions')
<button onclick="window.location.reload()" class="btn btn-primary">
    <i class="mdi mdi-refresh"></i>
    Refresh Page
</button>

@auth
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-secondary">
            <i class="mdi mdi-logout"></i>
            Log Out & Try Again
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="btn btn-secondary">
        <i class="mdi mdi-login"></i>
        Log In Again
    </a>
@endauth
@endsection

@section('scripts')
<script>
    // Auto-refresh after 5 seconds
    setTimeout(function() {
        window.location.reload();
    }, 5000);

    // Add a visual countdown
    const countdown = document.createElement('div');
    countdown.style.marginTop = '1rem';
    countdown.style.fontSize = '0.875rem';
    countdown.style.color = '#6B7280';
    countdown.innerHTML = 'Auto-refreshing in <span id="timer">5</span> seconds...';
    
    document.querySelector('.actions').insertAdjacentElement('beforebegin', countdown);

    let timeLeft = 5;
    setInterval(() => {
        timeLeft--;
        if (timeLeft >= 0) {
            document.getElementById('timer').textContent = timeLeft;
        }
    }, 1000);

    // Add progress bar
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
    countdown.appendChild(progressBar);

    // Update progress bar
    const updateProgress = () => {
        const percentage = (timeLeft / 5) * 100;
        progress.style.width = percentage + '%';
    };

    setInterval(updateProgress, 1000);
    updateProgress();
</script>
@endsection
