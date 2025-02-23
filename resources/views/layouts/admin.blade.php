<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Delivery Management') }} - Admin</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --header-height: 3rem;
            --nav-width: 250px;
            --first-color: #4723D9;
            --first-color-light: #AFA5D9;
            --white-color: #F7F6FB;
            --body-font: 'Inter', sans-serif;
            --normal-font-size: 1rem;
            --z-fixed: 100;
        }

        *,::before,::after {
            box-sizing: border-box;
        }

        body {
            position: relative;
            margin: var(--header-height) 0 0 0;
            padding: 0 1rem;
            font-family: var(--body-font);
            font-size: var(--normal-font-size);
            transition: .5s;
            background-color: #f8f9fa;
        }

        a {
            text-decoration: none;
        }

        .header {
            width: 100%;
            height: var(--header-height);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            background-color: var(--white-color);
            z-index: var(--z-fixed);
            transition: .5s;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }

        .header_toggle {
            color: var(--first-color);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .header_img {
            width: 35px;
            height: 35px;
            display: flex;
            justify-content: center;
            border-radius: 50%;
            overflow: hidden;
        }

        .header_img img {
            width: 40px;
        }

        .l-navbar {
            position: fixed;
            top: 0;
            left: -30%;
            width: var(--nav-width);
            height: 100vh;
            background-color: var(--first-color);
            padding: .5rem 1rem 0 0;
            transition: .5s;
            z-index: var(--z-fixed);
        }

        .nav {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .nav_logo, .nav_link {
            display: grid;
            grid-template-columns: max-content max-content;
            align-items: center;
            column-gap: 1rem;
            padding: .5rem 0 .5rem 1.5rem;
        }

        .nav_logo {
            margin-bottom: 2rem;
        }

        .nav_logo-icon {
            font-size: 1.25rem;
            color: var(--white-color);
        }

        .nav_logo-name {
            color: var(--white-color);
            font-weight: 700;
        }

        .nav_link {
            position: relative;
            color: var(--first-color-light);
            margin-bottom: .5rem;
            transition: .3s;
        }

        .nav_link:hover {
            color: var(--white-color);
        }

        .nav_icon {
            font-size: 1.25rem;
        }

        .show-nav {
            left: 0;
        }

        .body-pd {
            padding-left: calc(var(--nav-width) + 1rem);
        }

        .active {
            color: var(--white-color);
        }

        .active::before {
            content: '';
            position: absolute;
            left: 0;
            width: 2px;
            height: 32px;
            background-color: var(--white-color);
        }

        .height-100 {
            height: 100vh;
        }

        /* Form Styles */
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--first-color);
            box-shadow: 0 0 0 0.2rem rgba(71, 35, 217, 0.1);
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,.08);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,.05);
            padding: 1.5rem;
        }

        /* Button Styles */
        .btn {
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--first-color);
            border-color: var(--first-color);
        }

        .btn-primary:hover {
            background-color: #3b1bb3;
            border-color: #3b1bb3;
            transform: translateY(-1px);
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 500;
            padding: 1rem;
            color: #495057;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(71, 35, 217, 0.05);
        }

        /* Badge Styles */
        .badge {
            padding: 0.5em 1em;
            font-weight: 500;
            border-radius: 6px;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,.08);
            border-radius: 8px;
            padding: 0.5rem;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }

        .dropdown-item:hover {
            background-color: rgba(71, 35, 217, 0.05);
        }

        /* Navigation Sub-items */
        .nav_sub {
            padding-left: 2.5rem;
            display: none;
        }

        .nav_sub.show {
            display: block;
        }

        .nav_sub .nav_link {
            font-size: 0.9rem;
            padding: 0.3rem 0;
        }

        @media screen and (min-width: 768px) {
            body {
                margin: calc(var(--header-height) + 1rem) 0 0 0;
                padding-left: calc(var(--nav-width) + 2rem);
            }

            .header {
                height: calc(var(--header-height) + 1rem);
                padding: 0 2rem 0 calc(var(--nav-width) + 2rem);
            }

            .l-navbar {
                left: 0;
                padding: 1rem 1rem 0 0;
            }

            .show-nav {
                width: calc(var(--nav-width) + 156px);
            }

            .body-pd {
                padding-left: calc(var(--nav-width) + 188px);
            }
        }
    </style>
    @stack('styles')
</head>
<body id="body-pd">
    <header class="header" id="header">
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
        <div class="d-flex align-items-center">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2">{{ Auth::user()->name }}</span>
                    <div class="header_img">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4723D9&color=fff" alt="Profile">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="{{ route('admin.profile') }}">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="nav_logo">
                    <i class='bx bx-layer nav_logo-icon'></i>
                    <span class="nav_logo-name">Admin Panel</span>
                </a>
                <div class="nav_list">
                    <a href="{{ route('admin.dashboard') }}" class="nav_link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class='bx bx-grid-alt nav_icon'></i>
                        <span class="nav_name">Dashboard</span>
                    </a>

                    <!-- Zones Management -->
                    <div class="nav_item">
                        <a href="#" class="nav_link" data-bs-toggle="collapse" data-bs-target="#zonesSubmenu">
                            <i class='bx bx-map nav_icon'></i>
                            <span class="nav_name">Zones</span>
                        </a>
                        <div class="collapse nav_sub" id="zonesSubmenu">
                            <a href="{{ route('admin.zones.index') }}" class="nav_link">All Zones</a>
                            <a href="{{ route('admin.zones.create') }}" class="nav_link">Add Zone</a>
                            <a href="{{ route('admin.zones.assignments') }}" class="nav_link">Driver Assignments</a>
                        </div>
                    </div>

                    <!-- Locations Management -->
                    <div class="nav_item">
                        <a href="#" class="nav_link" data-bs-toggle="collapse" data-bs-target="#locationsSubmenu">
                            <i class='bx bx-pin nav_icon'></i>
                            <span class="nav_name">Locations</span>
                        </a>
                        <div class="collapse nav_sub" id="locationsSubmenu">
                            <a href="{{ route('admin.locations.index') }}" class="nav_link">All Locations</a>
                            <a href="{{ route('admin.locations.create') }}" class="nav_link">Add Location</a>
                            <a href="{{ route('admin.locations.import') }}" class="nav_link">Import Locations</a>
                        </div>
                    </div>

                    <!-- Drivers Management -->
                    <div class="nav_item">
                        <a href="#" class="nav_link" data-bs-toggle="collapse" data-bs-target="#driversSubmenu">
                            <i class='bx bx-user nav_icon'></i>
                            <span class="nav_name">Drivers</span>
                        </a>
                        <div class="collapse nav_sub" id="driversSubmenu">
                            <a href="{{ route('admin.drivers.index') }}" class="nav_link">All Drivers</a>
                            <a href="{{ route('admin.drivers.create') }}" class="nav_link">Add Driver</a>
                            <a href="{{ route('admin.drivers.performance') }}" class="nav_link">Performance</a>
                        </div>
                    </div>

                    <!-- Reports & Logs -->
                    <div class="nav_item">
                        <a href="#" class="nav_link" data-bs-toggle="collapse" data-bs-target="#logsSubmenu">
                            <i class='bx bx-history nav_icon'></i>
                            <span class="nav_name">Reports & Logs</span>
                        </a>
                        <div class="collapse nav_sub" id="logsSubmenu">
                            <a href="{{ route('admin.activity-logs.index') }}" class="nav_link">Activity Logs</a>
                            <a href="{{ route('admin.login-logs.index') }}" class="nav_link">Login Logs</a>
                            <a href="{{ route('admin.reports.delivery') }}" class="nav_link">Delivery Reports</a>
                            <a href="{{ route('admin.reports.financial') }}" class="nav_link">Financial Reports</a>
                            <a href="{{ route('admin.reports.performance') }}" class="nav_link">Performance Reports</a>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="nav_item">
                        <a href="#" class="nav_link" data-bs-toggle="collapse" data-bs-target="#settingsSubmenu">
                            <i class='bx bx-cog nav_icon'></i>
                            <span class="nav_name">Settings</span>
                        </a>
                        <div class="collapse nav_sub" id="settingsSubmenu">
                            <a href="{{ route('admin.settings.general') }}" class="nav_link">General Settings</a>
                            <a href="{{ route('admin.settings.notifications') }}" class="nav_link">Notifications</a>
                            <a href="{{ route('admin.settings.api') }}" class="nav_link">API Settings</a>
                        </div>
                    </div>
                </div>
            </div>
            <a href="#" class="nav_link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class='bx bx-log-out nav_icon'></i>
                <span class="nav_name">Logout</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            const showNavbar = (toggleId, navId, bodyId, headerId) =>{
                const toggle = document.getElementById(toggleId),
                nav = document.getElementById(navId),
                bodypd = document.getElementById(bodyId),
                headerpd = document.getElementById(headerId)

                if(toggle && nav && bodypd && headerpd){
                    toggle.addEventListener('click', ()=>{
                        nav.classList.toggle('show-nav')
                        toggle.classList.toggle('bx-x')
                        bodypd.classList.toggle('body-pd')
                        headerpd.classList.toggle('body-pd')
                    })
                }
            }

            showNavbar('header-toggle','nav-bar','body-pd','header')

            // Keep submenu open for active items
            const activeLink = document.querySelector('.nav_link.active')
            if (activeLink) {
                const submenu = activeLink.closest('.nav_sub')
                if (submenu) {
                    submenu.classList.add('show')
                }
            }
        });

        // SweetAlert2 confirmation for delete actions
        $(document).on('click', '.delete-confirm', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4723D9',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Toast notifications
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        @endif
    </script>
    @stack('scripts')
</body>
</html>
