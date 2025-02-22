<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'Delivery Management System',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Application name displayed in the interface',
            ],
            [
                'key' => 'company_name',
                'value' => 'Your Company Name',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Company name used in documents and reports',
            ],

            // Branding Settings
            [
                'key' => 'primary_color',
                'value' => '#4f46e5',
                'group' => 'branding',
                'type' => 'color',
                'description' => 'Primary color used in the application interface',
            ],
            [
                'key' => 'secondary_color',
                'value' => '#7c3aed',
                'group' => 'branding',
                'type' => 'color',
                'description' => 'Secondary color used in the application interface',
            ],

            // Maps Configuration
            [
                'key' => 'google_maps_api_key',
                'value' => '',
                'group' => 'maps',
                'type' => 'string',
                'description' => 'Google Maps API key for map integration',
            ],
            [
                'key' => 'default_latitude',
                'value' => '5.6037',
                'group' => 'maps',
                'type' => 'string',
                'description' => 'Default latitude for map center (Accra)',
            ],
            [
                'key' => 'default_longitude',
                'value' => '-0.1870',
                'group' => 'maps',
                'type' => 'string',
                'description' => 'Default longitude for map center (Accra)',
            ],

            // Notification Settings
            [
                'key' => 'enable_email_notifications',
                'value' => 'true',
                'group' => 'notification',
                'type' => 'boolean',
                'description' => 'Enable or disable email notifications',
            ],
            [
                'key' => 'enable_sms_notifications',
                'value' => 'true',
                'group' => 'notification',
                'type' => 'boolean',
                'description' => 'Enable or disable SMS notifications',
            ],
            [
                'key' => 'notification_sender_email',
                'value' => 'noreply@example.com',
                'group' => 'notification',
                'type' => 'string',
                'description' => 'Email address used to send notifications',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::create($setting);
        }
    }
}
