@extends('errors.layout')

@section('code', '422')

@section('title', 'Validation Error')

@section('icon')
<i class="mdi mdi-alert-circle icon"></i>
@endsection

@section('message')
The submitted data was invalid.
<br>
Please correct the errors and try again.

@if($errors->any())
    <div class="validation-errors" style="margin-top: 1.5rem; text-align: left;">
        <div style="font-weight: 600; color: #991B1B; margin-bottom: 0.5rem;">
            Please fix the following errors:
        </div>
        <ul style="list-style-type: none; padding: 0; margin: 0;">
            @foreach($errors->all() as $error)
                <div style="background-color: #FEE2E2; color: #991B1B; padding: 0.75rem; margin-bottom: 0.5rem; border-radius: 0.375rem; font-size: 0.875rem;">
                    <i class="mdi mdi-alert-circle" style="margin-right: 0.5rem;"></i>
                    {{ $error }}
                </div>
            @endforeach
        </ul>
    </div>
@endif

@if(app()->environment('local', 'staging'))
    <div class="debug-info" style="margin-top: 1.5rem; padding: 1rem; background: #F3F4F6; border-radius: 0.5rem; text-align: left;">
        <div style="font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Request Details:</div>
        <div style="font-family: monospace; font-size: 0.875rem; color: #4B5563;">
            <div>Method: {{ request()->method() }}</div>
            <div>Path: {{ request()->path() }}</div>
            @if(app()->environment('local'))
                <div style="margin-top: 0.5rem;">
                    <div style="font-weight: 600; color: #374151;">Submitted Data:</div>
                    <pre style="background: #E5E7EB; padding: 0.5rem; border-radius: 0.25rem; margin-top: 0.25rem; overflow-x: auto;">{{ json_encode(request()->except(['password', 'password_confirmation']), JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>
    </div>
@endif
@endsection

@section('actions')
<button onclick="window.history.back()" class="btn btn-primary">
    <i class="mdi mdi-arrow-left"></i>
    Go Back & Fix Errors
</button>

@auth
    @if(auth()->user()->isAdmin())
        <button onclick="showValidationDetails()" class="btn btn-secondary">
            <i class="mdi mdi-code-tags"></i>
            Show Technical Details
        </button>
    @endif
@endauth
@endsection

@section('scripts')
<script>
    // Highlight form fields with errors
    document.addEventListener('DOMContentLoaded', function() {
        const errors = @json($errors->messages());
        
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                input.style.borderColor = '#DC2626';
                input.style.backgroundColor = '#FEF2F2';
                
                // Add error message below the field
                const errorMessage = document.createElement('div');
                errorMessage.className = 'error-message';
                errorMessage.style.color = '#DC2626';
                errorMessage.style.fontSize = '0.875rem';
                errorMessage.style.marginTop = '0.25rem';
                errorMessage.innerHTML = `<i class="mdi mdi-alert-circle"></i> ${errors[field][0]}`;
                
                input.parentNode.insertBefore(errorMessage, input.nextSibling);
                
                // Add event listener to remove error styling on input
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    this.style.borderColor = '';
                    this.style.backgroundColor = '';
                    if (errorMessage.parentNode) {
                        errorMessage.parentNode.removeChild(errorMessage);
                    }
                });
            }
        });
    });

    @auth
        @if(auth()->user()->isAdmin())
            function showValidationDetails() {
                const details = {
                    errors: @json($errors->messages()),
                    request: {
                        method: '{{ request()->method() }}',
                        path: '{{ request()->path() }}',
                        url: '{{ request()->url() }}',
                        timestamp: '{{ now() }}',
                        data: @json(request()->except(['password', 'password_confirmation']))
                    }
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
        @endif
    @endauth
</script>
@endsection
