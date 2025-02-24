@extends('layouts.admin')

@section('title', 'Create Location')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create New Location</h5>
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Locations
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.locations.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="zone_id" class="form-label required">Zone</label>
                            <select class="form-select @error('zone_id') is-invalid @enderror" 
                                    id="zone_id" 
                                    name="zone_id" 
                                    required>
                                <option value="">Select Zone</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
                                        {{ $zone->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('zone_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shop_name" class="form-label required">Shop Name</label>
                            <input type="text" 
                                   class="form-control @error('shop_name') is-invalid @enderror" 
                                   id="shop_name" 
                                   name="shop_name" 
                                   value="{{ old('shop_name') }}" 
                                   required>
                            @error('shop_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label required">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="2" 
                                      required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ghana_post_gps_code" class="form-label required">GhanaPostGPS Code</label>
                            <input type="text" 
                                   class="form-control @error('ghana_post_gps_code') is-invalid @enderror" 
                                   id="ghana_post_gps_code" 
                                   name="ghana_post_gps_code" 
                                   value="{{ old('ghana_post_gps_code') }}" 
                                   placeholder="e.g., GG-739-9069"
                                   required>
                            @error('ghana_post_gps_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="latitude" class="form-label required">Latitude</label>
                                    <input type="number" 
                                           class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" 
                                           name="latitude" 
                                           value="{{ old('latitude') }}" 
                                           step="any"
                                           required>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="longitude" class="form-label required">Longitude</label>
                                    <input type="number" 
                                           class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" 
                                           name="longitude" 
                                           value="{{ old('longitude') }}" 
                                           step="any"
                                           required>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="contact_number" class="form-label required">Contact Number</label>
                            <input type="text" 
                                   class="form-control @error('contact_number') is-invalid @enderror" 
                                   id="contact_number" 
                                   name="contact_number" 
                                   value="{{ old('contact_number') }}"
                                   placeholder="e.g., +233 XX XXX XXXX"
                                   required>
                            @error('contact_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label required">Priority</label>
                            <select class="form-select @error('priority') is-invalid @enderror" 
                                    id="priority" 
                                    name="priority" 
                                    required>
                                <option value="">Select Priority</option>
                                <option value="1" {{ old('priority') == '1' ? 'selected' : '' }}>1 - Highest</option>
                                <option value="2" {{ old('priority') == '2' ? 'selected' : '' }}>2 - High</option>
                                <option value="3" {{ old('priority') == '3' ? 'selected' : '' }}>3 - Medium</option>
                                <option value="4" {{ old('priority') == '4' ? 'selected' : '' }}>4 - Low</option>
                                <option value="5" {{ old('priority') == '5' ? 'selected' : '' }}>5 - Lowest</option>
                            </select>
                            @error('priority')
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

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Create Location
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

@push('scripts')
<script>
    // Add GhanaPostGPS code validation and coordinate lookup functionality here
    document.getElementById('ghana_post_gps_code').addEventListener('blur', async function() {
        const code = this.value;
        if (code) {
            try {
                // Call GhanaPostGPS API to get coordinates
                const response = await fetch(`/api/ghana-post-gps/lookup/${code}`);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('latitude').value = data.latitude;
                    document.getElementById('longitude').value = data.longitude;
                }
            } catch (error) {
                console.error('Error looking up GhanaPostGPS code:', error);
            }
        }
    });
</script>
@endpush

@endsection
