<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Delivery Management') }} - Maintenance Mode</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #8B5CF6;
            --primary-hover: #7C3AED;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1F2937;
            line-height: 1.5;
        }

        .maintenance-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 32rem;
            padding: 2rem;
            text-align: center;
            margin: 1rem;
        }

        .icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1F2937;
        }

        .message {
            color: #4B5563;
            margin-bottom: 1.5rem;
        }

        .info {
            background-color: #F3F4F6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #E5E7EB;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6B7280;
            font-size: 0.875rem;
        }

        .info-value {
            color: #1F2937;
            font-weight: 500;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        .footer {
            margin-top: 2rem;
            color: #6B7280;
            font-size: 0.875rem;
        }

        .footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .maintenance-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .icon {
                font-size: 3rem;
            }

            .title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <i class="mdi mdi-tools icon"></i>
        
        <h1 class="title">System Maintenance</h1>
        
        <p class="message">
            {{ $message ?? 'We are currently performing scheduled maintenance to improve our service.' }}
            <br>
            Please check back soon.
        </p>

        <div class="info">
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value">Under Maintenance</span>
            </div>
            
            @if($estimatedDuration)
            <div class="info-item">
                <span class="info-label">Estimated Duration</span>
                <span class="info-value">{{ $estimatedDuration }}</span>
            </div>
            @endif
            
            @if($whenAvailable)
            <div class="info-item">
                <span class="info-label">Expected Back</span>
                <span class="info-value">{{ $whenAvailable }}</span>
            </div>
            @endif
        </div>

        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <i class="mdi mdi-shield-account"></i>
                    Access Admin Panel
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn btn-primary">
                <i class="mdi mdi-login"></i>
                Admin Login
            </a>
        @endauth

        <div class="footer">
            <p>If you need immediate assistance, please contact</p>
            <a href="mailto:{{ config('app.support_email', 'support@example.com') }}">
                {{ config('app.support_email', 'support@example.com') }}
            </a>
        </div>
    </div>

    @if(config('app.debug'))
        <script>
            // Auto-refresh the page periodically to check if maintenance mode is over
            setTimeout(function() {
                window.location.reload();
            }, {{ ($retryAfter ?? 60) * 1000 }});
        </script>
    @endif
</body>
</html>
