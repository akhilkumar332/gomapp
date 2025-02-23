@props(['title', 'subtitle' => null, 'actions' => null])

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">
                {{ $subtitle ?? Str::title(request()->segment(1)) }}
            </div>
            <h2 class="page-title">
                {{ $title }}
            </h2>
        </div>
        @if($actions)
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    {{ $actions }}
                </div>
            </div>
        @endif
    </div>
</div>

@pushOnce('styles')
<style>
.page-header {
    display: flex;
    flex-wrap: wrap;
    padding: 0;
    position: relative;
    min-height: 50px;
}

.page-pretitle {
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.page-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #2D3748;
    line-height: 1.5;
}

.btn-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.btn-list > .btn {
    display: inline-flex;
    align-items: center;
}

.btn-list > .btn i {
    margin-right: 0.5rem;
    font-size: 1.25rem;
}

/* Responsive styles */
@media (max-width: 768px) {
    .page-header .row {
        flex-direction: column;
        gap: 1rem;
    }

    .page-header .col-auto {
        width: 100%;
    }

    .btn-list {
        justify-content: flex-start;
    }

    .btn-list > .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Breadcrumb styles */
.breadcrumb {
    padding: 0;
    margin: 0;
    background: transparent;
    font-size: 0.875rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "•";
    color: #6c757d;
}

.breadcrumb-item.active {
    color: var(--first-color);
}

/* Action buttons */
.page-header .btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.page-header .btn:hover {
    transform: translateY(-1px);
}

.page-header .btn i {
    font-size: 1.25rem;
}

/* Stats in header */
.page-header .stats {
    display: flex;
    gap: 2rem;
    margin-top: 1rem;
}

.page-header .stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-header .stat-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.page-header .stat-details h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    color: #2D3748;
}

.page-header .stat-details p {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
}

/* Quick filters */
.page-header .quick-filters {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.page-header .quick-filter {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6c757d;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.2s;
}

.page-header .quick-filter:hover,
.page-header .quick-filter.active {
    color: var(--first-color);
    background-color: rgba(71, 35, 217, 0.1);
    border-color: transparent;
}
</style>
@endPushOnce
