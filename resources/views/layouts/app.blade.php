<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Delivery Management') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .navbar-brand img {
            height: 40px;
            width: auto;
        }
        img.fallback {
            display: none;
        }
        img.main {
            height: 40px;
            width: auto;
        }
        img.main:not([src]), 
        img.main[src=""], 
        img.main[src="#"],
        img.main:not([src^="data:"]):not([src^="http"]):not([src^="/"]) {
            display: none;
        }
        img.main:not([src]):not([src=""]):not([src="#"]) + img.fallback,
        img.main[src=""]:not([src^="data:"]):not([src^="http"]):not([src^="/"]) + img.fallback,
        img.main[src="#"]:not([src^="data:"]):not([src^="http"]):not([src^="/"]) + img.fallback,
        img.main:not([src^="data:"]):not([src^="http"]):not([src^="/"]) + img.fallback {
            display: inline-block;
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img class="main" src="{{ asset('images/logo.png') }}" alt="Logo">
                <img class="fallback" src="{{ asset('images/placeholder.png') }}" alt="Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.zones.index') }}">Zones</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.locations.index') }}">Locations</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.drivers.index') }}">Drivers</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('driver.dashboard') }}">Dashboard</a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ auth()->user()->role === 'admin' ? route('admin.profile') : route('driver.profile') }}">
                                        Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
