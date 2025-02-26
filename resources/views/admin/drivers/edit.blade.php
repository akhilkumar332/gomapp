@extends('layouts.admin')

@section('title', 'Edit Driver')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Driver: {{ $driver->name }}</h5>
                        <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Drivers
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.drivers.update', $driver) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label required">Full Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $driver->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label required">Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $driver->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label required">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+233</span>
                                <input type="text" 
                                       class="form-control @error('phone_number') is-invalid @enderror" 
                                       id="phone_number" 
                                       name="phone_number" 
                                       value="{{ old('phone_number', ltrim(str_replace('+233', '', $driver->phone_number), '0')) }}" 
                                       placeholder="XX XXX XXXX"
                                       required>
                            </div>
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter number without the country code (e.g., 244123456)</small>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="active" {{ $driver->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $driver->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ $driver->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign Zones</label>
                            <div class="card">
                                <div class="card-body">
                                    @foreach($zones as $zone)
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="zones[]" 
                                                   value="{{ $zone->id }}" 
                                                   id="zone{{ $zone->id }}"
                                                   {{ $driver->zones->contains($zone->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="zone{{ $zone->id }}">
                                                {{ $zone->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('zones')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Driver
                            </button>
                        </div>
                    </form>
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

@endsection
