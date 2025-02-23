@props(['items' => []])

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}">
                <i class='bx bx-home-alt'></i>
            </a>
        </li>
        
        @foreach($items as $item)
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                @if($loop->last || !isset($item['url']))
                    {{ $item['title'] }}
                @else
                    <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

@pushOnce('styles')
<style>
.breadcrumb {
    display: flex;
    flex-wrap: wrap;
    padding: 0;
    margin: 0;
    list-style: none;
    background: transparent;
    font-size: 0.875rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.2s;
    display: flex;
    align-items: center;
}

.breadcrumb-item a:hover {
    color: var(--first-color);
}

.breadcrumb-item i {
    font-size: 1.25rem;
}

.breadcrumb-item + .breadcrumb-item {
    padding-left: 0.75rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "•";
    padding-right: 0.75rem;
    color: #6c757d;
    font-size: 1rem;
}

.breadcrumb-item.active {
    color: var(--first-color);
    font-weight: 500;
}

/* Responsive styles */
@media (max-width: 576px) {
    .breadcrumb {
        font-size: 0.8125rem;
    }

    .breadcrumb-item + .breadcrumb-item {
        padding-left: 0.5rem;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        padding-right: 0.5rem;
    }
}

/* Animation */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.breadcrumb-item {
    animation: slideIn 0.3s ease-out forwards;
}

.breadcrumb-item:nth-child(2) {
    animation-delay: 0.1s;
}

.breadcrumb-item:nth-child(3) {
    animation-delay: 0.2s;
}

.breadcrumb-item:nth-child(4) {
    animation-delay: 0.3s;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.7);
    }

    .breadcrumb-item a:hover {
        color: #fff;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.4);
    }

    .breadcrumb-item.active {
        color: #fff;
    }
}
</style>
@endPushOnce
