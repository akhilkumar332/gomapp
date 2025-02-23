@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1',
    'trigger',
    'header' => null,
    'footer' => null
])

@php
switch ($align) {
    case 'left':
        $alignmentClasses = 'origin-top-left left-0';
        break;
    case 'top':
        $alignmentClasses = 'origin-top';
        break;
    case 'right':
    default:
        $alignmentClasses = 'origin-top-right right-0';
        break;
}

switch ($width) {
    case '48':
        $width = 'w-48';
        break;
    case '96':
        $width = 'w-96';
        break;
}
@endphp

<div class="dropdown" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="dropdown-menu {{ $width }} {{ $alignmentClasses }}"
         @click="open = false">
        @if($header)
            <div class="dropdown-header">
                {{ $header }}
            </div>
        @endif

        <div class="dropdown-content {{ $contentClasses }}">
            {{ $slot }}
        </div>

        @if($footer)
            <div class="dropdown-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>

@pushOnce('styles')
<style>
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    position: absolute;
    z-index: 1000;
    min-width: 10rem;
    margin: 0.5rem 0;
    padding: 0.5rem 0;
    font-size: 0.875rem;
    color: #2D3748;
    text-align: left;
    background-color: #fff;
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
}

.dropdown-header {
    display: block;
    padding: 0.75rem 1rem;
    margin-bottom: 0;
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.dropdown-content {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.dropdown-footer {
    display: block;
    padding: 0.75rem 1rem;
    margin-top: 0;
    font-size: 0.875rem;
    color: #6c757d;
    text-align: center;
    background-color: #f8f9fa;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    border-bottom-right-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

/* Dropdown items */
.dropdown-item {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: #2D3748;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
    transition: all 0.2s;
}

.dropdown-item:hover,
.dropdown-item:focus {
    color: var(--first-color);
    text-decoration: none;
    background-color: rgba(71, 35, 217, 0.05);
}

.dropdown-item.active,
.dropdown-item:active {
    color: #fff;
    text-decoration: none;
    background-color: var(--first-color);
}

.dropdown-item.disabled,
.dropdown-item:disabled {
    color: #adb5bd;
    pointer-events: none;
    background-color: transparent;
}

/* Icons in dropdown items */
.dropdown-item i {
    margin-right: 0.5rem;
    font-size: 1.25rem;
    width: 1.25rem;
    text-align: center;
}

/* Divider */
.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

/* Width variations */
.w-48 {
    width: 12rem;
}

.w-96 {
    width: 24rem;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .dropdown-menu {
        background-color: #2D3748;
        color: #f8f9fa;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.3);
    }

    .dropdown-header {
        color: #adb5bd;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .dropdown-footer {
        color: #adb5bd;
        background-color: #1a202c;
        border-top-color: rgba(255, 255, 255, 0.1);
    }

    .dropdown-item {
        color: #f8f9fa;
    }

    .dropdown-item:hover,
    .dropdown-item:focus {
        background-color: rgba(71, 35, 217, 0.1);
    }

    .dropdown-divider {
        border-top-color: rgba(255, 255, 255, 0.1);
    }
}

/* Animation */
@keyframes dropdownIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-menu {
    animation: dropdownIn 0.2s ease-out;
}

/* Scrollbar styling */
.dropdown-content::-webkit-scrollbar {
    width: 4px;
}

.dropdown-content::-webkit-scrollbar-track {
    background: transparent;
}

.dropdown-content::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 2px;
}

.dropdown-content::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.2);
}
</style>
@endPushOnce
