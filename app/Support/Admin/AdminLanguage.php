<?php

namespace App\Support\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminLanguage
{
    private const SESSION_KEY = 'admin_lang_code';

    public static function activeLanguages(): Collection
    {
        return Language::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public static function selected(Request $request): string
    {
        return self::set($request, trim((string) $request->session()->get(self::SESSION_KEY, '')));
    }

    public static function set(Request $request, string $language): string
    {
        $language = self::normalize($language);
        $request->session()->put(self::SESSION_KEY, $language);

        return $language;
    }

    public static function normalize(string $language): string
    {
        $language = trim($language);
        if ($language !== '' && Language::query()->active()->where('code', $language)->exists()) {
            return $language;
        }

        return (string) (Language::query()->active()->where('is_default', true)->value('code') ?: 'az');
    }
}
