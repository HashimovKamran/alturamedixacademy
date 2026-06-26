<?php

namespace App\Services\Site;

use Illuminate\Http\Request;

class SeoService
{
    private const INDEXABLE_QUERY = ['key', 'slug', 'category'];

    public function canonical(Request $request, string $language): string
    {
        $query = array_intersect_key($request->query(), array_flip(self::INDEXABLE_QUERY));
        $path = '/'.trim($request->path(), '/');
        $path = preg_replace('#^/(?:az|en|ru|tr)(?=/|$)#', '', $path) ?: '/';
        if ($path === '/article' && ! empty($query['slug'])) {
            return url('/'.$language.'/articles/'.rawurlencode((string) $query['slug']));
        }
        if ($path === '/page' && ! empty($query['key'])) {
            return url('/'.$language.'/pages/'.rawurlencode((string) $query['key']));
        }
        unset($query['slug'], $query['key']);
        ksort($query);
        $localized = '/'.$language.($path === '/' ? '' : $path);

        return url($localized).($query ? '?'.http_build_query($query) : '');
    }

    public function alternate(Request $request, string $language): string
    {
        return $this->canonical($request, $language);
    }
}
