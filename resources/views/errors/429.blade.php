<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Too Many Requests</title>
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

        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 2rem;
            animation: fadeIn 0.5s ease-out;
        }

        .error-icon {
            font-size: 4rem;
            color: #e67e22;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
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

        .countdown {
            background-color: rgba(230, 126, 34, 0.1);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .countdown-timer {
            font-size: 2.5rem;
            font-weight: 700;
            color: #e67e22;
            margin: 1rem 0;
        }

        .error-details {
            background-color: rgba(230, 126, 34, 0.1);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .error-details h3 {
            color: #e67e22;
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
            color: #e67e22;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background-color: rgba(230, 126, 34, 0.2);
            border-radius: 2px;
            margin: 1rem 0;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: #e67e22;
            border-radius: 2px;
            width: 100%;
            animation: countdown linear forwards;
        }

        .btn {
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

        .btn:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-1px);
        }

        .btn i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        .btn.disabled {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
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

        @keyframes countdown {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
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

            .countdown-timer {
                font-size: 2rem;
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
                background-color: rgba(230, 126, 34, 0.2);
            }

            .error-details li {
                color: #cbd5e0;
            }

            .countdown {
                background-color: rgba(230, 126, 34, 0.2);
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class='bx bx-time-five error-icon'></i>
        <h1 class="error-title">Too Many Requests</h1>
        <p class="error-message">
            {{ $exception->getMessage() ?: 'You have exceeded the rate limit. Please wait before trying again.' }}
        </p>

        <div class="countdown">
            <h3>Please wait</h3>
            <div class="countdown-timer" id="countdown">
                <span id="seconds">60</span>s
            </div>
            <div class="progress-bar">
                <div class="progress-bar-fill" id="progress-bar" style="animation-duration: 60s;"></div>
            </div>
            <p>Until next attempt is allowed</p>
        </div>

        <div class="error-details">
            <h3>Why am I seeing this?</h3>
            <ul>
                <li>Too many requests in a short time period</li>
                <li>Multiple failed login attempts</li>
                <li>Automated or suspicious activity detected</li>
                <li>API rate limit exceeded</li>
            </ul>
        </div>

        <button class="btn disabled" id="retry-btn">
            <i class='bx bx-refresh'></i>
            Try Again (<span id="retry-countdown">60</span>s)
        </button>
    </div>

    <script>
        // Countdown timer
        const countdownDisplay = document.getElementById('seconds');
        const retryCountdown = document.getElementById('retry-countdown');
        const retryBtn = document.getElementById('retry-btn');
        const progressBar = document.getElementById('progress-bar');
        
        let timeLeft = 60;
        
        const countdown = setInterval(() => {
            timeLeft--;
            countdownDisplay.textContent = timeLeft;
            retryCountdown.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                retryBtn.classList.remove('disabled');
                retryBtn.innerHTML = '<i class="bx bx-refresh"></i>Try Again';
                retryBtn.addEventListener('click', () => {
                    window.location.reload();
                });
            }
        }, 1000);

        // Handle back button cache
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    </script>
</body>
</html>
