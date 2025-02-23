<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Maintenance Mode</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    
    <style>
        :root {
            --primary-color: #4723D9;
            --primary-color-dark: #3b1bb3;
            --body-font: 'Inter', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--body-font);
            background-color: #f8f9fa;
            color: #2D3748;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            line-height: 1.6;
        }

        .maintenance-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            animation: fadeIn 0.5s ease-out;
        }

        .maintenance-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        .maintenance-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2D3748;
        }

        .maintenance-message {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .maintenance-info {
            background-color: rgba(71, 35, 217, 0.1);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .maintenance-info h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .maintenance-info p {
            color: #4a5568;
            font-size: 0.95rem;
        }

        .maintenance-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            background-color: #dc3545;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        .status-text {
            font-weight: 500;
            color: #dc3545;
        }

        .retry-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            color: #fff;
            background-color: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .retry-button:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-1px);
        }

        .retry-button i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes blink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        @media (max-width: 576px) {
            .maintenance-container {
                padding: 1.5rem;
            }

            .maintenance-icon {
                font-size: 3rem;
            }

            .maintenance-title {
                font-size: 1.5rem;
            }

            .maintenance-message {
                font-size: 1rem;
            }
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1a202c;
                color: #f7fafc;
            }

            .maintenance-title {
                color: #f7fafc;
            }

            .maintenance-message {
                color: #cbd5e0;
            }

            .maintenance-info {
                background-color: rgba(71, 35, 217, 0.2);
            }

            .maintenance-info p {
                color: #cbd5e0;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <i class='bx bx-wrench maintenance-icon'></i>
        <h1 class="maintenance-title">We'll Be Right Back!</h1>
        <p class="maintenance-message">
            {{ $exception->getMessage() ?: 'We are currently performing scheduled maintenance. Please check back soon.' }}
        </p>

        <div class="maintenance-info">
            <h3>What's Happening?</h3>
            <p>Our team is working on improving your experience. This maintenance window helps us keep our services running smoothly and securely.</p>
        </div>

        <div class="maintenance-status">
            <span class="status-indicator"></span>
            <span class="status-text">System Maintenance in Progress</span>
        </div>

        @if(isset($retryAfter))
            <p class="maintenance-message">
                Expected completion time: {{ now()->addSeconds($retryAfter)->diffForHumans() }}
            </p>
        @endif

        <a href="{{ url('/') }}" class="retry-button">
            <i class='bx bx-refresh'></i>
            Try Again
        </a>
    </div>

    @if(app()->environment('local'))
        <script>
            // Auto refresh in local environment
            setTimeout(() => {
                window.location.reload();
            }, 5000);
        </script>
    @endif
</body>
</html>
