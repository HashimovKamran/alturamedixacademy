<?php

namespace App\Services\Site;

use App\Models\Setting;

class SettingService
{
    private array $cache = [];

    public function get(string $language, string $key, string $default = ''): string
    {
        $settings = $this->all($language);
        return (string) ($settings[$key] ?? $default);
    }

    public function all(string $language): array
    {
        if (!isset($this->cache[$language])) {
            $this->cache[$language] = Setting::query()
                ->where('lang_code', $language)
                ->pluck('setting_value', 'setting_key')
                ->map(static fn ($value) => (string) $value)
                ->all();
        }

        return $this->cache[$language];
    }
}