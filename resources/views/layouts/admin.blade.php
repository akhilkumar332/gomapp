<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Delivery Management</title>

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.25rem;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #007bff;
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
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .dropdown-menu {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <div class="px-3 mb-4">
                <h5 class="text-white">Delivery Management</h5>
            </div>
            <nav class="nav flex-column">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ route('admin.zones.index') }}" class="nav-link {{ request()->routeIs('admin.zones.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i> Zones
                </a>
                <a href="{{ route('admin.locations.index') }}" class="nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt"></i> Locations
                </a>
                <a href="{{ route('admin.drivers.index') }}" class="nav-link {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Drivers
                </a>
                <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> Payments
                </a>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" 
                       data-bs-toggle="dropdown" href="#" role="button">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('admin.reports.driver-activity') }}">Driver Activity</a>
                        <a class="dropdown-item" href="{{ route('admin.reports.zone-statistics') }}">Zone Statistics</a>
                        <a class="dropdown-item" href="{{ route('admin.reports.system-usage') }}">System Usage</a>
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}" 
                       data-bs-toggle="dropdown" href="#" role="button">
                        <i class="fas fa-history"></i> Logs
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('admin.activity-logs.index') }}">Activity Logs</a>
                        <a class="dropdown-item" href="{{ route('admin.login-logs.index') }}">Login Logs</a>
                    </div>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i>
                                    {{ Auth::user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                        <i class="fas fa-user me-2"></i> Profile
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
        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Handle active state for nested dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            document.querySelectorAll('.dropdown-menu a').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                    link.closest('.nav-item').querySelector('.nav-link').classList.add('active');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
