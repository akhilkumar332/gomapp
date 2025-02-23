@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="pagination-wrapper">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class='bx bx-chevron-left'></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class='bx bx-chevron-left'></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <i class='bx bx-chevron-right'></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class='bx bx-chevron-right'></i>
                    </span>
                </li>
            @endif
        </ul>

        <div class="pagination-info">
            <p class="text-muted">
                Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} entries
            </p>
        </div>
    </nav>
@endif

@pushOnce('styles')
<style>
.pagination-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.pagination {
    margin: 0;
    padding: 0;
    display: flex;
    gap: 0.25rem;
}

.page-item {
    margin: 0;
}

.page-link {
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem !important;
    border: none;
    background-color: #fff;
    color: #6c757d;
    font-weight: 500;
    transition: all 0.2s;
}

.page-link:hover {
    background-color: rgba(71, 35, 217, 0.1);
    color: var(--first-color);
    border: none;
}

.page-item.active .page-link {
    background-color: var(--first-color);
    color: #fff;
    border: none;
}

.page-item.disabled .page-link {
    background-color: #f8f9fa;
    color: #adb5bd;
    border: none;
}

.page-link i {
    font-size: 1.25rem;
}

.pagination-info {
    font-size: 0.875rem;
}

.pagination-info p {
    margin: 0;
}

/* Responsive styles */
@media (max-width: 576px) {
    .pagination-wrapper {
        flex-direction: column-reverse;
        align-items: center;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }

    .page-link {
        width: 32px;
        height: 32px;
        font-size: 0.875rem;
    }

    .pagination-info {
        text-align: center;
    }
}

/* Animation */
.page-link {
    position: relative;
    overflow: hidden;
}

.page-link::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: rgba(71, 35, 217, 0.2);
    border-radius: 50%;
    transform: scale(0);
    transition: transform 0.3s ease-out;
    pointer-events: none;
}

.page-link:active::after {
    transform: scale(2);
    opacity: 0;
    transition: transform 0s, opacity 0.3s;
}

.page-item.active .page-link::after {
    background: rgba(255, 255, 255, 0.2);
}
</style>
@endPushOnce
