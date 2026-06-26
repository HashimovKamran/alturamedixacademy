<?php

namespace App\Services\Site;

use App\Models\VisualBlock;
use App\Models\VisualEdit;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisualEditorService
{
    public function pageKeyFromRequest(Request $request): string
    {
        $path = trim($request->path(), '/');
        $base = basename($path ?: 'index');
        $base = preg_replace('/\.php$/i', '', $base) ?: 'index';

        if ($base === 'page' && $request->filled('key')) {
            return $this->cleanKey((string) $request->query('key'));
        }

        return $this->cleanKey((string) $base);
    }

    public function cleanKey(string $key): string
    {
        $key = Str::lower(trim($key));
        $key = preg_replace('/[^a-z0-9_\-]/', '', $key) ?: '';

        return $key !== '' ? $key : 'index';
    }

    public function isPreview(Request $request): bool
    {
        return (string) $request->query('ve', '') === '1';
    }

    public function protectedSelector(string $selector): bool
    {
        $selector = Str::lower($selector);
        $protectedWords = [
            'header', 'site-header', 'header-top', 'header-tools', 'header-nav',
            'main-nav', 'nav-inner', 'nav-actions', 'brand', 'brand-logo', 'brand-text',
            'language', 'language-dropdown', 'language-current', 'language-menu',
            'flag-icon', 'social', 'social-links', 'footer', 'site-footer', 'footer-brand',
            'footer-social', 'mobile-menu', 'search-btn', 'auth-backdrop', 'site-search',
        ];

        foreach ($protectedWords as $word) {
            if (str_contains($selector, $word)) {
                return true;
            }
        }

        return false;
    }

    /** @return EloquentCollection<int, VisualEdit> */
    public function edits(string $language, string $pageKey): EloquentCollection
    {
        return VisualEdit::query()
            ->where('lang_code', $language)
            ->whereIn('page_key', ['_global', $pageKey])
            ->active()
            ->orderByRaw("CASE WHEN page_key = '_global' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get()
            ->filter(fn (VisualEdit $edit): bool => ! $this->protectedSelector((string) $edit->selector))
            ->values();
    }

    /** @return EloquentCollection<int, VisualBlock> */
    public function blocks(string $language, string $pageKey): EloquentCollection
    {
        return VisualBlock::query()
            ->where('lang_code', $language)
            ->whereIn('page_key', ['_global', $pageKey])
            ->active()
            ->orderByRaw("CASE WHEN page_key = '_global' THEN 0 ELSE 1 END")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (VisualBlock $block): bool => ! $this->protectedSelector((string) $block->target_selector))
            ->values();
    }
}
