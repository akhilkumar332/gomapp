@extends('layouts.admin')

@section('title', 'Edit Payment')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Payment #{{ $payment->id }}</h5>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Payments
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="location_id" class="form-label required">Location</label>
                            <select class="form-select @error('location_id') is-invalid @enderror" 
                                    id="location_id" 
                                    name="location_id" 
                                    required>
                                <option value="">Select Location</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" 
                                            data-zone="{{ $location->zone->name }}"
                                            {{ old('location_id', $payment->location_id) == $location->id ? 'selected' : '' }}>
                                        {{ $location->shop_name }} ({{ $location->zone->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label required">Amount (GHS)</label>
                            <div class="input-group">
                                <span class="input-group-text">GHS</span>
                                <input type="number" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       value="{{ old('amount', $payment->amount) }}" 
                                       step="0.01"
                                       min="0"
                                       required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label required">Payment Method</label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" 
                                    id="payment_method" 
                                    name="payment_method" 
                                    required>
                                <option value="">Select Payment Method</option>
                                <option value="online" {{ old('payment_method', $payment->payment_method) == 'online' ? 'selected' : '' }}>Online</option>
                                <option value="cash" {{ old('payment_method', $payment->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="credit" {{ old('payment_method', $payment->payment_method) == 'credit' ? 'selected' : '' }}>Credit</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference Number</label>
                            <input type="text" 
                                   class="form-control @error('reference') is-invalid @enderror" 
                                   id="reference" 
                                   name="reference" 
                                   value="{{ old('reference', $payment->reference) }}"
                                   placeholder="Enter reference number (optional)">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Enter any additional notes">{{ old('notes', $payment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="pending" {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ old('status', $payment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ old('status', $payment->status) == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Payment
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <div class="text-muted">
                        <small>Created: {{ $payment->created_at->format('Y-m-d H:i:s') }}</small><br>
                        <small>Last Updated: {{ $payment->updated_at->format('Y-m-d H:i:s') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .required:after {
        content: ' *';
        color: red;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize select2 for better dropdown experience
    $(document).ready(function() {
        $('#location_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Location'
        });

        // Update reference field based on payment method
        $('#payment_method').change(function() {
            const method = $(this).val();
            const referenceField = $('#reference');
            
            if (method === 'online') {
                referenceField.attr('placeholder', 'Enter transaction ID');
            } else if (method === 'cash') {
                referenceField.attr('placeholder', 'Enter receipt number (optional)');
            } else if (method === 'credit') {
                referenceField.attr('placeholder', 'Enter credit note number');
            } else {
                referenceField.attr('placeholder', 'Enter reference number (optional)');
            }
        }).trigger('change'); // Trigger on page load
    });
</script>
@endpush

@endsection
