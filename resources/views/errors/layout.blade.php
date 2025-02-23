<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Error') - {{ config('app.name', 'Delivery Management') }}</title>

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

        .error-container {
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

        .error-code {
            font-size: 5rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 1rem;
            opacity: 0.5;
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

        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 2rem;
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

        .btn-secondary {
            background-color: #F3F4F6;
            color: #374151;
            border: 1px solid #D1D5DB;
        }

        .btn-secondary:hover {
            background-color: #E5E7EB;
        }

        .btn i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        .error-details {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #E5E7EB;
            font-size: 0.875rem;
            color: #6B7280;
        }

        .error-reference {
            background-color: #F3F4F6;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-family: monospace;
            margin-top: 0.5rem;
        }

        @media (max-width: 640px) {
            .error-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .icon {
                font-size: 3rem;
            }

            .error-code {
                font-size: 4rem;
            }

            .title {
                font-size: 1.25rem;
            }
        }
    </style>

    @yield('styles')
</head>
<body>
    <div class="error-container">
        @yield('icon')
        
        <div class="error-code">@yield('code')</div>
        
        <h1 class="title">@yield('title')</h1>
        
        <div class="message">
            @yield('message')
        </div>

        <div class="actions">
            @yield('actions')

            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="mdi mdi-home"></i>
                Return Home
            </a>

            @if(url()->previous() !== url()->current())
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i>
                    Go Back
                </a>
            @endif
        </div>

        @if(app()->environment('local', 'staging') || (isset($errorReference) && $errorReference))
            <div class="error-details">
                @if(app()->environment('local', 'staging'))
                    <div>{{ class_basename($exception ?? '') }}</div>
                    <div>{{ $exception->getMessage() ?? '' }}</div>
                @endif

                @if(isset($errorReference) && $errorReference)
                    <div>Error Reference:</div>
                    <div class="error-reference">{{ $errorReference }}</div>
                @endif
            </div>
        @endif
    </div>

    @yield('scripts')
</body>
</html>
