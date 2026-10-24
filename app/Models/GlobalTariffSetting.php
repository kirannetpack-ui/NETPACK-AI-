<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GlobalTariffSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'display_name',
        'description',
        'updated_by',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a setting value by key with optional fallback.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $setting = Cache::remember("tariff_setting_{$key}", 60, function () use ($key) {
                return self::where('setting_key', $key)->first();
            });

            return $setting ? $setting->setting_value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting value and clear cache.
     */
    public static function setValue(string $key, mixed $value, ?int $userId = null): self
    {
        $setting = self::firstOrNew(['setting_key' => $key]);
        $setting->setting_value = (string) $value;
        if ($userId) {
            $setting->updated_by = $userId;
        }
        $setting->save();

        Cache::forget("tariff_setting_{$key}");

        return $setting;
    }

    /**
     * Clear all cached settings.
     */
    public static function clearCache(): void
    {
        try {
            $keys = self::pluck('setting_key');
            foreach ($keys as $key) {
                Cache::forget("tariff_setting_{$key}");
            }
        } catch (\Throwable $e) {
            // Ignore if table not yet created
        }
    }
}
