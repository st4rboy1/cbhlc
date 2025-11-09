<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'system_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Cache key for settings.
     */
    public const CACHE_KEY = 'system_settings';

    /**
     * Cache TTL in seconds (24 hours).
     */
    public const CACHE_TTL = 86400;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * Get a setting value by key with type casting.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::all()->keyBy('key');
        });

        $setting = $settings->get($key);

        if (! $setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key.
     */
    public static function set(
        string $key,
        mixed $value,
        ?string $type = null,
        ?string $description = null,
        bool $isPublic = false
    ): bool {
        $type = $type ?? self::inferType($value);
        $valueString = self::valueToString($value, $type);

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valueString,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );

        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Check if a setting exists.
     */
    public static function has(string $key): bool
    {
        $settings = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::all()->keyBy('key');
        });

        return $settings->has($key);
    }

    /**
     * Forget a setting by key.
     */
    public static function forget(string $key): bool
    {
        $deleted = self::where('key', $key)->delete();
        Cache::forget(self::CACHE_KEY);

        return $deleted > 0;
    }

    /**
     * Get all public settings.
     */
    public static function getPublic(): \Illuminate\Support\Collection
    {
        return Cache::remember(self::CACHE_KEY.'_public', self::CACHE_TTL, function () {
            return self::where('is_public', true)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => self::castValue($setting->value, $setting->type)];
                });
        });
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache(): bool
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY.'_public');

        return true;
    }

    /**
     * Cast a value to its appropriate type.
     */
    protected static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer', 'int' => (int) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'float', 'double' => (float) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Convert a value to string for storage.
     */
    protected static function valueToString(mixed $value, string $type): string
    {
        return match ($type) {
            'array', 'json' => json_encode($value),
            'boolean', 'bool' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    /**
     * Infer the type from a value.
     */
    protected static function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }
}
