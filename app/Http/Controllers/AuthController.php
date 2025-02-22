<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\FirebaseException;

class AuthController extends Controller
{
    protected $firebaseAuth = null;

    public function __construct()
    {
        try {
            if (config('firebase.credentials.project_id')) {
                $this->firebaseAuth = app(FirebaseAuth::class);
            }
        } catch (FirebaseException $e) {
            // Firebase is not configured, continue without it
            $this->firebaseAuth = null;
        }
    }

    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return view('auth.login'); // Adjust the view path as necessary
    }

    /**
     * Admin login
     */
    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.login');
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return redirect()->back()
                ->withErrors(['email' => 'Invalid login credentials'])
                ->withInput($request->except('password'));
        }

        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            Auth::logout();
            return redirect()->back()
                ->withErrors(['email' => 'Unauthorized access'])
                ->withInput($request->except('password'));
        }

        // Log the login
        LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'device_info' => $request->userAgent() ?? 'Unknown Device',
            'location' => 'Unknown',
            'country' => 'Unknown',
        ]);

        return redirect()->route('admin.dashboard');
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
            if (config('firebase.credentials')) {
                $verification = $this->firebaseAuth->signInWithPhoneNumber($request->phone_number);
            } else {
                return response()->json([
                    'message' => 'Firebase configuration is missing.'
                ], 500);
            }
            
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
            // Verify OTP with Firebase if configured
            if ($this->firebaseAuth) {
                $this->firebaseAuth->verifyIdToken($request->verification_id);
            } else {
                return response()->json([
                    'message' => 'Firebase configuration is missing.'
                ], 500);
            }

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
                'device_info' => $request->userAgent() ?? 'Unknown Device',
                'location' => 'Unknown',
                'country' => 'Unknown',
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Successfully logged out');
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

    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Show user profile
     */
    public function showProfile()
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }

    /**
     * Show driver dashboard
     */
    public function driverDashboard()
    {
        return view('driver.dashboard');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
