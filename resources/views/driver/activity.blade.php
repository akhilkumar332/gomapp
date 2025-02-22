@extends('layouts.driver')

@section('title', 'My Activity')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Activity</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Activity</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('driver.activity') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="action" class="form-label">Activity Type</label>
                    <select class="form-select" id="action" name="action">
                        <option value="">All Activities</option>
                        <option value="delivery" {{ request('action') == 'delivery' ? 'selected' : '' }}>Deliveries</option>
                        <option value="payment" {{ request('action') == 'payment' ? 'selected' : '' }}>Payments</option>
                        <option value="status_change" {{ request('action') == 'status_change' ? 'selected' : '' }}>Status Changes</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="date_range" class="form-label">Date Range</label>
                    <input type="text" 
                           class="form-control" 
                           id="date_range" 
                           name="date_range" 
                           value="{{ request('date_range') }}"
                           placeholder="Select date range">
                </div>

                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Timeline -->
    <div class="card">
        <div class="card-body">
            <div class="timeline">
                @forelse($activities as $activity)
                    <div class="timeline-item mb-4">
                        <div class="row">
                            <div class="col-auto">
                                <div class="timeline-icon bg-{{ $activity->getStatusColor() }}">
                                    @switch($activity->action)
                                        @case('delivery')
                                            <i class="fas fa-truck"></i>
                                            @break
                                        @case('payment')
                                            <i class="fas fa-money-bill"></i>
                                            @break
                                        @case('status_change')
                                            <i class="fas fa-toggle-on"></i>
                                            @break
                                        @default
                                            <i class="fas fa-check-circle"></i>
                                    @endswitch
                                </div>
                            </div>
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">{{ $activity->getActionTitle() }}</h6>
                                            <small class="text-muted">{{ $activity->created_at->format('M d, Y H:i') }}</small>
                                        </div>
                                        <p class="mb-0">{{ $activity->description }}</p>
                                        @if($activity->metadata)
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    @foreach($activity->metadata as $key => $value)
                                                        <div><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</div>
                                                    @endforeach
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No activity records found.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .timeline {
        position: relative;
        padding: 1rem 0;
    }

    .timeline-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 20px;
        width: 2px;
        height: calc(100% - 40px);
        background-color: #e9ecef;
    }

    .bg-delivery { background-color: #3498db; }
    .bg-payment { background-color: #2ecc71; }
    .bg-status_change { background-color: #f1c40f; }
    .bg-default { background-color: #95a5a6; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        $('#date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
@endpush

@endsection
