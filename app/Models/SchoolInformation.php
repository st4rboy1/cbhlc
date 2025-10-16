<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SchoolInformation extends Model
{
    use LogsActivity;

    protected $table = 'school_information';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value', 'type', 'group', 'label', 'description', 'order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('school_information');
        });

        static::deleted(function () {
            Cache::forget('school_information');
        });
    }

    /**
     * Scope to filter by group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to get ordered items
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }

    /**
     * Get all school information as a cached collection
     */
    public static function getAllCached()
    {
        return Cache::remember('school_information', 3600, function () {
            return static::ordered()->get();
        });
    }

    /**
     * Get school information by key
     */
    public static function getByKey(string $key, $default = null)
    {
        $item = static::getAllCached()->firstWhere('key', $key);

        return $item ? $item->value : $default;
    }

    /**
     * Set school information by key
     */
    public static function setByKey(string $key, $value): bool
    {
        $item = static::where('key', $key)->first();

        if ($item) {
            $item->update(['value' => $value]);

            return true;
        }

        return false;
    }

    /**
     * Get all information grouped by category
     */
    public static function getGrouped()
    {
        return static::getAllCached()->groupBy('group');
    }

    /**
     * Get information by group
     */
    public static function getByGroup(string $group)
    {
        return static::getAllCached()->where('group', $group)->sortBy('order')->values();
    }
}
