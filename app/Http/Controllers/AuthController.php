<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        try {
            if (Auth::check()) {
                return $this->redirectBasedOnRole();
            }
            return view('auth.login');
        } catch (\Exception $e) {
            Log::error('Error showing login form: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required'
            ], [
                'email.exists' => 'No account found with this email address.'
            ]);

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();

                // Record login log
                try {
                    LoginLog::create([
                        'user_id' => auth()->id(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'login_at' => now(),
                        'location_data' => [
                            'city' => 'Unknown',
                            'country' => 'Unknown'
                        ]
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create login log: ' . $e->getMessage());
                }

                return $this->redirectBasedOnRole();
            }

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput($request->only('email', 'remember'));
        } catch (\Exception $e) {
            Log::error('Error during login: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login');
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        try {
            $this->authorize('admin');
            return view('admin.dashboard');
        } catch (\Exception $e) {
            Log::error('Error accessing admin dashboard: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Show driver dashboard
     */
    public function driverDashboard()
    {
        try {
            $this->authorize('driver');
            return view('driver.dashboard');
        } catch (\Exception $e) {
            Log::error('Error accessing driver dashboard: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Show profile page
     */
    public function showProfile()
    {
        try {
            return view('admin.profile');
        } catch (\Exception $e) {
            Log::error('Error showing profile: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone_number' => 'required|string|unique:users,phone_number,' . $user->id,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user->update($validator->validated());

            return redirect()->back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'The current password is incorrect.'])
                    ->withInput();
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return redirect()->back()->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating password: ' . $e->getMessage());
            return $this->handleError($e);
        }
    }

    /**
     * Redirect based on user role
     */
    protected function redirectBasedOnRole()
    {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('driver.dashboard');
    }

    /**
     * Handle error response
     */
    protected function handleError(\Exception $e)
    {
        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return redirect()->route('login')
                ->with('error', 'You are not authorized to access this page.');
        }

        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'An error occurred. Please try again.'
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'An error occurred. Please try again.')
            ->withInput();
    }
}
