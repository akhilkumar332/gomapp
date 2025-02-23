@props([
    'id',
    'active' => false,
    'icon' => null,
    'badge' => null,
    'disabled' => false
])

<button type="button"
        role="tab"
        id="tab-{{ $id }}"
        aria-controls="{{ $id }}"
        aria-selected="{{ $active ? 'true' : 'false' }}"
        tabindex="{{ $active ? '0' : '-1' }}"
        x-on:click="activeTab = '{{ $id }}'"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge([
            'class' => 'nav-tab ' . ($active ? 'active' : '') . ($disabled ? ' disabled' : '')
        ]) }}>
    
    @if($icon)
        <i class='bx {{ $icon }}'></i>
    @endif
    
    <span class="tab-text">{{ $slot }}</span>
    
    @if($badge)
        <span class="badge bg-{{ $badge['variant'] ?? 'primary' }} rounded-pill">
            {{ $badge['text'] }}
        </span>
    @endif
</button>

@pushOnce('styles')
<style>
.nav-tab {
    position: relative;
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1rem;
    font-weight: 500;
    color: #6c757d;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
    -webkit-user-select: none;
}

.nav-tab:hover:not(.disabled) {
    color: var(--first-color);
}

.nav-tab.active:not(.disabled) {
    color: var(--first-color);
    border-bottom-color: var(--first-color);
}

.nav-tab.disabled {
    color: #adb5bd;
    cursor: not-allowed;
    pointer-events: none;
}

/* Icon styles */
.nav-tab i {
    font-size: 1.25rem;
    margin-right: 0.5rem;
    transition: transform 0.2s;
}

.nav-tab:hover:not(.disabled) i {
    transform: scale(1.1);
}

/* Badge styles */
.nav-tab .badge {
    margin-left: 0.5rem;
    font-size: 0.75rem;
    padding: 0.25em 0.6em;
    transition: all 0.2s;
}

.nav-tab:hover:not(.disabled) .badge {
    transform: scale(1.1);
}

/* Focus styles */
.nav-tab:focus {
    outline: none;
}

.nav-tab:focus-visible {
    box-shadow: 0 0 0 2px rgba(71, 35, 217, 0.25);
    border-radius: 0.25rem;
}

/* Loading state */
.nav-tab.loading {
    position: relative;
    pointer-events: none;
}

.nav-tab.loading .tab-text {
    opacity: 0;
}

.nav-tab.loading::after {
    content: '';
    position: absolute;
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(71, 35, 217, 0.2);
    border-top-color: var(--first-color);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

/* Notification dot */
.nav-tab[data-notification]::after {
    content: '';
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 6px;
    height: 6px;
    background-color: #dc3545;
    border-radius: 50%;
}

/* Animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Hover effect */
.nav-tab::before {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    width: 0;
    height: 2px;
    background-color: var(--first-color);
    transition: all 0.3s;
    transform: translateX(-50%);
}

.nav-tab:hover:not(.disabled)::before {
    width: 100%;
}

/* Active state animation */
.nav-tab.active::before {
    width: 100%;
    opacity: 1;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .nav-tab {
        color: #adb5bd;
    }

    .nav-tab:hover:not(.disabled),
    .nav-tab.active:not(.disabled) {
        color: #fff;
    }

    .nav-tab.disabled {
        color: #6c757d;
    }
}

/* Responsive styles */
@media (max-width: 768px) {
    .nav-tab {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .nav-tab i {
        font-size: 1.1rem;
    }

    .nav-tab .badge {
        font-size: 0.7rem;
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle keyboard navigation between tabs
    const tabs = document.querySelectorAll('[role="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('keydown', e => {
            const targetTab = e.target;
            const parentList = targetTab.parentNode;
            const tabs = [...parentList.querySelectorAll('[role="tab"]:not(.disabled)')];
            const index = tabs.indexOf(targetTab);

            let newTab;
            switch (e.key) {
                case 'ArrowLeft':
                    newTab = tabs[index - 1] || tabs[tabs.length - 1];
                    break;
                case 'ArrowRight':
                    newTab = tabs[index + 1] || tabs[0];
                    break;
                case 'Home':
                    newTab = tabs[0];
                    break;
                case 'End':
                    newTab = tabs[tabs.length - 1];
                    break;
                default:
                    return;
            }

            if (newTab) {
                e.preventDefault();
                newTab.click();
                newTab.focus();
            }
        });
    });
});
</script>
@endPushOnce
