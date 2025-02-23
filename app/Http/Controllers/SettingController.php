<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Show general settings
     */
    public function general()
    {
        $settings = AppSetting::whereIn('key', [
            'app_name',
            'company_name',
            'contact_email',
            'contact_phone',
            'primary_color',
            'secondary_color',
            'logo_path',
            'favicon_path'
        ])->pluck('value', 'key');

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:20',
            'primary_color' => 'required|string|size:7|starts_with:#',
            'secondary_color' => 'required|string|size:7|starts_with:#',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('public/images');
            AppSetting::updateOrCreate(
                ['key' => 'logo_path'],
                ['value' => str_replace('public/', 'storage/', $logoPath)]
            );
        }

        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('public/images');
            AppSetting::updateOrCreate(
                ['key' => 'favicon_path'],
                ['value' => str_replace('public/', 'storage/', $faviconPath)]
            );
        }

        // Update other settings
        $settings = $request->only([
            'app_name',
            'company_name',
            'contact_email',
            'contact_phone',
            'primary_color',
            'secondary_color'
        ]);

        foreach ($settings as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Clear cache
        Cache::tags(['settings'])->flush();

        ActivityLog::log(
            'settings.update',
            'Updated general settings'
        );

        return back()->with('success', 'Settings updated successfully');
    }

    /**
     * Show notification settings
     */
    public function notifications()
    {
        $settings = AppSetting::whereIn('key', [
            'email_notifications',
            'push_notifications',
            'sms_notifications',
            'notification_email',
            'firebase_server_key'
        ])->pluck('value', 'key');

        return view('admin.settings.notifications', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_notifications' => 'required|boolean',
            'push_notifications' => 'required|boolean',
            'sms_notifications' => 'required|boolean',
            'notification_email' => 'required_if:email_notifications,true|email|nullable',
            'firebase_server_key' => 'required_if:push_notifications,true|string|nullable'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = $request->only([
            'email_notifications',
            'push_notifications',
            'sms_notifications',
            'notification_email',
            'firebase_server_key'
        ]);

        foreach ($settings as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::tags(['settings'])->flush();

        ActivityLog::log(
            'settings.update',
            'Updated notification settings'
        );

        return back()->with('success', 'Notification settings updated successfully');
    }

    /**
     * Show API settings
     */
    public function api()
    {
        $settings = AppSetting::whereIn('key', [
            'ghana_post_gps_api_key',
            'ghana_post_gps_api_url',
            'google_maps_api_key',
            'api_rate_limit',
            'api_token_expiry'
        ])->pluck('value', 'key');

        return view('admin.settings.api', compact('settings'));
    }

    /**
     * Update API settings
     */
    public function updateApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ghana_post_gps_api_key' => 'required|string',
            'ghana_post_gps_api_url' => 'required|url',
            'google_maps_api_key' => 'required|string',
            'api_rate_limit' => 'required|integer|min:1',
            'api_token_expiry' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = $request->only([
            'ghana_post_gps_api_key',
            'ghana_post_gps_api_url',
            'google_maps_api_key',
            'api_rate_limit',
            'api_token_expiry'
        ]);

        foreach ($settings as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::tags(['settings'])->flush();

        ActivityLog::log(
            'settings.update',
            'Updated API settings'
        );

        return back()->with('success', 'API settings updated successfully');
    }
}
