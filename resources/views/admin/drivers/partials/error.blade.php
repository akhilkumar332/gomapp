<div class="text-center py-5">
    <div class="text-danger mb-3">
        <i class="bx bx-error-circle display-4"></i>
    </div>
    <h5 class="text-danger">Error Loading Drivers</h5>
    <p class="text-muted mb-3">An unexpected error occurred while loading the drivers list.</p>
    <div class="d-flex justify-content-center gap-2">
        <button onclick="window.location.reload()" class="btn btn-primary">
            <i class="bx bx-refresh me-1"></i>Refresh Page
        </button>
        <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-reset me-1"></i>Reset Filters
        </a>
    </div>
</div>
