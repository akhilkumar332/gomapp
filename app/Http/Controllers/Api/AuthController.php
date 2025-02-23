<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Verify phone number and create/update user
     */
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'firebase_token' => 'required|string',
            'device_token' => 'nullable|string'
        ]);

        // Verify Firebase token
        $firebaseUser = $this->firebaseService->verifyIdToken($request->firebase_token);
        if (!$firebaseUser) {
            throw ValidationException::withMessages([
                'firebase_token' => ['Invalid Firebase token.']
            ]);
        }

        // Find or create user
        $user = User::where('phone_number', $firebaseUser['phone_number'])
            ->orWhere('firebase_uid', $firebaseUser['uid'])
            ->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Driver ' . Str::random(6),
                'email' => Str::random(10) . '@driver.com',
                'password' => Hash::make(Str::random(16)),
                'phone_number' => $firebaseUser['phone_number'],
                'firebase_uid' => $firebaseUser['uid'],
                'role' => 'driver',
                'phone_verified' => true,
                'phone_verified_at' => now(),
                'device_token' => $request->device_token,
            ]);
        } else {
            $user->update([
                'firebase_uid' => $firebaseUser['uid'],
                'phone_verified' => true,
                'phone_verified_at' => now(),
                'device_token' => $request->device_token,
            ]);
        }

        // Create token
        $token = $user->createToken('driver-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'phone_verified' => $user->phone_verified,
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'device_token' => 'nullable|string'
        ]);

        $user = $request->user();
        $user->update([
            'name' => $request->name,
            'device_token' => $request->device_token
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'phone_verified' => $user->phone_verified,
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->update(['device_token' => null]);
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'phone_verified' => $user->phone_verified,
            ]
        ]);
    }
}
