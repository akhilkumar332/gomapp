<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        $settings = AppSetting::all()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            })
            ->toArray();

        // Group settings by category
        $groupedSettings = [
            'app' => [
                'name' => $settings['app_name'] ?? config('app.name'),
                'timezone' => $settings['app_timezone'] ?? config('app.timezone'),
                'date_format' => $settings['app_date_format'] ?? 'Y-m-d',
                'time_format' => $settings['app_time_format'] ?? 'H:i:s',
            ],
            'theme' => [
                'primary_color' => $settings['theme_primary_color'] ?? '#007bff',
                'secondary_color' => $settings['theme_secondary_color'] ?? '#6c757d',
                'logo_url' => $settings['theme_logo_url'] ?? null,
                'favicon_url' => $settings['theme_favicon_url'] ?? null,
            ],
            'notification' => [
                'email_notifications' => filter_var($settings['notification_email_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
                'sms_notifications' => filter_var($settings['notification_sms_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
                'push_notifications' => filter_var($settings['notification_push_enabled'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            ],
            'delivery' => [
                'default_payment_method' => $settings['delivery_default_payment_method'] ?? 'cash',
                'allowed_payment_methods' => json_decode($settings['delivery_allowed_payment_methods'] ?? '["cash"]', true),
                'auto_assign_drivers' => filter_var($settings['delivery_auto_assign_drivers'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            ],
        ];

        return response()->json(['data' => $groupedSettings]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'sometimes|required|string|max:255',
            'app_timezone' => 'sometimes|required|string|timezone',
            'app_date_format' => 'sometimes|required|string|max:50',
            'app_time_format' => 'sometimes|required|string|max:50',
            'theme.primary_color' => 'sometimes|required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'theme.secondary_color' => 'sometimes|required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'notification.email_notifications' => 'sometimes|required|boolean',
            'notification.sms_notifications' => 'sometimes|required|boolean',
            'notification.push_notifications' => 'sometimes|required|boolean',
            'delivery.default_payment_method' => 'sometimes|required|string|in:cash,mobile_money,card',
            'delivery.allowed_payment_methods' => 'sometimes|required|array',
            'delivery.allowed_payment_methods.*' => 'required|string|in:cash,mobile_money,card',
            'delivery.auto_assign_drivers' => 'sometimes|required|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Update app settings
            if ($request->has('app_name')) {
                $this->updateSetting('app_name', $request->app_name);
            }
            if ($request->has('app_timezone')) {
                $this->updateSetting('app_timezone', $request->app_timezone);
            }
            if ($request->has('app_date_format')) {
                $this->updateSetting('app_date_format', $request->app_date_format);
            }
            if ($request->has('app_time_format')) {
                $this->updateSetting('app_time_format', $request->app_time_format);
            }

            // Update theme settings
            if ($request->has('theme.primary_color')) {
                $this->updateSetting('theme_primary_color', $request->input('theme.primary_color'));
            }
            if ($request->has('theme.secondary_color')) {
                $this->updateSetting('theme_secondary_color', $request->input('theme.secondary_color'));
            }

            // Update notification settings
            if ($request->has('notification.email_notifications')) {
                $this->updateSetting('notification_email_enabled', $request->input('notification.email_notifications'));
            }
            if ($request->has('notification.sms_notifications')) {
                $this->updateSetting('notification_sms_enabled', $request->input('notification.sms_notifications'));
            }
            if ($request->has('notification.push_notifications')) {
                $this->updateSetting('notification_push_enabled', $request->input('notification.push_notifications'));
            }

            // Update delivery settings
            if ($request->has('delivery.default_payment_method')) {
                $this->updateSetting('delivery_default_payment_method', $request->input('delivery.default_payment_method'));
            }
            if ($request->has('delivery.allowed_payment_methods')) {
                $this->updateSetting('delivery_allowed_payment_methods', json_encode($request->input('delivery.allowed_payment_methods')));
            }
            if ($request->has('delivery.auto_assign_drivers')) {
                $this->updateSetting('delivery_auto_assign_drivers', $request->input('delivery.auto_assign_drivers'));
            }

            // Log activity
            ActivityLog::log(
                'settings.update',
                'Updated system settings',
                $request->user()
            );

            DB::commit();

            return response()->json([
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update branding (logo and favicon)
     */
    public function updateBranding(Request $request)
    {
        $request->validate([
            'logo' => 'sometimes|required|image|mimes:jpeg,png,jpg|max:2048',
            'favicon' => 'sometimes|required|image|mimes:ico,png|max:1024',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                $oldLogo = AppSetting::where('key', 'theme_logo_url')->first();
                if ($oldLogo && Storage::exists($oldLogo->value)) {
                    Storage::delete($oldLogo->value);
                }

                // Store new logo
                $logoPath = $request->file('logo')->store('public/branding');
                $this->updateSetting('theme_logo_url', Storage::url($logoPath));
            }

            if ($request->hasFile('favicon')) {
                // Delete old favicon if exists
                $oldFavicon = AppSetting::where('key', 'theme_favicon_url')->first();
                if ($oldFavicon && Storage::exists($oldFavicon->value)) {
                    Storage::delete($oldFavicon->value);
                }

                // Store new favicon
                $faviconPath = $request->file('favicon')->store('public/branding');
                $this->updateSetting('theme_favicon_url', Storage::url($faviconPath));
            }

            // Log activity
            ActivityLog::log(
                'settings.branding.update',
                'Updated branding assets',
                $request->user()
            );

            DB::commit();

            return response()->json([
                'message' => 'Branding updated successfully',
                'data' => [
                    'logo_url' => $request->hasFile('logo') ? Storage::url($logoPath) : null,
                    'favicon_url' => $request->hasFile('favicon') ? Storage::url($faviconPath) : null,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Helper function to update a setting
     */
    private function updateSetting($key, $value)
    {
        AppSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
