<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Delivery Management') }} - Driver Portal</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #8B5CF6;
            --primary-hover: #7C3AED;
            --secondary-color: #6B7280;
            --sidebar-width: 280px;
            --header-height: 64px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: white;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .header {
                left: 0 !important;
            }
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .sidebar-logo {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }

        .sidebar-nav {
            padding: 1rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--secondary-color);
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background-color: #F3F4F6;
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: #EDE9FE;
            color: var(--primary-color);
        }

        .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        .header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 99;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--secondary-color);
            font-size: 1.5rem;
            padding: 0;
            margin-right: 1rem;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
        }

        .content {
            margin-top: calc(var(--header-height) + 2rem);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .card {
            border: none;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #E5E7EB;
            padding: 1rem 1.5rem;
        }

        .table th {
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .dropdown-item.active, .dropdown-item:active {
            background-color: var(--primary-color);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge i {
            margin-right: 0.25rem;
            font-size: 1rem;
        }

        .status-online {
            background-color: #D1FAE5;
            color: #059669;
        }

        .status-offline {
            background-color: #FEE2E2;
            color: #DC2626;
        }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('driver.dashboard') }}" class="sidebar-logo">
                {{ config('app.name', 'Delivery Management') }}
            </a>
        </div>

        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('driver.dashboard') }}" class="nav-link {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
                    <i class="mdi mdi-view-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('driver.zones.index') }}" class="nav-link {{ request()->routeIs('driver.zones.*') ? 'active' : '' }}">
                    <i class="mdi mdi-map"></i>
                    <span>My Zones</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('driver.locations.index') }}" class="nav-link {{ request()->routeIs('driver.locations.*') ? 'active' : '' }}">
                    <i class="mdi mdi-map-marker"></i>
                    <span>Locations</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('driver.activities') }}" class="nav-link {{ request()->routeIs('driver.activities') ? 'active' : '' }}">
                    <i class="mdi mdi-history"></i>
                    <span>Activities</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <header class="header">
            <button class="menu-toggle" id="menuToggle">
                <i class="mdi mdi-menu"></i>
            </button>

            <div class="flex-grow-1 d-flex align-items-center">
                <div class="status-badge {{ auth()->user()->isOnline() ? 'status-online' : 'status-offline' }}">
                    <i class="mdi {{ auth()->user()->isOnline() ? 'mdi-circle' : 'mdi-circle-outline' }}"></i>
                    <span>{{ auth()->user()->isOnline() ? 'Online' : 'Offline' }}</span>
                </div>
            </div>

            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle text-decoration-none" type="button" data-bs-toggle="dropdown">
                    {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('driver.profile') }}">
                            <i class="mdi mdi-account me-2"></i> Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="mdi mdi-logout me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <div class="content">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
