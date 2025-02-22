<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description'
    ];

    /**
     * Get the setting value by key.
     *
     * @param string $key
     * @return mixed
     */
    public static function getValue(string $key)
    {
        return static::where('key', $key)->value('value');
    }

    /**
     * Set the setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setValue(string $key, $value)
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
