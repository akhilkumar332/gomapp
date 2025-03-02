<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Delivery Management') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Other Styles -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
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

        /* Fix for chart container */
        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Status Dashboard Styles */
        .avatar-md {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
        .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
        .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
        .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
        .bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }

        .text-primary { color: #0d6efd !important; }
        .text-success { color: #198754 !important; }
        .text-warning { color: #ffc107 !important; }
        .text-danger { color: #dc3545 !important; }
        .text-info { color: #0dcaf0 !important; }

        .display-6 {
            font-size: 2rem;
            line-height: 1;
        }

        /* Header and User Menu Styles */
        .header {
            background: white;
            border-bottom: 1px solid #E5E7EB;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            height: var(--header-height);
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 99;
        }

        .user-menu {
            position: relative;
        }

        .user-menu .btn {
            padding: 0.5rem 0.75rem;
            border: none;
            background: transparent;
            color: var(--secondary-color);
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .user-menu .btn:hover,
        .user-menu .btn:focus,
        .user-menu .btn.active {
            color: var(--primary-color);
            background-color: #EDE9FE;
            border-radius: 0.375rem;
        }

        .user-menu .btn i {
            font-size: 1.25rem;
        }

        .user-menu .dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            min-width: 240px;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 0.5rem 0;
        }

        .user-menu .dropdown-item {
            padding: 0.625rem 1rem;
            display: flex;
            align-items: center;
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .user-menu .dropdown-item i {
            font-size: 1.25rem;
            margin-right: 0.75rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .user-menu .dropdown-item:hover {
            background-color: #EDE9FE;
            color: var(--primary-color);
        }

        .user-menu .dropdown-item:hover i {
            opacity: 1;
        }

        .user-menu .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid #E5E7EB;
        }

        .user-menu .dropdown-item.text-danger {
            color: #DC2626;
        }

        .user-menu .dropdown-item.text-danger:hover {
            background-color: #FEF2F2;
        }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="https://{{ request()->getHost() }}{{ route('admin.dashboard', [], false) }}" class="sidebar-logo">
                {{ config('app.name', 'Delivery Management') }}
            </a>
        </div>

        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="https://{{ request()->getHost() }}{{ route('admin.dashboard', [], false) }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="mdi mdi-view-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="https://{{ request()->getHost() }}{{ route('admin.drivers.index', [], false) }}" class="nav-link {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                    <i class="mdi mdi-account-multiple"></i>
                    <span>Drivers</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="https://{{ request()->getHost() }}{{ route('admin.zones.index', [], false) }}" class="nav-link {{ request()->routeIs('admin.zones.*') ? 'active' : '' }}">
                    <i class="mdi mdi-map"></i>
                    <span>Zones</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="https://{{ request()->getHost() }}{{ route('admin.locations.index', [], false) }}" class="nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                    <i class="mdi mdi-map-marker"></i>
                    <span>Locations</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="https://{{ request()->getHost() }}{{ route('admin.reports.index', [], false) }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="mdi mdi-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="https://{{ request()->getHost() }}{{ route('admin.status.index', [], false) }}" class="nav-link {{ request()->routeIs('admin.status.*') ? 'active' : '' }}">
                    <i class="mdi mdi-server"></i>
                    <span>Status</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="https://{{ request()->getHost() }}{{ route('admin.settings.index', [], false) }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="mdi mdi-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <header class="header">
            <div class="user-menu">
                <button type="button" class="btn" id="userMenuBtn">
                    <i class="mdi mdi-account-circle"></i>
                    <span>{{ Auth::user()->name }}</span>
                    <i class="mdi mdi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu" id="userMenuDropdown" style="display: none;">
                    <li>
                        <a class="dropdown-item" href="https://{{ request()->getHost() }}{{ route('admin.profile', [], false) }}">
                            <i class="mdi mdi-account me-2"></i> Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="https://{{ request()->getHost() }}{{ route('logout', [], false) }}">
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

    <!-- jQuery first, then Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Other Scripts -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Custom dropdown handling -->
    <script>
        $(document).ready(function() {
            const userMenuBtn = $('#userMenuBtn');
            const userMenuDropdown = $('#userMenuDropdown');
            let isOpen = false;

            // Toggle dropdown on button click
            userMenuBtn.on('click', function(e) {
                e.preventDefault();
                isOpen = !isOpen;
                userMenuDropdown.toggle(isOpen);
                userMenuBtn.toggleClass('active', isOpen);
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.user-menu').length) {
                    isOpen = false;
                    userMenuDropdown.hide();
                    userMenuBtn.removeClass('active');
                }
            });

            // Handle keyboard navigation
            userMenuBtn.on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                } else if (e.key === 'Escape' && isOpen) {
                    isOpen = false;
                    userMenuDropdown.hide();
                    userMenuBtn.removeClass('active');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
