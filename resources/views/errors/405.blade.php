@extends('errors.layout')

@section('code', '405')

@section('title', 'Method Not Allowed')

@section('icon')
<i class="mdi mdi-block-helper icon"></i>
@endsection

@section('message')
The requested method is not supported for this route.
<br>
Please check your request method and try again.

@if(isset($exception) && app()->environment('local', 'staging'))
    <div class="debug-info" style="margin-top: 1rem; padding: 1rem; background: #F3F4F6; border-radius: 0.5rem; text-align: left;">
        <div style="font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Request Details:</div>
        <div style="font-family: monospace; font-size: 0.875rem; color: #4B5563;">
            <div>Method: {{ request()->method() }}</div>
            <div>Path: {{ request()->path() }}</div>
            @if($exception->getHeaders()['Allow'] ?? false)
                <div style="margin-top: 0.5rem; color: #059669;">
                    Allowed Methods: {{ $exception->getHeaders()['Allow'] }}
                </div>
            @endif
        </div>
    </div>
@endif

@auth
    @if(auth()->user()->isAdmin())
        <div class="admin-info" style="margin-top: 1rem; padding: 1rem; background: #EDE9FE; border-radius: 0.5rem; text-align: left;">
            <div style="font-weight: 600; margin-bottom: 0.5rem; color: var(--primary-color);">Admin Information:</div>
            <div style="font-size: 0.875rem; color: #6B7280;">
                This error typically occurs when:
                <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                    <li>A form submission uses the wrong HTTP method</li>
                    <li>An API endpoint is called with an unsupported method</li>
                    <li>CSRF protection is bypassed incorrectly</li>
                </ul>
            </div>
        </div>
    @endif
@endauth
@endsection

@section('actions')
<button onclick="window.history.back()" class="btn btn-primary">
    <i class="mdi mdi-arrow-left"></i>
    Go Back
</button>

@auth
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">
            <i class="mdi mdi-history"></i>
            View Request Logs
        </a>

        <div style="margin-top: 1rem;">
            <button onclick="showRequestDetails()" class="btn btn-secondary" style="background-color: #F3F4F6; color: #374151;">
                <i class="mdi mdi-code-tags"></i>
                Show Technical Details
            </button>
        </div>
    @endif
@endauth
@endsection

@section('scripts')
@auth
    @if(auth()->user()->isAdmin())
        <script>
            function showRequestDetails() {
                const details = {
                    method: '{{ request()->method() }}',
                    path: '{{ request()->path() }}',
                    url: '{{ request()->url() }}',
                    @if(isset($exception) && isset($exception->getHeaders()['Allow']))
                    allowedMethods: '{{ $exception->getHeaders()['Allow'] }}',
                    @endif
                    timestamp: '{{ now() }}',
                    userAgent: '{{ request()->userAgent() }}',
                    ip: '{{ request()->ip() }}'
                };

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
                content.style.maxWidth = '600px';
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
        </script>
    @endif
@endauth
@endsection
