@props([
    'size' => 'md', 
    'variant' => 'primary', 
    'type' => 'border',
    'centered' => false,
    'fullscreen' => false,
    'text' => null
])

@php
    $sizeClass = match($size) {
        'sm' => 'spinner-' . $type . '-sm',
        'lg' => 'spinner-' . $type . '-lg',
        default => 'spinner-' . $type
    };

    $classes = [
        $sizeClass,
        'text-' . $variant,
        $centered ? 'mx-auto d-block' : '',
        $fullscreen ? 'position-fixed' : ''
    ];
@endphp

<div {{ $attributes->merge(['class' => $fullscreen ? 'loading-overlay' : '']) }}>
    <div @class($classes) role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    @if($text)
        <p @class(['text-center mt-3', 'text-' . $variant])>{{ $text }}</p>
    @endif
</div>

@if($fullscreen)
    @pushOnce('styles')
    <style>
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-overlay .spinner-border,
    .loading-overlay .spinner-grow {
        width: 3rem;
        height: 3rem;
    }

    /* Spinner animations */
    @keyframes spinner-pulse {
        0% {
            transform: scale(0.75);
            opacity: 0.2;
        }
        50% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(0.75);
            opacity: 0.2;
        }
    }

    .spinner-grow {
        animation: spinner-pulse 1s ease-in-out infinite;
    }

    /* Spinner variants */
    .text-primary {
        color: var(--first-color) !important;
    }

    .spinner-border.text-primary {
        border-color: var(--first-color);
        border-right-color: transparent;
    }

    /* Loading text animation */
    .loading-overlay p {
        animation: fadeInUp 0.5s ease-out forwards;
        opacity: 0;
        transform: translateY(10px);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Size variations */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }

    .spinner-border-lg {
        width: 4rem;
        height: 4rem;
        border-width: 0.25em;
    }

    .spinner-grow-sm {
        width: 1rem;
        height: 1rem;
    }

    .spinner-grow-lg {
        width: 4rem;
        height: 4rem;
    }

    /* Inline spinner */
    .btn .spinner-border-sm,
    .btn .spinner-grow-sm {
        margin-right: 0.5rem;
    }

    /* Centered spinner */
    .mx-auto.d-block {
        margin: 2rem auto;
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .loading-overlay {
            background: rgba(33, 37, 41, 0.9);
        }

        .loading-overlay p {
            color: #fff;
        }
    }
    </style>
    @endPushOnce
@endif

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle loading state for forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                const spinner = document.createElement('span');
                spinner.className = 'spinner-border spinner-border-sm me-2';
                spinner.setAttribute('role', 'status');
                spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
                
                submitBtn.prepend(spinner);
                submitBtn.disabled = true;
            }
        });
    });

    // Handle loading state for buttons with data-loading attribute
    const loadingBtns = document.querySelectorAll('[data-loading]');
    loadingBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.disabled) {
                const spinner = document.createElement('span');
                spinner.className = 'spinner-border spinner-border-sm me-2';
                spinner.setAttribute('role', 'status');
                spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
                
                this.prepend(spinner);
                this.disabled = true;
            }
        });
    });
});
</script>
@endPushOnce
