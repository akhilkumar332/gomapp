<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Driver Portal</title>

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem;
        }

        .navbar-brand, .nav-link {
            color: white !important;
        }

        .nav-link:hover {
            color: var(--accent-color) !important;
        }

        .sidebar {
            background-color: var(--secondary-color);
            min-height: 100vh;
            padding-top: 1rem;
            width: 250px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin: 0.2rem 0;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: var(--accent-color);
            color: white;
        }

        .sidebar .nav-link i {
            width: 1.5rem;
            text-align: center;
            margin-right: 0.5rem;
        }

        .content {
            padding: 2rem;
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: bold;
        }

        .status-badge.online {
            background-color: #2ecc71;
            color: white;
        }

        .status-badge.offline {
            background-color: #e74c3c;
            color: white;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="px-3 mb-4">
                <h5 class="text-white">Driver Portal</h5>
            </div>
            <nav class="nav flex-column">
                <a href="{{ route('driver.dashboard') }}" class="nav-link {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ route('driver.zones') }}" class="nav-link {{ request()->routeIs('driver.zones.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i> My Zones
                </a>
                <a href="{{ route('driver.deliveries') }}" class="nav-link {{ request()->routeIs('driver.deliveries.*') ? 'active' : '' }}">
                    <i class="fas fa-truck"></i> Deliveries
                </a>
                <a href="{{ route('driver.payments') }}" class="nav-link {{ request()->routeIs('driver.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> Payments
                </a>
                <a href="{{ route('driver.reports') }}" class="nav-link {{ request()->routeIs('driver.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> My Reports
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto align-items-center">
                            <li class="nav-item me-3">
                                <span class="status-badge {{ Auth::user()->is_online ? 'online' : 'offline' }}">
                                    <i class="fas fa-circle me-1"></i>
                                    {{ Auth::user()->is_online ? 'Online' : 'Offline' }}
                                </span>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i>
                                    {{ Auth::user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('driver.profile') }}">
                                        <i class="fas fa-user me-2"></i> Profile
                                    </a>
                                    <a class="dropdown-item" href="{{ route('driver.settings') }}">
                                        <i class="fas fa-cog me-2"></i> Settings
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Update online status
        function updateOnlineStatus() {
            $.post('/api/driver/status/update', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                is_online: navigator.onLine
            });
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
    </script>
    @stack('scripts')
</body>
</html>
