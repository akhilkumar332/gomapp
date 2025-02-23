@props([
    'variant' => 'primary',
    'size' => 'md',
    'pill' => false,
    'dot' => false,
    'icon' => null,
    'pulse' => false
])

@php
    $classes = [
        'badge',
        'badge-' . $variant,
        'badge-' . $size,
        $pill ? 'rounded-pill' : 'rounded',
        $dot ? 'badge-dot' : '',
        $pulse ? 'badge-pulse' : ''
    ];
@endphp

<span {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}>
    @if($icon)
        <i class='bx {{ $icon }} {{ $slot->isEmpty() ? '' : 'me-1' }}'></i>
    @endif
    {{ $slot }}
</span>

@pushOnce('styles')
<style>
.badge {
    display: inline-flex;
    align-items: center;
    font-weight: 500;
    line-height: 1;
    white-space: nowrap;
    vertical-align: baseline;
    transition: all 0.2s;
}

/* Size variations */
.badge-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.badge-md {
    font-size: 0.875rem;
    padding: 0.35rem 0.65rem;
}

.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 0.85rem;
}

/* Variant styles */
.badge-primary {
    background-color: rgba(71, 35, 217, 0.1);
    color: var(--first-color);
}

.badge-success {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.badge-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.badge-warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.badge-info {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.badge-light {
    background-color: rgba(248, 249, 250, 0.1);
    color: #f8f9fa;
}

.badge-dark {
    background-color: rgba(52, 58, 64, 0.1);
    color: #343a40;
}

/* Dot style */
.badge-dot {
    position: relative;
    padding-left: 1.5rem;
}

.badge-dot::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background-color: currentColor;
}

/* Pulse animation */
.badge-pulse::before {
    animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
    0% {
        transform: translateY(-50%) scale(1);
        opacity: 1;
    }
    50% {
        transform: translateY(-50%) scale(1.5);
        opacity: 0.5;
    }
    100% {
        transform: translateY(-50%) scale(1);
        opacity: 1;
    }
}

/* Icon styles */
.badge i {
    font-size: 1.1em;
    margin-top: -0.1em;
}

/* Hover effect */
.badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Outline style */
.badge[class*="badge-outline-"] {
    background-color: transparent;
    border: 1px solid currentColor;
}

/* Counter style */
.badge-counter {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(50%, -50%);
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
    line-height: 1;
    min-width: 1rem;
    min-height: 1rem;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .badge[class*="badge-outline-"] {
        border-color: currentColor;
    }

    .badge-light {
        background-color: rgba(248, 249, 250, 0.2);
    }

    .badge-dark {
        background-color: rgba(52, 58, 64, 0.2);
    }
}

/* Status indicators */
.badge-status {
    width: 0.75rem;
    height: 0.75rem;
    padding: 0;
    border-radius: 50%;
}

.badge-status.badge-pulse::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: inherit;
    animation: statusPulse 1.5s infinite;
}

@keyframes statusPulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Group badges */
.badge-group {
    display: inline-flex;
    align-items: center;
}

.badge-group .badge:not(:first-child) {
    margin-left: -0.5rem;
}

/* Stacked badges */
.badge-stack {
    position: relative;
    display: inline-flex;
}

.badge-stack .badge:not(:first-child) {
    position: absolute;
    top: -0.5rem;
    right: -0.5rem;
    transform: scale(0.8);
}
</style>
@endPushOnce
