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

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #8B5CF6;
            --primary-hover: #7C3AED;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #6B7280;
            font-size: 0.875rem;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #D1D5DB;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-weight: 500;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .alert {
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .firebase-login {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #E5E7EB;
        }

        .firebase-login-title {
            text-align: center;
            font-size: 0.875rem;
            color: #6B7280;
            margin-bottom: 1rem;
        }

        .btn-firebase {
            background-color: #4285F4;
            border-color: #4285F4;
            color: white;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-weight: 500;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-firebase:hover {
            background-color: #3367D6;
            border-color: #3367D6;
            color: white;
        }

        .btn-firebase i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card mx-auto">
            <div class="login-header">
                @if(config('app.logo'))
                    <img src="{{ asset(config('app.logo')) }}" alt="Logo" class="login-logo">
                @endif
                <h1 class="login-title">{{ config('app.name', 'Delivery Management') }}</h1>
                <p class="login-subtitle">Please sign in to your account</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ secure_url('login') }}" id="login-form">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           autocomplete="username" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" autocomplete="current-password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <span class="d-flex align-items-center justify-content-center">
                        <span>Log In</span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </span>
                </button>

                @if(config('services.firebase.enabled'))
                    <div class="firebase-login">
                        <p class="firebase-login-title">Or sign in as driver with</p>
                        <button type="button" class="btn btn-firebase" onclick="signInWithFirebase()">
                            <i class="mdi mdi-google"></i>
                            Continue with Google
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const submitButton = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function(e) {
                // Get spinner element
                const spinner = submitButton.querySelector('.spinner-border');
                const buttonText = submitButton.querySelector('span:not(.spinner-border)');
                
                // Disable button and show loading state
                submitButton.disabled = true;
                spinner.classList.remove('d-none');
                buttonText.textContent = 'Logging in...';
            });

            // Show any error messages
            const errorContainer = document.querySelector('.alert-danger');
            if (errorContainer) {
                errorContainer.setAttribute('role', 'alert');
                errorContainer.setAttribute('aria-live', 'polite');
            }
        });
    </script>

    @if(config('services.firebase.enabled'))
        <script src="https://www.gstatic.com/firebasejs/9.x.x/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/9.x.x/firebase-auth.js"></script>
        <script>
            // Initialize Firebase
            const firebaseConfig = {
                apiKey: "{{ config('services.firebase.api_key') }}",
                authDomain: "{{ config('services.firebase.auth_domain') }}",
                projectId: "{{ config('services.firebase.project_id') }}",
                storageBucket: "{{ config('services.firebase.storage_bucket') }}",
                messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
                appId: "{{ config('services.firebase.app_id') }}"
            };

            firebase.initializeApp(firebaseConfig);

            function signInWithFirebase() {
                const provider = new firebase.auth.GoogleAuthProvider();
                firebase.auth().signInWithPopup(provider)
                    .then((result) => {
                        // Get the ID token
                        return result.user.getIdToken();
                    })
                    .then((idToken) => {
                        // Send the token to your backend
                        return fetch('/api/auth/verify-phone', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                firebase_token: idToken
                            })
                        });
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.token) {
                            // Store the token and redirect
                            localStorage.setItem('auth_token', data.token);
                            window.location.href = '/driver/dashboard';
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('Authentication failed. Please try again.');
                    });
            }
        </script>
    @endif
</body>
</html>
