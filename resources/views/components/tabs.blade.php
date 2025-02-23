@props(['active' => '', 'variant' => 'default'])

<div x-data="{ activeTab: '{{ $active }}' }" class="tabs-wrapper tabs-{{ $variant }}">
    <div class="tabs-header">
        <nav class="nav-tabs" role="tablist">
            {{ $triggers }}
        </nav>
    </div>

    <div class="tabs-content">
        {{ $content }}
    </div>
</div>

@pushOnce('styles')
<style>
.tabs-wrapper {
    margin-bottom: 1.5rem;
}

.tabs-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.nav-tabs {
    display: flex;
    flex-wrap: wrap;
    padding-left: 0;
    margin-bottom: 0;
    list-style: none;
    gap: 1rem;
}

/* Tab triggers */
.nav-tab {
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
}

.nav-tab:hover {
    color: var(--first-color);
}

.nav-tab.active {
    color: var(--first-color);
    border-bottom-color: var(--first-color);
}

.nav-tab i {
    margin-right: 0.5rem;
    font-size: 1.25rem;
}

.nav-tab .badge {
    margin-left: 0.5rem;
}

/* Tab content */
.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
    animation: fadeIn 0.2s ease-in-out;
}

/* Pills variant */
.tabs-pills .nav-tabs {
    border-bottom: 0;
    gap: 0.5rem;
}

.tabs-pills .nav-tab {
    border: none;
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
}

.tabs-pills .nav-tab.active {
    color: #fff;
    background-color: var(--first-color);
}

/* Vertical variant */
.tabs-vertical {
    display: flex;
    gap: 1.5rem;
}

.tabs-vertical .tabs-header {
    border-bottom: 0;
    border-right: 1px solid rgba(0, 0, 0, 0.05);
    margin-bottom: 0;
}

.tabs-vertical .nav-tabs {
    flex-direction: column;
    gap: 0.5rem;
}

.tabs-vertical .nav-tab {
    border-bottom: none;
    border-right: 2px solid transparent;
    margin-bottom: 0;
    margin-right: -1px;
}

.tabs-vertical .nav-tab.active {
    border-right-color: var(--first-color);
}

.tabs-vertical .tabs-content {
    flex: 1;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Icons in tabs */
.nav-tab i {
    transition: transform 0.2s;
}

.nav-tab:hover i {
    transform: scale(1.1);
}

/* Counter badges */
.nav-tab .badge {
    transition: all 0.2s;
}

.nav-tab:hover .badge {
    transform: scale(1.1);
}

/* Responsive styles */
@media (max-width: 768px) {
    .nav-tabs {
        gap: 0.5rem;
    }

    .nav-tab {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .tabs-vertical {
        flex-direction: column;
    }

    .tabs-vertical .tabs-header {
        border-right: 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .tabs-vertical .nav-tabs {
        flex-direction: row;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 0.5rem;
    }

    .tabs-vertical .nav-tab {
        border-right: none;
        border-bottom: 2px solid transparent;
        white-space: nowrap;
    }

    .tabs-vertical .nav-tab.active {
        border-bottom-color: var(--first-color);
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .tabs-header {
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .nav-tab {
        color: #adb5bd;
    }

    .nav-tab:hover {
        color: #fff;
    }

    .nav-tab.active {
        color: #fff;
    }

    .tabs-vertical .tabs-header {
        border-right-color: rgba(255, 255, 255, 0.1);
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab navigation with keyboard
    const tabLists = document.querySelectorAll('[role="tablist"]');
    tabLists.forEach(tabList => {
        const tabs = tabList.querySelectorAll('[role="tab"]');
        let tabFocus = 0;

        tabList.addEventListener('keydown', e => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                tabs[tabFocus].setAttribute('tabindex', -1);
                if (e.key === 'ArrowRight') {
                    tabFocus++;
                    if (tabFocus >= tabs.length) {
                        tabFocus = 0;
                    }
                } else if (e.key === 'ArrowLeft') {
                    tabFocus--;
                    if (tabFocus < 0) {
                        tabFocus = tabs.length - 1;
                    }
                }
                tabs[tabFocus].setAttribute('tabindex', 0);
                tabs[tabFocus].focus();
            }
        });
    });
});
</script>
@endPushOnce
