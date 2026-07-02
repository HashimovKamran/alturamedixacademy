<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Services\Site\SiteDataService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request, SiteDataService $site): View
    {
        $language = $site->language($request);
        $selectedCategory = trim((string) $request->query('category', ''));
        $search = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'latest');

        $categories = ArticleCategory::query()
            ->withCount(['articles' => fn (Builder $query) => $query->forLanguage($language)->active()])
            ->forLanguage($language)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $articlesQuery = Article::query()
            ->with('category')
            ->forLanguage($language)
            ->active()
            ->when($selectedCategory !== '', function (Builder $query) use ($selectedCategory): void {
                $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $selectedCategory));
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('excerpt', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%')
                        ->orWhere('author_name', 'like', '%'.$search.'%');
                });
            });

        match ($sort) {
            'oldest' => $articlesQuery->oldest('published_at')->oldest('id'),
            'title' => $articlesQuery->orderBy('title')->orderByDesc('published_at'),
            default => $articlesQuery->latest('published_at')->latest('id'),
        };

        $articles = $articlesQuery->paginate(6)->withQueryString();
        $popularArticles = Article::query()
            ->with('category')
            ->forLanguage($language)
            ->active()
            ->latest('published_at')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('public.articles', array_merge($site->shared($request, $language, 'articles'), [
            'articles' => $articles,
            'categories' => $categories,
            'popularArticles' => $popularArticles,
            'selectedCategory' => $selectedCategory,
            'searchQuery' => $search,
            'selectedSort' => in_array($sort, ['latest', 'oldest', 'title'], true) ? $sort : 'latest',
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
