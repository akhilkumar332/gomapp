@extends('errors.layout')

@section('code', 'NET')

@section('title', 'Network Error')

@section('icon')
<i class="mdi mdi-wifi-off icon"></i>
@endsection

@section('message')
We're having trouble connecting to our services.
<br>
This could be due to your internet connection or our servers.

<div class="network-status" style="margin-top: 1.5rem;">
    <div class="status-item" style="background: #F3F4F6; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-weight: 600; color: #374151;">Internet Connection</span>
                <div style="font-size: 0.875rem; color: #6B7280;" id="connectionStatus">Checking...</div>
            </div>
            <i class="mdi mdi-loading mdi-spin" style="font-size: 1.5rem; color: #6B7280;" id="connectionIcon"></i>
        </div>
    </div>

    <div class="status-item" style="background: #F3F4F6; padding: 1rem; border-radius: 0.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-weight: 600; color: #374151;">Server Status</span>
                <div style="font-size: 0.875rem; color: #6B7280;" id="serverStatus">Checking...</div>
            </div>
            <i class="mdi mdi-loading mdi-spin" style="font-size: 1.5rem; color: #6B7280;" id="serverIcon"></i>
        </div>
    </div>
</div>

@if(app()->environment('local', 'staging'))
    <div class="debug-info" style="margin-top: 1.5rem; padding: 1rem; background: #FEF3C7; border-radius: 0.5rem; text-align: left;">
        <div style="font-weight: 600; margin-bottom: 0.5rem; color: #92400E;">Debug Information:</div>
        <div style="font-family: monospace; font-size: 0.875rem; color: #92400E;">
            <div>Request URL: {{ request()->fullUrl() }}</div>
            <div>Method: {{ request()->method() }}</div>
            @if(isset($exception))
                <div style="margin-top: 0.5rem;">Error: {{ $exception->getMessage() }}</div>
            @endif
        </div>
    </div>
@endif

@if(isset($errorReference))
    <div style="margin-top: 1rem; font-size: 0.875rem; color: #6B7280;">
        Error Reference: <span class="error-reference">{{ $errorReference }}</span>
    </div>
@endif
@endsection

@section('actions')
<button onclick="retryConnection()" class="btn btn-primary">
    <i class="mdi mdi-refresh"></i>
    Try Again
</button>

<button onclick="checkConnectivity()" class="btn btn-secondary">
    <i class="mdi mdi-wifi-check"></i>
    Check Connection
</button>

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
<script>
    let isCheckingConnection = false;
    let retryCount = 0;
    const maxRetries = 3;

    async function checkConnectivity() {
        if (isCheckingConnection) return;
        isCheckingConnection = true;

        // Reset status indicators
        updateConnectionStatus('checking');
        updateServerStatus('checking');

        // Check internet connectivity
        const online = navigator.onLine;
        updateConnectionStatus(online ? 'online' : 'offline');

        if (online) {
            // Check server connectivity
            try {
                const response = await fetch('/api/health-check', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                updateServerStatus(response.ok ? 'online' : 'offline');

                if (response.ok) {
                    // If everything is ok, reload after a short delay
                    setTimeout(() => window.location.reload(), 1000);
                }
            } catch (error) {
                updateServerStatus('offline');
            }
        }

        isCheckingConnection = false;
    }

    function updateConnectionStatus(status) {
        const statusElement = document.getElementById('connectionStatus');
        const iconElement = document.getElementById('connectionIcon');
        
        switch (status) {
            case 'checking':
                statusElement.textContent = 'Checking connection...';
                iconElement.className = 'mdi mdi-loading mdi-spin';
                iconElement.style.color = '#6B7280';
                break;
            case 'online':
                statusElement.textContent = 'Connected';
                iconElement.className = 'mdi mdi-check-circle';
                iconElement.style.color = '#059669';
                break;
            case 'offline':
                statusElement.textContent = 'No connection';
                iconElement.className = 'mdi mdi-close-circle';
                iconElement.style.color = '#DC2626';
                break;
        }
    }

    function updateServerStatus(status) {
        const statusElement = document.getElementById('serverStatus');
        const iconElement = document.getElementById('serverIcon');
        
        switch (status) {
            case 'checking':
                statusElement.textContent = 'Checking server...';
                iconElement.className = 'mdi mdi-loading mdi-spin';
                iconElement.style.color = '#6B7280';
                break;
            case 'online':
                statusElement.textContent = 'Server is responding';
                iconElement.className = 'mdi mdi-check-circle';
                iconElement.style.color = '#059669';
                break;
            case 'offline':
                statusElement.textContent = 'Server is not responding';
                iconElement.className = 'mdi mdi-close-circle';
                iconElement.style.color = '#DC2626';
                break;
        }
    }

    function retryConnection() {
        if (retryCount >= maxRetries) {
            alert('Maximum retry attempts reached. Please try again later.');
            return;
        }
        retryCount++;
        checkConnectivity();
    }

    // Check connectivity on page load
    checkConnectivity();

    // Listen for online/offline events
    window.addEventListener('online', () => updateConnectionStatus('online'));
    window.addEventListener('offline', () => updateConnectionStatus('offline'));

    // Auto-retry connection periodically
    const retryInterval = setInterval(() => {
        if (retryCount < maxRetries) {
            checkConnectivity();
        } else {
            clearInterval(retryInterval);
        }
    }, 10000);
</script>
@endsection
