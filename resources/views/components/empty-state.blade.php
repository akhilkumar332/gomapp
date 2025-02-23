@props([
    'title' => 'No Data Found',
    'message' => 'No records are available at the moment.',
    'icon' => 'bx-folder-open',
    'action' => null,
    'actionLabel' => null,
    'actionUrl' => null,
    'variant' => 'primary'
])

<div {{ $attributes->merge(['class' => 'empty-state text-center']) }}>
    <div class="empty-state-icon mb-4">
        <i class='bx {{ $icon }}'></i>
    </div>
    
    <h3 class="empty-state-title mb-2">{{ $title }}</h3>
    <p class="empty-state-message mb-4">{{ $message }}</p>
    
    @if($action || $actionUrl)
        <div class="empty-state-action">
            @if($actionUrl)
                <a href="{{ $actionUrl }}" class="btn btn-{{ $variant }}">
                    <i class='bx bx-plus me-2'></i>
                    {{ $actionLabel ?? 'Add New' }}
                </a>
            @else
                {{ $action }}
            @endif
        </div>
    @endif
</div>

@pushOnce('styles')
<style>
.empty-state {
    padding: 3rem 1.5rem;
    max-width: 420px;
    margin: 0 auto;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: rgba(71, 35, 217, 0.1);
    color: var(--first-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 2.5rem;
}

.empty-state-title {
    color: #2D3748;
    font-weight: 600;
    font-size: 1.25rem;
}

.empty-state-message {
    color: #6c757d;
    font-size: 0.95rem;
    max-width: 300px;
    margin-left: auto;
    margin-right: auto;
}

.empty-state-action .btn {
    padding: 0.6rem 1.5rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}

.empty-state-action .btn i {
    font-size: 1.25rem;
}

/* Variant styles */
.empty-state[data-variant="warning"] .empty-state-icon {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.empty-state[data-variant="danger"] .empty-state-icon {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.empty-state[data-variant="success"] .empty-state-icon {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

/* Animation */
@keyframes emptyStateIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.empty-state {
    animation: emptyStateIn 0.5s ease-out forwards;
}

/* Icon animation */
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

.empty-state:hover .empty-state-icon {
    animation: iconPulse 1s ease-in-out infinite;
}

/* Responsive styles */
@media (max-width: 576px) {
    .empty-state {
        padding: 2rem 1rem;
    }

    .empty-state-icon {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }

    .empty-state-title {
        font-size: 1.125rem;
    }

    .empty-state-message {
        font-size: 0.875rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .empty-state-title {
        color: #f8f9fa;
    }

    .empty-state-message {
        color: #adb5bd;
    }
}
</style>
@endPushOnce
