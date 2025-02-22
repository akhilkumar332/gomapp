@extends('layouts.admin')

@section('title', 'Create Zone')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create New Zone</h5>
                        <a href="{{ route('admin.zones.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Zones
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.zones.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label required">Zone Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign Drivers</label>
                            <div class="card">
                                <div class="card-body">
                                    @foreach($drivers as $driver)
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="drivers[]" 
                                                   value="{{ $driver->id }}" 
                                                   id="driver{{ $driver->id }}"
                                                   {{ (is_array(old('drivers')) && in_array($driver->id, old('drivers'))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="driver{{ $driver->id }}">
                                                {{ $driver->name }} ({{ $driver->phone_number }})
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('drivers')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Create Zone
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
