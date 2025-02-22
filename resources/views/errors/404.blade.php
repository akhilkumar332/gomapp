@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page mt-5">
                <h1 class="error-code">404</h1>
                <h2 class="error-title">Page Not Found</h2>
                <p class="error-description">
                    Sorry, the page you are looking for could not be found.
                </p>
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home mr-2"></i> Return Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .error-page {
        padding: 40px 0;
    }
    .error-code {
        font-size: 96px;
        color: #dc3545;
        margin-bottom: 20px;
    }
    .error-title {
        font-size: 24px;
        margin-bottom: 20px;
    }
    .error-description {
        color: #6c757d;
        margin-bottom: 30px;
    }
</style>
@endpush
