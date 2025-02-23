<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title', 'Error')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    
    <style>
        :root {
            --primary-color: #4723D9;
            --primary-color-dark: #3b1bb3;
            --body-font: 'Inter', sans-serif;
            --error-color: @yield('error-color', '#dc3545');
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

        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 2rem;
            animation: fadeIn 0.5s ease-out;
        }

        .error-icon {
            font-size: 4rem;
            color: var(--error-color);
            margin-bottom: 1.5rem;
            animation: @yield('icon-animation', 'fadeIn') 1s ease-out;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2D3748;
        }

        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .error-details {
            background-color: rgba(var(--error-color-rgb), 0.1);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .error-details h3 {
            color: var(--error-color);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .error-details ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .error-details li {
            color: #4a5568;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .error-details li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: var(--error-color);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn i {
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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (max-width: 576px) {
            .error-container {
                padding: 1.5rem;
            }

            .error-icon {
                font-size: 3rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .error-message {
                font-size: 1rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1a202c;
                color: #f7fafc;
            }

            .error-title {
                color: #f7fafc;
            }

            .error-message {
                color: #cbd5e0;
            }

            .error-details {
                background-color: rgba(var(--error-color-rgb), 0.2);
            }

            .error-details li {
                color: #cbd5e0;
            }

            .btn-outline {
                border-color: #fff;
                color: #fff;
            }

            .btn-outline:hover {
                background-color: #fff;
                color: #1a202c;
            }
        }

        @yield('additional-styles')
    </style>
</head>
<body>
    <div class="error-container">
        <i class='bx @yield('icon', 'bx-error') error-icon'></i>
        <h1 class="error-title">@yield('title', 'Error')</h1>
        <p class="error-message">@yield('message')</p>

        @yield('content')

        <div class="action-buttons">
            @yield('actions')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
