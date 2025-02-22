<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Auth as FirebaseAuth;

class AuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * Admin login
     */
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            Auth::logout();
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Log the login
        LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => now(),
        ]);

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Driver login with Firebase Phone Auth
     */
    public function driverLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // Send OTP via Firebase
            $verification = $this->firebaseAuth->signInWithPhoneNumber($request->phone_number);
            
            return response()->json([
                'verification_id' => $verification->verificationId(),
                'message' => 'OTP sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP and complete driver login
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'verification_id' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // Verify OTP with Firebase
            $this->firebaseAuth->verifyIdToken($request->verification_id);

            // Find or create user
            $user = User::where('phone_number', $request->phone_number)
                       ->where('role', 'driver')
                       ->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized. Please contact admin for registration.'
                ], 403);
            }

            // Log the login
            LoginLog::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
            ]);

            $token = $user->createToken('driver-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid OTP',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        if ($user->isDriver()) {
            $user->load('zones.locations');
        }

        return response()->json($user);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|required|string|unique:users,phone_number,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
