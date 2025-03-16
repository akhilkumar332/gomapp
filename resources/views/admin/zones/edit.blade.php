@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Edit Zone</h5>
            <a href="{{ route('admin.zones.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left me-1"></i>Back to List
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.zones.update', $zone) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Zone Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $zone->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4">{{ old('description', $zone->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="center_lat" class="form-label">Center Latitude</label>
                            <input type="text" class="form-control @error('center_lat') is-invalid @enderror" 
                                   id="center_lat" name="center_lat" value="{{ old('center_lat', $zone->center_lat) }}" required>
                            @error('center_lat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="center_lng" class="form-label">Center Longitude</label>
                            <input type="text" class="form-control @error('center_lng') is-invalid @enderror" 
                                   id="center_lng" name="center_lng" value="{{ old('center_lng', $zone->center_lng) }}" required>
                            @error('center_lng')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="radius" class="form-label">Radius (in kilometers)</label>
                            <input type="number" class="form-control @error('radius') is-invalid @enderror" 
                                   id="radius" name="radius" value="{{ old('radius', $zone->radius) }}" required>
                            @error('radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                <option value="active" {{ old('status', $zone->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $zone->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Zone Information</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="mdi mdi-map-marker text-primary me-2"></i>
                                        Latitude: {{ $zone->center_lat }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="mdi mdi-map-marker text-primary me-2"></i>
                                        Longitude: {{ $zone->center_lng }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="mdi mdi-map-marker text-primary me-2"></i>
                                        Radius: {{ $zone->radius }} km
                                    </li>
                                <p class="card-text text-muted">
                                    This zone currently has:
                                </p>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="mdi mdi-map-marker text-primary me-2"></i>
                                        {{ $zone->locations_count }} Locations
                                    </li>
<li>
    <i class="mdi mdi-account-multiple text-success me-2"></i>
    {{ $zone->getActiveDriversCountAttribute() }} Active Drivers
                                    </li>
                                </ul>
                                <hr>
                                <p class="card-text small text-muted mb-0">
                                    Last updated: {{ $zone->updated_at->format('M d, Y H:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.zones.show', $zone) }}" class="btn btn-secondary ms-2">
                        <i class="mdi mdi-cancel me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
