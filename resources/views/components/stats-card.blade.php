@props([
    'title',
    'value',
    'icon',
    'variant' => 'primary',
    'trend' => null,
    'trendValue' => null
])

<x-card {{ $attributes->merge(['class' => 'stats-card']) }} :variant="$variant">
    <div class="d-flex align-items-center">
        <div class="icon bg-{{ $variant }}-subtle">
            <i class="bx {{ $icon }} text-{{ $variant }}"></i>
        </div>
        <div class="stats-content">
            <h6 class="mb-1">{{ $title }}</h6>
            <h2 class="mb-0">{{ $value }}</h2>
            @if($trend)
                <div class="trend mt-2">
                    <span class="text-{{ $trend === 'up' ? 'success' : 'danger' }} d-flex align-items-center">
                        <i class="bx bx-{{ $trend === 'up' ? 'up' : 'down' }}-arrow-alt me-1"></i>
                        {{ $trendValue }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</x-card>

@pushOnce('styles')
<style>
.stats-card .icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 1rem;
}

.stats-card .stats-content h6 {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    opacity: 0.8;
}

.stats-card .stats-content h2 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0;
}

.stats-card .trend {
    font-size: 0.875rem;
}

.bg-primary-subtle {
    background-color: rgba(71, 35, 217, 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(23, 162, 184, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.text-primary {
    color: var(--first-color) !important;
}

.text-white .stats-content h6,
.text-white .stats-content h2 {
    color: #fff !important;
}
</style>
@endPushOnce
