<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Site\SiteDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request, SiteDataService $site): View
    {
        $language = $site->language($request);
        $articleData = $site->articles($language);

        return view('public.articles', array_merge($site->shared($request, $language, 'articles'), $articleData, [
            'selectedCategory' => (string) $request->query('category', ''),
            'pageBuilderBlocks' => $site->pageBuilderBlocks($language, 'articles', $site->builderPreview($request)),
            'pageBuilderDocument' => $site->pageBuilderDocument($language, 'articles', $site->builderPreview($request)),
        ]));
    }

    public function show(Request $request, SiteDataService $site): View
    {
        $language = $site->language($request);
        $article = $site->article($language, (string) ($request->route('slug') ?: $request->query('slug', '')));

        return view('public.article', array_merge($site->shared($request, $language, 'articles'), [
            'article' => $article,
            'pageBuilderBlocks' => $site->pageBuilderBlocks($language, 'article_detail', $site->builderPreview($request)),
            'pageBuilderDocument' => $site->pageBuilderDocument($language, 'article_detail', $site->builderPreview($request)),
        ]));
    }
}
