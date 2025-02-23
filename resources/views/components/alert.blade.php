@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null,
    'title' => null
])

@php
    $icons = [
        'success' => 'bx-check-circle',
        'danger' => 'bx-x-circle',
        'warning' => 'bx-error',
        'info' => 'bx-info-circle'
    ];

    $selectedIcon = $icon ?? $icons[$type];
@endphp

<div {{ $attributes->merge(['class' => "alert alert-$type"]) }} 
     role="alert"
     x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-2">
    
    <div class="alert-content">
        <div class="alert-icon">
            <i class='bx {{ $selectedIcon }}'></i>
        </div>
        
        <div class="alert-body">
            @if($title)
                <h4 class="alert-title">{{ $title }}</h4>
            @endif
            <div class="alert-text">
                {{ $slot }}
            </div>
        </div>

        @if($dismissible)
            <button type="button" 
                    class="btn-close" 
                    data-bs-dismiss="alert" 
                    aria-label="Close"
                    @click="show = false">
            </button>
        @endif
    </div>
</div>

@pushOnce('styles')
<style>
.alert {
    position: relative;
    padding: 1rem;
    margin-bottom: 1rem;
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.alert-content {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.alert-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.alert-body {
    flex-grow: 1;
}

.alert-title {
    margin: 0 0 0.25rem;
    font-size: 1rem;
    font-weight: 600;
}

.alert-text {
    color: inherit;
    opacity: 0.9;
}

.btn-close {
    padding: 0.5rem;
    margin: -0.5rem -0.5rem -0.5rem auto;
    opacity: 0.75;
    transition: opacity 0.2s;
}

.btn-close:hover {
    opacity: 1;
}

/* Alert variants */
.alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.alert-info {
    background-color: rgba(71, 35, 217, 0.1);
    color: var(--first-color);
}

/* Alert with border */
.alert-bordered {
    border-left: 4px solid;
}

.alert-bordered.alert-success {
    border-left-color: #28a745;
}

.alert-bordered.alert-danger {
    border-left-color: #dc3545;
}

.alert-bordered.alert-warning {
    border-left-color: #ffc107;
}

.alert-bordered.alert-info {
    border-left-color: var(--first-color);
}

/* Alert animations */
@keyframes alertSlideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert {
    animation: alertSlideIn 0.3s ease-out forwards;
}

/* Icon animations */
@keyframes iconPulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.alert:hover .alert-icon i {
    animation: iconPulse 1s ease-in-out infinite;
}

/* Responsive styles */
@media (max-width: 576px) {
    .alert {
        padding: 0.75rem;
    }

    .alert-title {
        font-size: 0.9375rem;
    }

    .alert-text {
        font-size: 0.875rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .alert {
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .alert-text {
        opacity: 0.8;
    }

    .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds if they have data-auto-dismiss attribute
    const autoDismissAlerts = document.querySelectorAll('.alert[data-auto-dismiss]');
    autoDismissAlerts.forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            } else {
                alert.remove();
            }
        }, 5000);
    });
});
</script>
@endPushOnce
