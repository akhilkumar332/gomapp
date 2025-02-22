<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AppSettingController extends Controller
{
    /**
     * Display a listing of application settings.
     */
    public function index()
    {
        // Only admins can view all settings, drivers get only public settings
        $query = AppSetting::query();

        if (Auth::user()->isDriver()) {
            $query->where('group', 'public');
        }

        $settings = $query->get()
            ->groupBy('group')
            ->map(function ($items) {
                return $items->pluck('value', 'key');
            });

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update application settings
     */
    public function update(Request $request)
    {
        // Only admins can update settings
        if (!Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized to update settings');
        }

        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
            'settings.*.group' => 'required|string',
            'settings.*.type' => 'required|string|in:string,number,boolean,color,json'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        foreach ($request->settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type']
                ]
            );
        }

        // Clear the cache
        Cache::forget('app.settings');
        Cache::forget('app.branding');

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => 'Updated application settings',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    /**
     * Update application branding
     */
    public function updateBranding(Request $request)
    {
        // Only admins can update branding
        if (!Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized to update branding');
        }

        $validator = Validator::make($request->all(), [
            'app_name' => 'sometimes|required|string|max:255',
            'primary_color' => 'sometimes|required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'secondary_color' => 'sometimes|required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'accent_color' => 'sometimes|required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'logo' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'sometimes|required|image|mimes:ico,png|max:1024'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('public/branding');
            AppSetting::updateOrCreate(
                ['key' => 'logo_url'],
                ['value' => asset(str_replace('public', 'storage', $logoPath)), 'group' => 'branding', 'type' => 'string']
            );
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('public/branding');
            AppSetting::updateOrCreate(
                ['key' => 'favicon_url'],
                ['value' => asset(str_replace('public', 'storage', $faviconPath)), 'group' => 'branding', 'type' => 'string']
            );
        }

        // Update other branding settings
        $brandingSettings = array_filter($request->only([
            'app_name',
            'primary_color',
            'secondary_color',
            'accent_color'
        ]));

        foreach ($brandingSettings as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'branding', 'type' => $key === 'app_name' ? 'string' : 'color']
            );
        }

        // Clear the cache
        Cache::forget('app.branding');

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => 'Updated application branding',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Branding updated successfully');
    }

    /**
     * Get application branding settings
     */
    public function getBranding()
    {
        $branding = Cache::remember('app.branding', 3600, function () {
            return AppSetting::where('group', 'branding')
                ->get()
                ->pluck('value', 'key');
        });

        return response()->json($branding);
    }

    /**
     * Get specific setting by key
     */
    public function show($key)
    {
        $setting = AppSetting::where('key', $key)->firstOrFail();

        // Check if driver can access this setting
        if (Auth::user()->isDriver() && $setting->group !== 'public') {
            return redirect()->back()->with('error', 'Unauthorized to view this setting');
        }

        return response()->json($setting);
    }

    /**
     * Delete a setting
     */
    public function destroy($key)
    {
        // Only admins can delete settings
        if (!Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized to delete settings');
        }

        $setting = AppSetting::where('key', $key)->firstOrFail();
        $setting->delete();

        // Clear the cache if it's a branding setting
        if ($setting->group === 'branding') {
            Cache::forget('app.branding');
        }

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'description' => "Deleted application setting: {$key}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return redirect()->back()->with('success', 'Setting deleted successfully');
    }
}
