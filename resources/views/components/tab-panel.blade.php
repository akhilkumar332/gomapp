@props(['id', 'active' => false])

<div id="{{ $id }}"
     role="tabpanel"
     tabindex="0"
     x-show="activeTab === '{{ $id }}'"
     x-transition:enter="transition ease-in-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in-out duration-300"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform translate-y-2"
     {{ $attributes->merge(['class' => 'tab-panel ' . ($active ? 'active' : '')]) }}>
    {{ $slot }}
</div>

@pushOnce('styles')
<style>
.tab-panel {
    outline: none;
}

.tab-panel:focus {
    outline: none;
}

/* Animation classes */
.tab-panel.fade {
    transition: opacity 0.15s linear;
}

.tab-panel.fade:not(.active) {
    opacity: 0;
}

/* Loading state */
.tab-panel.loading {
    position: relative;
    min-height: 200px;
}

.tab-panel.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 30px;
    height: 30px;
    margin: -15px 0 0 -15px;
    border: 2px solid rgba(71, 35, 217, 0.2);
    border-top-color: var(--first-color);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Responsive height adjustment */
.tab-panel.responsive-height {
    height: 0;
    overflow: hidden;
    transition: height 0.3s ease-in-out;
}

.tab-panel.responsive-height.active {
    height: auto;
}

/* Content fade in */
.tab-panel .tab-content {
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease-in-out;
}

.tab-panel.active .tab-content {
    opacity: 1;
    transform: translateY(0);
}

/* Lazy loading placeholder */
.tab-panel.lazy-loading {
    position: relative;
    overflow: hidden;
}

.tab-panel.lazy-loading::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.6) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .tab-panel.lazy-loading::before {
        background: linear-gradient(
            90deg,
            rgba(45, 55, 72, 0) 0%,
            rgba(45, 55, 72, 0.6) 50%,
            rgba(45, 55, 72, 0) 100%
        );
    }
}

/* Accessibility focus styles */
.tab-panel:focus-visible {
    outline: 2px solid var(--first-color);
    outline-offset: 2px;
}

/* Print styles */
@media print {
    .tab-panel {
        display: block !important;
        opacity: 1 !important;
        page-break-inside: avoid;
    }

    .tab-panel::before {
        content: attr(aria-label);
        font-weight: bold;
        margin-bottom: 1rem;
        display: block;
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle keyboard navigation within tab panels
    const tabPanels = document.querySelectorAll('[role="tabpanel"]');
    tabPanels.forEach(panel => {
        panel.addEventListener('keydown', e => {
            if (e.key === 'Tab' && !e.shiftKey) {
                const focusableElements = panel.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                if (focusableElements.length) {
                    const lastElement = focusableElements[focusableElements.length - 1];
                    if (document.activeElement === lastElement) {
                        const nextPanel = panel.nextElementSibling;
                        if (nextPanel && nextPanel.hasAttribute('role') && nextPanel.getAttribute('role') === 'tabpanel') {
                            e.preventDefault();
                            nextPanel.focus();
                        }
                    }
                }
            }
        });
    });
});
</script>
@endPushOnce
