<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("app_setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (! $setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value): bool
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return false;
        }

        // Convert value based on type
        if ($setting->type === 'boolean') {
            $value = $value ? '1' : '0';
        } elseif ($setting->type === 'json') {
            $value = json_encode($value);
        }

        $setting->update(['value' => $value]);

        // Clear cache
        Cache::forget("app_setting_{$key}");
        Cache::forget("app_settings_group_{$setting->group}");

        return true;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        $settings = Cache::remember("app_settings_group_{$group}", 3600, function () use ($group) {
            return static::where('group', $group)->get();
        });

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = self::castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("app_setting_{$key}");
        }

        $groups = static::distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("app_settings_group_{$group}");
        }
    }
}
