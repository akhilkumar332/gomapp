@extends('errors.layout')

@section('code', '404')

@section('title', 'Page Not Found')

@section('icon')
<i class="mdi mdi-map-marker-question icon"></i>
@endsection

@section('message')
The page you're looking for doesn't exist or has been moved.
<br>
Please check the URL or try navigating from the home page.
@endsection
