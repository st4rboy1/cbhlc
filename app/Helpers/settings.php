<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get or set a setting value.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return app(Setting::class);
        }

        return Setting::get($key, $default);
    }
}

if (! function_exists('setting_set')) {
    /**
     * Set a setting value.
     */
    function setting_set(
        string $key,
        mixed $value,
        ?string $type = null,
        ?string $description = null,
        bool $isPublic = false
    ): bool {
        return Setting::set($key, $value, $type, $description, $isPublic);
    }
}

if (! function_exists('setting_has')) {
    /**
     * Check if a setting exists.
     */
    function setting_has(string $key): bool
    {
        return Setting::has($key);
    }
}

if (! function_exists('setting_forget')) {
    /**
     * Delete a setting.
     */
    function setting_forget(string $key): bool
    {
        return Setting::forget($key);
    }
}

if (! function_exists('public_settings')) {
    /**
     * Get all public settings.
     */
    function public_settings(): \Illuminate\Support\Collection
    {
        return Setting::getPublic();
    }
}
