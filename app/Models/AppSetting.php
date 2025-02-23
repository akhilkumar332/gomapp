<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group'
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::tags(['settings'])->remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @return void
     */
    public static function set(string $key, $value, ?string $group = null)
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group
            ]
        );

        Cache::tags(['settings'])->forget("setting.{$key}");
    }

    /**
     * Get all settings in a group
     *
     * @param string $group
     * @return array
     */
    public static function getGroup(string $group): array
    {
        return Cache::tags(['settings'])->remember("settings.group.{$group}", 3600, function () use ($group) {
            return static::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public static function getAll(): array
    {
        return Cache::tags(['settings'])->remember('settings.all', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear settings cache
     *
     * @return void
     */
    public static function clearCache()
    {
        Cache::tags(['settings'])->flush();
    }

    /**
     * Get theme settings
     *
     * @return array
     */
    public static function getTheme(): array
    {
        return static::getGroup('theme');
    }

    /**
     * Get API settings
     *
     * @return array
     */
    public static function getApi(): array
    {
        return static::getGroup('api');
    }

    /**
     * Get notification settings
     *
     * @return array
     */
    public static function getNotifications(): array
    {
        return static::getGroup('notifications');
    }

    /**
     * Get general settings
     *
     * @return array
     */
    public static function getGeneral(): array
    {
        return static::getGroup('general');
    }

    /**
     * Boot the model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}
