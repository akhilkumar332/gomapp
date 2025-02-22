@extends('layouts.admin')

@section('title', 'Admin Profile')

@section('content')
<div class="container">
    <h1>Admin Profile</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Profile Information</h5>
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Phone Number:</strong> {{ $user->phone_number }}</p>
            <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
            <a href="{{ route('admin.profile.edit') }}" class="btn btn-warning">Edit Profile</a>
        </div>
    </div>
</div>
@endsection
