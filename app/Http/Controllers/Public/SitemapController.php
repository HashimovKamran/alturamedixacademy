<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $languages = Language::query()->active()->orderBy('sort_order')->pluck('code');
        $urls = collect();
        foreach ($languages as $language) {
            foreach (['/', '/about', '/articles', '/certificates', '/trainings', '/gallery', '/contact'] as $path) {
                $urls->push(['loc' => url('/'.$language.($path === '/' ? '' : $path)), 'lastmod' => null]);
            }
        }
        Page::query()->active()->whereNotIn('page_key', ['index', 'about', 'articles', 'certificates', 'trainings', 'gallery', 'contact'])->get()->each(
            fn (Page $page) => $urls->push(['loc' => url('/'.$page->lang_code.'/pages/'.rawurlencode($page->page_key)), 'lastmod' => $page->updated_at?->toAtomString()])
        );
        Article::query()->active()->get()->each(
            fn (Article $article) => $urls->push(['loc' => url('/'.$article->lang_code.'/articles/'.rawurlencode($article->slug)), 'lastmod' => $article->updated_at?->toAtomString()])
        );

        $xml = view('public.sitemap', ['urls' => $urls->unique('loc')->values()])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
