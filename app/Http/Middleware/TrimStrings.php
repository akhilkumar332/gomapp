<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
        'old_password',
        'new_password',
        'new_password_confirmation',
        'api_token',
        'access_token',
        'refresh_token',
        '_token',
        '_method',
        'signature',
        'signed_url',
        'webhook_secret',
        'encryption_key',
        'private_key',
        'public_key',
        'secret_key',
        'client_secret',
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true) || !is_string($value)) {
            return $value;
        }

        $value = trim($value);

        // Convert empty strings to null
        if ($value === '') {
            return null;
        }

        // Remove multiple spaces
        $value = preg_replace('/\s+/', ' ', $value);

        // Remove invisible characters
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

        return $value;
    }
}
