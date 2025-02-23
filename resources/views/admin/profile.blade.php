@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Profile Settings</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <!-- Profile Card -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="avatar avatar-xxl mb-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4723D9&color=fff" 
                             alt="Profile" class="rounded-circle img-fluid">
                    </div>
                    <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                    <p class="text-muted mb-3">{{ ucfirst(Auth::user()->role) }}</p>
                    <div class="d-flex justify-content-center">
                        <div class="text-center px-4">
                            <p class="mb-0 text-muted">Last Login</p>
                            <h5 class="mb-0">
                                {{ Auth::user()->loginLogs()->latest()->first()?->login_at?->diffForHumans() ?? 'Never' }}
                            </h5>
                        </div>
                        <div class="text-center px-4">
                            <p class="mb-0 text-muted">Status</p>
                            <h5 class="mb-0">
                                <span class="badge bg-{{ Auth::user()->is_online ? 'success' : 'danger' }}">
                                    {{ Auth::user()->is_online ? 'Online' : 'Offline' }}
                                </span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <!-- Update Profile -->
            <x-form :action="route('admin.profile.update')" method="POST" class="mb-4">
                <x-slot name="header">
                    <h5 class="card-title mb-0">Update Profile</h5>
                </x-slot>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input 
                            label="Name" 
                            name="name" 
                            :value="Auth::user()->name" 
                            required />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            type="email" 
                            label="Email" 
                            name="email" 
                            :value="Auth::user()->email" 
                            required />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            label="Phone Number" 
                            name="phone_number" 
                            :value="Auth::user()->phone_number" 
                            required />
                    </div>
                </div>

                <x-slot name="footer">
                    <div class="text-end">
                        <x-form.button>
                            <i class='bx bx-save me-1'></i> Update Profile
                        </x-form.button>
                    </div>
                </x-slot>
            </x-form>

            <!-- Change Password -->
            <x-form :action="route('admin.profile.password')" method="POST">
                <x-slot name="header">
                    <h5 class="card-title mb-0">Change Password</h5>
                </x-slot>

                <div class="row">
                    <div class="col-md-6">
                        <x-form.input 
                            type="password" 
                            label="Current Password" 
                            name="current_password" 
                            required />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            type="password" 
                            label="New Password" 
                            name="password" 
                            required />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            type="password" 
                            label="Confirm New Password" 
                            name="password_confirmation" 
                            required />
                    </div>
                </div>

                <x-slot name="footer">
                    <div class="text-end">
                        <x-form.button variant="warning">
                            <i class='bx bx-lock-alt me-1'></i> Change Password
                        </x-form.button>
                    </div>
                </x-slot>
            </x-form>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar-xxl {
    width: 8rem;
    height: 8rem;
}

.avatar-xxl img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge {
    padding: 0.5em 1em;
    font-weight: 500;
}
</style>
@endpush
