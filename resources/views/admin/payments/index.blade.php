@extends('layouts.admin')

@section('title', 'Manage Payments')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Payments</h1>
        <div>
            <a href="{{ route('admin.payments.export') }}" class="btn btn-success me-2">
                <i class="fas fa-file-excel me-1"></i>Export Payments
            </a>
            <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Payment
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Payments</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="location" class="form-label">Location</label>
                    <select class="form-select" id="location" name="location_id">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->shop_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="">All Methods</option>
                        <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="date_range" class="form-label">Date Range</label>
                    <input type="text" 
                           class="form-control" 
                           id="date_range" 
                           name="date_range" 
                           value="{{ request('date_range') }}"
                           placeholder="Select date range">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Location</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->location->shop_name }}</td>
                                <td>GHS {{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst($payment->payment_method) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>{{ $payment->reference ?? 'N/A' }}</td>
                                <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
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
