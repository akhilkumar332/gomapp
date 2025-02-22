@extends('layouts.admin')

@section('title', 'Application Settings')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Application Settings</h1>
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

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Branding Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="app_name" class="form-label">Application Name</label>
                            <input type="text" 
                                   class="form-control @error('app_name') is-invalid @enderror" 
                                   id="app_name" 
                                   name="app_name" 
                                   value="{{ old('app_name', $settings['branding']['app_name'] ?? config('app.name')) }}">
                            @error('app_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="primary_color" class="form-label">Primary Color</label>
                            <input type="color" 
                                   class="form-control form-control-color @error('primary_color') is-invalid @enderror" 
                                   id="primary_color" 
                                   name="primary_color" 
                                   value="{{ old('primary_color', $settings['branding']['primary_color'] ?? '#007bff') }}">
                            @error('primary_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <input type="color" 
                                   class="form-control form-control-color @error('secondary_color') is-invalid @enderror" 
                                   id="secondary_color" 
                                   name="secondary_color" 
                                   value="{{ old('secondary_color', $settings['branding']['secondary_color'] ?? '#6c757d') }}">
                            @error('secondary_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="accent_color" class="form-label">Accent Color</label>
                            <input type="color" 
                                   class="form-control form-control-color @error('accent_color') is-invalid @enderror" 
                                   id="accent_color" 
                                   name="accent_color" 
                                   value="{{ old('accent_color', $settings['branding']['accent_color'] ?? '#28a745') }}">
                            @error('accent_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo</label>
                            @if(isset($settings['branding']['logo_url']))
                                <div class="mb-2">
                                    <img src="{{ $settings['branding']['logo_url'] }}" 
                                         alt="Current Logo" 
                                         class="img-thumbnail" 
                                         style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" 
                                   class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/*">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="favicon" class="form-label">Favicon</label>
                            @if(isset($settings['branding']['favicon_url']))
                                <div class="mb-2">
                                    <img src="{{ $settings['branding']['favicon_url'] }}" 
                                         alt="Current Favicon" 
                                         class="img-thumbnail" 
                                         style="max-height: 32px;">
                                </div>
                            @endif
                            <input type="file" 
                                   class="form-control @error('favicon') is-invalid @enderror" 
                                   id="favicon" 
                                   name="favicon" 
                                   accept="image/x-icon,image/png">
                            @error('favicon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Branding
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update color input text values
    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('input', function() {
            this.nextElementSibling.value = this.value;
        });
    });
</script>
@endpush

@endsection
