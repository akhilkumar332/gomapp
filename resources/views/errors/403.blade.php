@extends('errors.layout')

@section('code', '403')

@section('title', 'Access Denied')

@section('icon')
<i class="mdi mdi-shield-alert icon"></i>
@endsection

@section('message')
You don't have permission to access this resource.
<br>
If you believe this is an error, please contact your administrator.
@endsection

@section('actions')
@auth
    @if(auth()->user()->isDriver())
        <a href="{{ route('driver.dashboard') }}" class="btn btn-primary">
            <i class="mdi mdi-view-dashboard"></i>
            Go to Driver Dashboard
        </a>
    @elseif(auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
            <i class="mdi mdi-view-dashboard"></i>
            Go to Admin Dashboard
        </a>
    @endif
@else
    <a href="{{ route('login') }}" class="btn btn-primary">
        <i class="mdi mdi-login"></i>
        Log In
    </a>
@endauth
@endsection
