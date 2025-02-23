@props([
    'type' => 'info',
    'title' => null,
    'message' => null,
    'autoDismiss' => true,
    'dismissAfter' => 5000
])

@php
    $icons = [
        'success' => 'bx-check-circle',
        'danger' => 'bx-x-circle',
        'warning' => 'bx-error',
        'info' => 'bx-info-circle'
    ];
@endphp

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
    <div class="toast toast-{{ $type }} {{ $autoDismiss ? 'auto-dismiss' : '' }}"
         role="alert"
         aria-live="assertive"
         aria-atomic="true"
         data-bs-delay="{{ $dismissAfter }}"
         data-bs-autohide="{{ $autoDismiss ? 'true' : 'false' }}">
        <div class="toast-header">
            <i class='bx {{ $icons[$type] }} me-2'></i>
            <strong class="me-auto">{{ $title ?? ucfirst($type) }}</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            {{ $message ?? $slot }}
        </div>
    </div>
</div>

@pushOnce('styles')
<style>
.toast-container {
    --bs-toast-spacing: 1.5rem;
}

.toast {
    background-color: #fff;
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
    overflow: hidden;
    margin-bottom: var(--bs-toast-spacing);
    min-width: 300px;
}

.toast-header {
    background-color: transparent;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem;
}

.toast-header i {
    font-size: 1.25rem;
}

.toast-body {
    padding: 1rem;
    color: #6c757d;
}

.toast .btn-close {
    padding: 0.5rem;
    margin: -0.5rem -0.5rem -0.5rem auto;
    opacity: 0.75;
    transition: opacity 0.2s;
}

.toast .btn-close:hover {
    opacity: 1;
}

/* Toast variants */
.toast-success .toast-header {
    color: #28a745;
}

.toast-success .toast-header i {
    color: #28a745;
}

.toast-danger .toast-header {
    color: #dc3545;
}

.toast-danger .toast-header i {
    color: #dc3545;
}

.toast-warning .toast-header {
    color: #ffc107;
}

.toast-warning .toast-header i {
    color: #ffc107;
}

.toast-info .toast-header {
    color: var(--first-color);
}

.toast-info .toast-header i {
    color: var(--first-color);
}

/* Toast animations */
@keyframes toastSlideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes toastSlideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.toast.showing {
    animation: toastSlideIn 0.3s ease-out forwards;
}

.toast.hide {
    animation: toastSlideOut 0.3s ease-in forwards;
}

/* Progress bar for auto-dismiss */
.toast.auto-dismiss::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: rgba(0, 0, 0, 0.1);
}

.toast.auto-dismiss::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: currentColor;
    opacity: 0.2;
    animation: toastProgress linear forwards;
}

@keyframes toastProgress {
    to {
        width: 0%;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .toast {
        background-color: #2D3748;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
    }

    .toast-header {
        color: #fff;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .toast-body {
        color: #cbd5e0;
    }

    .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
}

/* Responsive styles */
@media (max-width: 576px) {
    .toast {
        min-width: auto;
        margin: 0.5rem;
    }

    .toast-container {
        --bs-toast-spacing: 0.5rem;
        right: 0;
        left: 0;
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all toasts
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toastEl => {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();

        // Add animation duration to auto-dismiss toasts
        if (toastEl.classList.contains('auto-dismiss')) {
            const duration = toastEl.getAttribute('data-bs-delay') || 5000;
            toastEl.style.setProperty('--toast-duration', `${duration}ms`);
            toastEl.style.animationDuration = `${duration}ms`;
        }
    });
});
</script>
@endPushOnce
