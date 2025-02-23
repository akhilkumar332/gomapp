@props(['action' => null])

<div class="filter-section mb-4">
    <form action="{{ $action ?? request()->url() }}" method="GET" class="filter-form">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class='bx bx-filter-alt me-2'></i>
                    Filters
                </h5>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class='bx bx-search me-1'></i>
                        Apply Filters
                    </button>
                    <a href="{{ request()->url() }}" class="btn btn-light btn-sm ms-2">
                        <i class='bx bx-reset me-1'></i>
                        Reset
                    </a>
                    <button type="button" class="btn btn-link btn-sm text-muted filter-toggle ms-2" data-bs-toggle="collapse" data-bs-target="#filterContent">
                        <i class='bx bx-chevron-down'></i>
                    </button>
                </div>
            </div>
            
            <div class="collapse {{ request()->hasAny(array_keys(request()->query())) ? 'show' : '' }}" id="filterContent">
                <div class="card-body">
                    <div class="row g-3">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@pushOnce('styles')
<style>
.filter-section .card {
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.filter-section .card-header {
    background-color: #fff;
    padding: 1rem 1.5rem;
}

.filter-section .card-title {
    font-size: 1rem;
    display: flex;
    align-items: center;
    color: #2D3748;
}

.filter-section .card-title i {
    font-size: 1.25rem;
    color: var(--first-color);
}

.filter-section .card-body {
    padding: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.filter-actions .btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.filter-actions .btn i {
    font-size: 1.1rem;
}

.filter-toggle {
    padding: 0;
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.filter-toggle:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.filter-toggle i {
    font-size: 1.25rem;
    transition: transform 0.2s;
}

.filter-toggle[aria-expanded="true"] i {
    transform: rotate(180deg);
}

/* Quick filter tags */
.filter-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0 1.5rem;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--first-color);
    background-color: rgba(71, 35, 217, 0.1);
    border-radius: 1rem;
}

.filter-tag .remove {
    margin-left: 0.5rem;
    width: 16px;
    height: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(71, 35, 217, 0.2);
    color: var(--first-color);
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-tag .remove:hover {
    background-color: var(--first-color);
    color: #fff;
}

/* Date range picker customization */
.daterangepicker {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
}

.daterangepicker .ranges li.active {
    background-color: var(--first-color);
}

.daterangepicker td.active, 
.daterangepicker td.active:hover {
    background-color: var(--first-color);
}

/* Responsive */
@media (max-width: 768px) {
    .filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .filter-actions .btn {
        flex: 1;
    }

    .filter-toggle {
        flex: 0 0 auto;
    }
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle filter form submission
    const filterForm = document.querySelector('.filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            // Remove empty fields before submitting
            const formData = new FormData(this);
            for (const pair of formData.entries()) {
                if (!pair[1].trim()) {
                    const input = this.querySelector(`[name="${pair[0]}"]`);
                    if (input) {
                        input.disabled = true;
                    }
                }
            }
        });
    }

    // Handle filter tag removal
    const filterTags = document.querySelectorAll('.filter-tag .remove');
    filterTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const param = this.dataset.param;
            const url = new URL(window.location.href);
            url.searchParams.delete(param);
            window.location.href = url.toString();
        });
    });
});
</script>
@endPushOnce
