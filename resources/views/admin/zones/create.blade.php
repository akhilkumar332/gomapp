@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Create New Zone</h5>
            <a href="{{ route('admin.zones.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left me-1"></i>Back to List
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.zones.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Zone Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="center_lat" class="form-label">Center Latitude</label>
                            <input type="text" class="form-control @error('center_lat') is-invalid @enderror" 
                                   id="center_lat" name="center_lat" value="{{ old('center_lat') }}" required>
                            @error('center_lat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="center_lng" class="form-label">Center Longitude</label>
                            <input type="text" class="form-control @error('center_lng') is-invalid @enderror" 
                                   id="center_lng" name="center_lng" value="{{ old('center_lng') }}" required>
                            @error('center_lng')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="radius" class="form-label">Radius (in kilometers)</label>
                            <input type="number" class="form-control @error('radius') is-invalid @enderror" 
                                   id="radius" name="radius" value="{{ old('radius') }}" required>
                            @error('radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Zone Guidelines</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="mdi mdi-information text-primary me-2"></i>
                                        Create zones to organize delivery locations effectively
                                    </li>
                                    <li class="mb-2">
                                        <i class="mdi mdi-map-marker text-success me-2"></i>
                                        You can add locations to this zone after creation
                                    </li>
                                    <li class="mb-2">
                                        <i class="mdi mdi-account-multiple text-info me-2"></i>
                                        Assign drivers to manage deliveries in this zone
                                    </li>
                                    <li>
                                        <i class="mdi mdi-alert text-warning me-2"></i>
                                        Ensure the zone name is unique and descriptive
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-plus-circle me-1"></i>Create Zone
                    </button>
                    <a href="{{ route('admin.zones.index') }}" class="btn btn-secondary ms-2">
                        <i class="mdi mdi-cancel me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
