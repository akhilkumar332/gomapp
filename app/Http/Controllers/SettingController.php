<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all()->pluck('value', 'key')->toArray();
        $settings = [
            'branding' => [
                'app_name' => $settings['app_name'] ?? config('app.name'),
                'primary_color' => $settings['primary_color'] ?? '#007bff',
                'secondary_color' => $settings['secondary_color'] ?? '#6c757d',
                'accent_color' => $settings['accent_color'] ?? '#28a745',
                'logo_url' => $settings['logo_url'] ?? null,
                'favicon_url' => $settings['favicon_url'] ?? null,
            ]
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Handle other settings updates here
        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function updateBranding(Request $request)
    {
        try {
            $request->validate([
                'app_name' => 'required|string|max:255',
                'primary_color' => 'required|string|max:7',
                'secondary_color' => 'required|string|max:7',
                'accent_color' => 'required|string|max:7',
                'logo' => 'nullable|image|max:2048',
                'favicon' => 'nullable|file|mimes:ico,png|max:1024',
            ]);

            // Create storage symbolic link if it doesn't exist
            if (!Storage::disk('public')->exists('branding')) {
                Storage::disk('public')->makeDirectory('branding');
            }

            // Update app name
            AppSetting::updateOrCreate(
                ['key' => 'app_name'],
                ['value' => $request->app_name]
            );

            // Update colors
            AppSetting::updateOrCreate(
                ['key' => 'primary_color'],
                ['value' => $request->primary_color]
            );
            AppSetting::updateOrCreate(
                ['key' => 'secondary_color'],
                ['value' => $request->secondary_color]
            );
            AppSetting::updateOrCreate(
                ['key' => 'accent_color'],
                ['value' => $request->accent_color]
            );

            // Handle logo upload
            if ($request->hasFile('logo')) {
                try {
                    // Delete old logo if exists
                    $oldLogoSetting = AppSetting::where('key', 'logo_url')->first();
                    if ($oldLogoSetting) {
                        $oldPath = str_replace('/storage', '/public', $oldLogoSetting->value);
                        if (Storage::exists($oldPath)) {
                            Storage::delete($oldPath);
                        }
                    }

                    // Store the new logo with original extension
                    $logo = $request->file('logo');
                    $extension = $logo->getClientOriginalExtension();
                    $filename = 'logo.' . $extension;
                    
                    // Store with custom filename
                    $path = $logo->storeAs('public/branding', $filename);
                    
                    // Update the URL in database
                    AppSetting::updateOrCreate(
                        ['key' => 'logo_url'],
                        ['value' => Storage::url($path)]
                    );
                } catch (\Exception $e) {
                    \Log::error('Logo upload failed: ' . $e->getMessage());
                    return redirect()->route('admin.settings.index')
                        ->with('error', 'Failed to upload logo: ' . $e->getMessage());
                }
            }

            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                try {
                    // Delete old favicon if exists
                    $oldFaviconSetting = AppSetting::where('key', 'favicon_url')->first();
                    if ($oldFaviconSetting) {
                        $oldPath = str_replace('/storage', '/public', $oldFaviconSetting->value);
                        if (Storage::exists($oldPath)) {
                            Storage::delete($oldPath);
                        }
                    }

                    // Store the new favicon with original extension
                    $favicon = $request->file('favicon');
                    $extension = $favicon->getClientOriginalExtension();
                    $filename = 'favicon.' . $extension;
                    
                    // Store with custom filename
                    $path = $favicon->storeAs('public/branding', $filename);
                    
                    // Update the URL in database
                    AppSetting::updateOrCreate(
                        ['key' => 'favicon_url'],
                        ['value' => Storage::url($path)]
                    );
                } catch (\Exception $e) {
                    \Log::error('Favicon upload failed: ' . $e->getMessage());
                    return redirect()->route('admin.settings.index')
                        ->with('error', 'Failed to upload favicon: ' . $e->getMessage());
                }
            }

            return redirect()->route('admin.settings.index')
                ->with('success', 'Branding settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Failed to update branding settings: ' . $e->getMessage());
        }
    }
}
