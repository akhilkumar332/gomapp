@props(['logs', 'showUser' => true, 'limit' => null])

<div class="activity-timeline">
    @foreach($logs->take($limit ?? $logs->count()) as $log)
        <div class="activity-item pb-3 mb-3 border-bottom">
            <div class="d-flex align-items-start">
                <div class="activity-indicator me-3">
                    <div class="avatar avatar-xs">
                        @if($showUser && $log->user)
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($log->user->name) }}&background=4723D9&color=fff" 
                                 alt="{{ $log->user->name }}"
                                 class="rounded-circle">
                        @else
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                <i class='bx bx-check'></i>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="activity-content flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            @if($showUser && $log->user)
                                <span class="fw-semibold">{{ $log->user->name }}</span>
                            @endif
                            <span class="text-muted">{{ $log->description }}</span>
                        </div>
                        <small class="text-muted ms-2">{{ $log->created_at->diffForHumans() }}</small>
                    </div>
                    @if($log->device_type || $log->ip_address)
                        <small class="text-muted d-flex align-items-center">
                            @if($log->device_type)
                                <i class='bx bx-{{ $log->device_type === 'mobile' ? 'mobile' : 'desktop' }} me-1'></i>
                                {{ ucfirst($log->device_type) }}
                            @endif
                            @if($log->ip_address)
                                <span class="mx-2">•</span>
                                <i class='bx bx-globe me-1'></i>
                                {{ $log->ip_address }}
                            @endif
                        </small>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@pushOnce('styles')
<style>
.activity-timeline {
    position: relative;
    padding: 0;
}

.activity-item {
    position: relative;
}

.activity-item:last-child {
    border-bottom: 0 !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.activity-indicator {
    position: relative;
}

.activity-indicator::after {
    content: '';
    position: absolute;
    left: 50%;
    top: 100%;
    transform: translateX(-50%);
    width: 2px;
    height: calc(100% + 1rem);
    background-color: #e9ecef;
}

.activity-item:last-child .activity-indicator::after {
    display: none;
}

.avatar {
    position: relative;
    width: 2.375rem;
    height: 2.375rem;
    cursor: pointer;
}

.avatar-xs {
    width: 1.65rem;
    height: 1.65rem;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--first-color);
}

.bg-primary-subtle {
    background-color: rgba(71, 35, 217, 0.1) !important;
}

.activity-content {
    font-size: 0.9375rem;
}

.activity-content .text-muted {
    font-size: 0.875rem;
}
</style>
@endPushOnce
