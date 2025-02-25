@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Application Settings</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- General Settings -->
                    <div class="col-md-6">
                        <h6 class="mb-3">General Settings</h6>
                        
                        <div class="mb-3">
                            <label for="app_name" class="form-label">Application Name</label>
                            <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                                   id="app_name" name="app_name" value="{{ old('app_name', $settings['branding']['app_name']) }}">
                            @error('app_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" name="company_name" value="{{ old('company_name', $settings['branding']['company_name'] ?? '') }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                   id="contact_email" name="contact_email" value="{{ old('contact_email', $settings['branding']['contact_email'] ?? '') }}">
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                   id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $settings['branding']['contact_phone'] ?? '') }}">
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div class="col-md-6">
                        <h6 class="mb-3">System Settings</h6>

                        <div class="mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select @error('timezone') is-invalid @enderror" 
                                    id="timezone" name="timezone">
                                @foreach($timezones as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', $settings['branding']['timezone'] ?? '') === $tz ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date_format" class="form-label">Date Format</label>
                            <select class="form-select @error('date_format') is-invalid @enderror" 
                                    id="date_format" name="date_format">
                                <option value="Y-m-d" {{ old('date_format', $settings['branding']['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' }}>
                                    YYYY-MM-DD
                                </option>
                                <option value="d/m/Y" {{ old('date_format', $settings['branding']['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' }}>
                                    DD/MM/YYYY
                                </option>
                                <option value="m/d/Y" {{ old('date_format', $settings['branding']['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' }}>
                                    MM/DD/YYYY
                                </option>
                            </select>
                            @error('date_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">System Features</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_notifications" 
                                       name="enable_notifications" value="1" 
                                       {{ old('enable_notifications', $settings['branding']['enable_notifications'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_notifications">Enable Notifications</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_auto_assignment" 
                                       name="enable_auto_assignment" value="1" 
                                       {{ old('enable_auto_assignment', $settings['branding']['enable_auto_assignment'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_auto_assignment">Enable Auto Assignment</label>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="col-12 mt-4">
                        <h6 class="mb-3">Notification Settings</h6>
                        
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email Notifications</label>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="notify_new_delivery" 
                                                       name="notify_new_delivery" value="1" 
                                                       {{ old('notify_new_delivery', $settings['branding']['notify_new_delivery'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notify_new_delivery">New Delivery Assignments</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="notify_delivery_complete" 
                                                       name="notify_delivery_complete" value="1" 
                                                       {{ old('notify_delivery_complete', $settings['branding']['notify_delivery_complete'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notify_delivery_complete">Completed Deliveries</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">SMS Notifications</label>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="sms_notifications" 
                                                       name="sms_notifications" value="1" 
                                                       {{ old('sms_notifications', $settings['branding']['sms_notifications'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="sms_notifications">Enable SMS Notifications</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>Save Settings
                    </button>
                    <button type="reset" class="btn btn-secondary ms-2">
                        <i class="mdi mdi-refresh me-1"></i>Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any form elements that need it
    const form = document.querySelector('form');
    
    // Handle form reset
    form.addEventListener('reset', function(e) {
        if (!confirm('Are you sure you want to reset all changes?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
@endsection
