@extends('errors.layout')

@section('code', $statusCode)

@section('title')
    Webhook Error - {{ ucfirst($provider ?? 'Unknown') }}
@endsection

@section('icon')
@php
    $iconClass = match($provider) {
        'firebase' => 'mdi-firebase',
        'stripe' => 'mdi-currency-usd',
        'twilio' => 'mdi-phone',
        default => 'mdi-webhook'
    };
@endphp
<i class="mdi {{ $iconClass }} icon"></i>
@endsection

@section('message')
{{ $exception->getMessage() }}
<br>
@if($event)
    Event: <span class="font-mono">{{ $event }}</span>
@endif

@if(app()->environment('local', 'staging'))
    <div class="debug-info" style="margin-top: 1.5rem; text-align: left;">
        <div class="webhook-details" style="background: #F3F4F6; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                Webhook Details:
            </div>
            <div style="font-family: monospace; font-size: 0.875rem; color: #4B5563;">
                <div>Provider: {{ $provider ?? 'Unknown' }}</div>
                @if($event)
                    <div>Event: {{ $event }}</div>
                @endif
                <div>Status Code: {{ $statusCode }}</div>
                <div>Timestamp: {{ now()->format('Y-m-d H:i:s') }}</div>
            </div>
        </div>

        @if(!empty($context))
            <div class="context-data" style="background: #FEF3C7; padding: 1rem; border-radius: 0.5rem;">
                <div style="font-weight: 600; color: #92400E; margin-bottom: 0.5rem;">
                    Additional Context:
                </div>
                <pre style="font-family: monospace; font-size: 0.875rem; color: #92400E; white-space: pre-wrap;">{{ json_encode($context, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if(isset($exception->getTrace()[0]))
            <div class="stack-trace" style="margin-top: 1rem; background: #EEF2FF; padding: 1rem; border-radius: 0.5rem;">
                <div style="font-weight: 600; color: #3730A3; margin-bottom: 0.5rem;">
                    Stack Trace:
                </div>
                <div style="font-family: monospace; font-size: 0.875rem; color: #3730A3;">
                    {{ $exception->getFile() }}:{{ $exception->getLine() }}
                </div>
            </div>
        @endif
    </div>
@endif
@endsection

@section('actions')
<button onclick="window.location.reload()" class="btn btn-primary">
    <i class="mdi mdi-refresh"></i>
    Retry Webhook
</button>

@auth
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.webhooks.logs') }}" class="btn btn-secondary">
            <i class="mdi mdi-history"></i>
            View Webhook Logs
        </a>

        <button onclick="showWebhookDetails()" class="btn btn-secondary">
            <i class="mdi mdi-code-json"></i>
            Show Technical Details
        </button>
    @endif
@endauth
@endsection

@section('scripts')
@auth
    @if(auth()->user()->isAdmin())
        <script>
            function showWebhookDetails() {
                const details = @json([
                    'provider' => $provider,
                    'event' => $event,
                    'status_code' => $statusCode,
                    'message' => $exception->getMessage(),
                    'context' => $context,
                    'timestamp' => now()->toIso8601String(),
                    'environment' => app()->environment(),
                    'debug' => [
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => array_slice($exception->getTrace(), 0, 5)
                    ]
                ]);

                const modal = document.createElement('div');
                modal.style.position = 'fixed';
                modal.style.top = '0';
                modal.style.left = '0';
                modal.style.width = '100%';
                modal.style.height = '100%';
                modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                modal.style.display = 'flex';
                modal.style.alignItems = 'center';
                modal.style.justifyContent = 'center';
                modal.style.zIndex = '9999';

                const content = document.createElement('div');
                content.style.backgroundColor = 'white';
                content.style.padding = '2rem';
                content.style.borderRadius = '0.5rem';
                content.style.maxWidth = '800px';
                content.style.width = '90%';
                content.style.maxHeight = '90vh';
                content.style.overflow = 'auto';
                content.style.position = 'relative';

                const close = document.createElement('button');
                close.innerHTML = '<i class="mdi mdi-close"></i>';
                close.style.position = 'absolute';
                close.style.top = '1rem';
                close.style.right = '1rem';
                close.style.border = 'none';
                close.style.background = 'none';
                close.style.cursor = 'pointer';
                close.style.fontSize = '1.5rem';
                close.style.color = '#6B7280';
                close.onclick = () => modal.remove();

                const pre = document.createElement('pre');
                pre.style.whiteSpace = 'pre-wrap';
                pre.style.fontFamily = 'monospace';
                pre.style.fontSize = '0.875rem';
                pre.style.color = '#374151';
                pre.textContent = JSON.stringify(details, null, 2);

                content.appendChild(close);
                content.appendChild(pre);
                modal.appendChild(content);
                document.body.appendChild(modal);

                modal.onclick = (e) => {
                    if (e.target === modal) modal.remove();
                };
            }

            // Auto-refresh for certain error types
            @if(in_array($statusCode, [502, 503, 504]))
                setTimeout(() => window.location.reload(), 5000);
            @endif
        </script>
    @endif
@endauth
@endsection
