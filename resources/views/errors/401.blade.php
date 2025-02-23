@extends('errors.layout')

@section('code', '401')

@section('title', 'Unauthorized')

@section('icon')
<i class="mdi mdi-account-lock icon"></i>
@endsection

@section('message')
You need to be authenticated to access this resource.
<br>
Please log in with appropriate credentials to continue.

@if(session('error'))
    <div class="alert" style="margin-top: 1rem; padding: 1rem; background-color: #FEE2E2; color: #991B1B; border-radius: 0.5rem;">
        {{ session('error') }}
    </div>
@endif
@endsection

@section('actions')
<a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" class="btn btn-primary">
    <i class="mdi mdi-login"></i>
    Log In
</a>

@if(config('app.firebase.enabled'))
    <div style="margin-top: 1.5rem; text-align: center;">
        <div style="color: #6B7280; font-size: 0.875rem; margin-bottom: 1rem;">
            Or continue as driver with
        </div>
        <button onclick="signInWithFirebase()" class="btn btn-secondary" style="background-color: #4285F4; color: white; border: none;">
            <i class="mdi mdi-google"></i>
            Sign in with Google
        </button>
    </div>
@endif
@endsection

@section('scripts')
@if(config('app.firebase.enabled'))
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
@endsection
