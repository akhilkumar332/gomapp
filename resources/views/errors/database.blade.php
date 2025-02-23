@extends('errors.layout')

@section('code', 'DB')

@section('title', 'Database Connection Error')

@section('icon')
<i class="mdi mdi-database-off icon"></i>
@endsection

@section('message')
We're having trouble connecting to our database.
<br>
Our technical team has been notified and is working to restore service.

@if(app()->environment('local', 'staging'))
    <div class="debug-info" style="margin-top: 1.5rem; padding: 1rem; background: #F3F4F6; border-radius: 0.5rem; text-align: left;">
        <div style="font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Connection Details:</div>
        <div style="font-family: monospace; font-size: 0.875rem; color: #4B5563;">
            <div>Driver: {{ config('database.default') }}</div>
            <div>Host: {{ config('database.connections.' . config('database.default') . '.host') }}</div>
            <div>Database: {{ config('database.connections.' . config('database.default') . '.database') }}</div>
            @if(isset($exception))
                <div style="margin-top: 0.5rem; color: #DC2626;">
                    Error: {{ $exception->getMessage() }}
                </div>
            @endif
        </div>
    </div>

    <div class="troubleshooting" style="margin-top: 1rem; padding: 1rem; background: #ECFDF5; border-radius: 0.5rem; text-align: left;">
        <div style="font-weight: 600; margin-bottom: 0.5rem; color: #065F46;">Troubleshooting Steps:</div>
        <ul style="list-style-type: disc; margin-left: 1.5rem; font-size: 0.875rem; color: #065F46;">
            <li>Check if MySQL service is running</li>
            <li>Verify database credentials in .env file</li>
            <li>Ensure database exists and is accessible</li>
            <li>Check network connectivity to database server</li>
            <li>Verify firewall settings and port access</li>
        </ul>
    </div>
@endif

@if(isset($errorReference))
    <div style="margin-top: 1rem; font-size: 0.875rem; color: #6B7280;">
        Error Reference: <span class="error-reference">{{ $errorReference }}</span>
    </div>
@endif
@endsection

@section('actions')
<button onclick="window.location.reload()" class="btn btn-primary">
    <i class="mdi mdi-refresh"></i>
    Try Again
</button>

@if(app()->environment('local'))
    <button onclick="checkDatabaseConnection()" class="btn btn-secondary">
        <i class="mdi mdi-database-check"></i>
        Check Connection
    </button>
@endif

@auth
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">
            <i class="mdi mdi-history"></i>
            View System Logs
        </a>
    @endif
@endauth

<a href="mailto:{{ config('app.support_email', 'support@example.com') }}" class="btn btn-secondary">
    <i class="mdi mdi-email"></i>
    Contact Support
</a>
@endsection

@section('scripts')
@if(app()->environment('local'))
<script>
    async function checkDatabaseConnection() {
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Checking...';

        try {
            const response = await fetch('/api/health-check/database', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            const statusDiv = document.createElement('div');
            statusDiv.style.marginTop = '1rem';
            statusDiv.style.padding = '1rem';
            statusDiv.style.borderRadius = '0.5rem';
            statusDiv.style.fontSize = '0.875rem';

            if (response.ok) {
                statusDiv.style.backgroundColor = '#ECFDF5';
                statusDiv.style.color = '#065F46';
                statusDiv.innerHTML = '<i class="mdi mdi-check-circle"></i> Database connection successful';
                
                // Reload page after 1 second
                setTimeout(() => window.location.reload(), 1000);
            } else {
                statusDiv.style.backgroundColor = '#FEE2E2';
                statusDiv.style.color = '#991B1B';
                statusDiv.innerHTML = `<i class="mdi mdi-alert-circle"></i> ${result.message || 'Connection failed'}`;
            }

            document.querySelector('.actions').insertAdjacentElement('beforebegin', statusDiv);
        } catch (error) {
            console.error('Error checking database connection:', error);
            
            const errorDiv = document.createElement('div');
            errorDiv.style.marginTop = '1rem';
            errorDiv.style.padding = '1rem';
            errorDiv.style.backgroundColor = '#FEE2E2';
            errorDiv.style.color = '#991B1B';
            errorDiv.style.borderRadius = '0.5rem';
            errorDiv.style.fontSize = '0.875rem';
            errorDiv.innerHTML = '<i class="mdi mdi-alert-circle"></i> Failed to check database connection';
            
            document.querySelector('.actions').insertAdjacentElement('beforebegin', errorDiv);
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    // Auto-refresh check
    let checkCount = 0;
    const maxChecks = 5;
    const checkInterval = 10000; // 10 seconds

    async function autoCheckConnection() {
        if (checkCount >= maxChecks) return;
        
        try {
            const response = await fetch('/api/health-check/database', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                window.location.reload();
            } else {
                checkCount++;
                setTimeout(autoCheckConnection, checkInterval);
            }
        } catch (error) {
            checkCount++;
            setTimeout(autoCheckConnection, checkInterval);
        }
    }

    setTimeout(autoCheckConnection, checkInterval);
</script>
@endif
@endsection
