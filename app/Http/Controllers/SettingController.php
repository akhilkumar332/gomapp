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
        $request->validate([
            'app_name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'accent_color' => 'required|string|max:7',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png|max:1024',
        ]);

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
            $path = $request->file('logo')->store('public/branding');
            AppSetting::updateOrCreate(
                ['key' => 'logo_url'],
                ['value' => Storage::url($path)]
            );
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('public/branding');
            AppSetting::updateOrCreate(
                ['key' => 'favicon_url'],
                ['value' => Storage::url($path)]
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
