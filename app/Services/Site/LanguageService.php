<?php

namespace App\Services\Site;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LanguageService
{
    public function active(): Collection
    {
        return Language::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function defaultCode(): string
    {
        return (string) (Language::query()->active()->where('is_default', true)->value('code') ?: 'az');
    }

    public function currentCode(Request $request): string
    {
        $requested = strtolower((string) ($request->route('lang') ?: $request->query('lang', '')));
        $validCodes = $this->active()->pluck('code')->all();

        if ($requested !== '' && in_array($requested, $validCodes, true)) {
            $request->session()->put('site_lang', $requested);
            return $requested;
        }

        $sessionLanguage = (string) $request->session()->get('site_lang', '');
        if ($sessionLanguage !== '' && in_array($sessionLanguage, $validCodes, true)) {
            return $sessionLanguage;
        }

        return $this->defaultCode();
    }

    public function switchUrl(Request $request, string $language): string
    {
        $query = $request->query();
        $query['lang'] = $language;

        return $request->url() . '?' . http_build_query($query);
    }
}
