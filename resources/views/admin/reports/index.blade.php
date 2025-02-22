@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container">
    <h1 class="mb-4">Reports & Analytics</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Driver Activity</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $driverActivityCount }}</h5>
                    <p class="card-text">Total driver activities recorded.</p>
                    <a href="{{ route('admin.reports.driver-activity') }}" class="btn btn-light">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Zone Statistics</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $zoneStatisticsCount }}</h5>
                    <p class="card-text">Total zones with statistics available.</p>
                    <a href="{{ route('admin.reports.zone-statistics') }}" class="btn btn-light">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header">System Usage</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $systemUsageCount }}</h5>
                    <p class="card-text">Total system usage logs recorded.</p>
                    <a href="{{ route('admin.reports.system-usage') }}" class="btn btn-light">View Details</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
