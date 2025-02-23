@props(['header' => null, 'footer' => null, 'variant' => null])

<div {{ $attributes->merge(['class' => 'card ' . ($variant ? 'bg-' . $variant . ' text-white' : '')]) }}>
    @if($header)
        <div class="card-header">
            @if(is_string($header))
                <h5 class="card-title mb-0">{{ $header }}</h5>
            @else
                {{ $header }}
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>

@pushOnce('styles')
<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,.08);
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 25px rgba(0,0,0,.1);
}

.card-header {
    background-color: transparent;
    border-bottom: 1px solid rgba(0,0,0,.05);
    padding: 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

.card-footer {
    background-color: transparent;
    border-top: 1px solid rgba(0,0,0,.05);
    padding: 1.5rem;
}

.card-title {
    font-weight: 600;
    color: #2D3748;
}

/* Card variants */
.bg-primary {
    background: linear-gradient(135deg, var(--first-color), #3b1bb3) !important;
}

.bg-success {
    background: linear-gradient(135deg, #28a745, #1f8838) !important;
}

.bg-info {
    background: linear-gradient(135deg, #17a2b8, #117a8b) !important;
}

.bg-warning {
    background: linear-gradient(135deg, #ffc107, #d39e00) !important;
}

.bg-danger {
    background: linear-gradient(135deg, #dc3545, #bd2130) !important;
}

/* Stats card specific styles */
.stats-card {
    min-height: 120px;
}

.stats-card .card-body {
    display: flex;
    align-items: center;
}

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

/* Activity card specific styles */
.activity-card .activity-item {
    padding: 1rem 0;
    border-bottom: 1px solid rgba(0,0,0,.05);
}

.activity-card .activity-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.activity-card .activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}
</style>
@endPushOnce
