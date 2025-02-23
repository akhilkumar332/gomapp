<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginLog;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class AuthController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Create login log
            LoginLog::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success'
            ]);

            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('driver.dashboard'));
            }
        }

        // Log failed login attempt
        LoginLog::create([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'failed'
        ]);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * API Authentication Methods
     */

    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)
            ->where('role', 'admin')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $token = $user->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function verifyPhone(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firebase_token' => 'required|string',
                'device_token' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Verify Firebase token
            $firebaseUser = $this->firebaseService->verifyIdToken($request->firebase_token);

            if (!$firebaseUser) {
                return response()->json(['message' => 'Invalid Firebase token'], 401);
            }

            // Find or create user
            $user = User::where('phone_number', $firebaseUser->phoneNumber)
                ->orWhere('firebase_uid', $firebaseUser->uid)
                ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $firebaseUser->displayName ?? 'Driver',
                    'email' => $firebaseUser->email,
                    'phone_number' => $firebaseUser->phoneNumber,
                    'firebase_uid' => $firebaseUser->uid,
                    'role' => 'driver',
                    'status' => 'active',
                    'phone_verified' => true,
                    'phone_verified_at' => now(),
                ]);
            }

            // Update device token if provided
            if ($request->has('device_token')) {
                $user->update(['device_token' => $request->device_token]);
            }

            // Create token with driver abilities
            $token = $user->createToken('driver-token', ['driver'])->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentication failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();
            
            // Revoke all existing tokens
            $user->tokens()->delete();

            // Create new token
            $abilities = $user->isAdmin() ? ['admin'] : ['driver'];
            $token = $user->createToken($user->isAdmin() ? 'admin-token' : 'driver-token', $abilities);

            return response()->json([
                'token' => $token->plainTextToken
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token refresh failed'], 500);
        }
    }
}
