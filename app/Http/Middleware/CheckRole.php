<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Convert single role to array
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Required roles: ' . implode(', ', $roles),
                    'required_roles' => $roles,
                    'user_role' => $user->role
                ], 403);
            }

            // Redirect based on user's role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'You do not have permission to access that page.');
                case 'driver':
                    return redirect()->route('driver.dashboard')
                        ->with('error', 'You do not have permission to access that page.');
                default:
                    return redirect()->route('login')
                        ->with('error', 'You do not have permission to access that page.');
            }
        }

        // Check if user is active
        if ($user->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account is not active.',
                    'status' => $user->status
                ], 403);
            }

            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your account is not active. Please contact support.');
        }

        // Add role information to request for controllers
        $request->merge([
            'user_role' => $user->role,
            'role_permissions' => $this->getRolePermissions($user->role)
        ]);

        return $next($request);
    }

    /**
     * Get permissions for a given role
     *
     * @param  string  $role
     * @return array
     */
    protected function getRolePermissions($role)
    {
        $permissions = [
            'admin' => [
                'manage_drivers',
                'manage_zones',
                'manage_locations',
                'view_reports',
                'manage_settings',
                'view_activity_logs',
                'export_data',
                'import_data',
            ],
            'driver' => [
                'view_assigned_zones',
                'view_locations',
                'update_location_status',
                'update_position',
                'record_payment',
                'view_own_activities',
            ],
        ];

        return $permissions[$role] ?? [];
    }
}
