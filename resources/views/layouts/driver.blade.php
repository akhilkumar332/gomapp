@extends('layouts.admin')

@section('nav_items')
<div class="nav_list">
    <a href="{{ route('driver.dashboard') }}" class="nav_link {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
        <i class='bx bx-grid-alt nav_icon'></i>
        <span class="nav_name">Dashboard</span>
    </a>
    <a href="{{ route('driver.zones') }}" class="nav_link {{ request()->routeIs('driver.zones.*') ? 'active' : '' }}">
        <i class='bx bx-map nav_icon'></i>
        <span class="nav_name">My Zones</span>
    </a>
    <a href="{{ route('driver.activity') }}" class="nav_link {{ request()->routeIs('driver.activity') ? 'active' : '' }}">
        <i class='bx bx-history nav_icon'></i>
        <span class="nav_name">Activity</span>
    </a>
    <a href="{{ route('driver.payments') }}" class="nav_link {{ request()->routeIs('driver.payments') ? 'active' : '' }}">
        <i class='bx bx-money nav_icon'></i>
        <span class="nav_name">Payments</span>
    </a>
</div>
@endsection

@push('styles')
<style>
:root {
    --first-color: #28a745;
    --first-color-light: #86efac;
}

.btn-primary {
    background-color: var(--first-color);
    border-color: var(--first-color);
}

.btn-primary:hover {
    background-color: #1f8838;
    border-color: #1f8838;
}

.text-primary {
    color: var(--first-color) !important;
}

.bg-primary-subtle {
    background-color: rgba(40, 167, 69, 0.1) !important;
}
</style>
@endpush
