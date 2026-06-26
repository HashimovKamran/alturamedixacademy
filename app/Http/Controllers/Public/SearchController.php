<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Feature;
use App\Models\Page;
use App\Models\Training;
use App\Services\Site\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __invoke(Request $request, LanguageService $languages): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $language = $languages->currentCode($request);

        if (mb_strlen($query, 'UTF-8') < 2) {
            return response()->json([
                'ok' => true,
                'query' => $query,
                'count' => 0,
                'results' => [],
                'message' => 'Axtarış üçün ən azı 2 simvol yazın.',
            ]);
        }

        $results = collect()
            ->merge($this->pages($language, $query))
            ->merge($this->articles($language, $query))
            ->merge($this->categories($language, $query))
            ->merge($this->trainings($language, $query))
            ->merge($this->features($language, $query))
            ->unique(fn (array $item): string => $item['type'] . '|' . $item['title'] . '|' . $item['url'])
            ->take(40)
            ->values();

        return response()->json([
            'ok' => true,
            'query' => $query,
            'count' => $results->count(),
            'results' => $results,
        ]);
    }

    private function pages(string $language, string $query): array
    {
        return Page::query()
            ->forLanguage($language)
            ->active()
            ->where(function ($builder) use ($query): void {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('subtitle', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%")
                    ->orWhere('meta_description', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get()
            ->map(fn (Page $page): array => $this->result(
                'Səhifə',
                (string) $page->title,
                trim((string) $page->subtitle . ' ' . (string) $page->body),
                $this->pageUrl((string) $page->page_key, $language),
                'fa-regular fa-file-lines',
                $query
            ))
            ->all();
    }

    private function articles(string $language, string $query): array
    {
        return Article::query()
            ->forLanguage($language)
            ->active()
            ->where(function ($builder) use ($query): void {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%");
            })
            ->latest('published_at')
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn (Article $article): array => $this->result(
                'Məqalə',
                (string) $article->title,
                trim((string) $article->excerpt . ' ' . (string) $article->body),
                route('articles.show', ['slug' => (string) $article->slug, 'lang' => $language]),
                'fa-regular fa-newspaper',
                $query
            ))
            ->all();
    }

    private function categories(string $language, string $query): array
    {
        return ArticleCategory::query()
            ->forLanguage($language)
            ->active()
            ->where(function ($builder) use ($query): void {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%")
                    ->orWhere('icon_class', 'like', "%{$query}%");
            })
            ->orderBy('sort_order')
            ->limit(20)
            ->get()
            ->map(fn (ArticleCategory $category): array => $this->result(
                'Kateqoriya',
                (string) $category->title,
                (string) $category->slug,
                route('articles.index', ['category' => (string) $category->slug, 'lang' => $language]),
                'fa-solid fa-layer-group',
                $query
            ))
            ->all();
    }

    private function trainings(string $language, string $query): array
    {
        return Training::query()
            ->forLanguage($language)
            ->active()
            ->where(function ($builder) use ($query): void {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%");
            })
            ->orderBy('training_date')
            ->limit(20)
            ->get()
            ->map(fn (Training $training): array => $this->result(
                'Təlim',
                (string) $training->title,
                (string) $training->location,
                route('trainings.index', ['lang' => $language]),
                'fa-solid fa-graduation-cap',
                $query
            ))
            ->all();
    }

    private function features(string $language, string $query): array
    {
        return Feature::query()
            ->forLanguage($language)
            ->active()
            ->where(function ($builder) use ($query): void {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('url', 'like', "%{$query}%");
            })
            ->orderBy('sort_order')
            ->limit(20)
            ->get()
            ->map(fn (Feature $feature): array => $this->result(
                'Bölmə',
                (string) $feature->title,
                (string) $feature->description,
                \App\Support\CleanUrl::to((string) ($feature->url ?: '/'), $language),
                'fa-solid fa-compass',
                $query
            ))
            ->all();
    }

    private function pageUrl(string $pageKey, string $language): string
    {
        $map = [
            'index' => '/',
            'about' => '/about',
            'articles' => '/articles',
            'certificates' => '/certificates',
            'trainings' => '/trainings',
            'gallery' => '/gallery',
            'contact' => '/contact',
        ];

        $path = $map[$pageKey] ?? '/page?key=' . urlencode($pageKey);

        return \App\Support\CleanUrl::to($path, $language);
    }

    private function result(string $type, string $title, string $description, string $url, string $icon, string $query): array
    {
        return [
            'type' => $type,
            'title' => trim(strip_tags($title)),
            'description' => $this->excerpt($description, $query),
            'url' => $url,
            'icon' => $icon,
        ];
    }

    private function excerpt(string $text, string $query): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?: '');
        if ($text === '') {
            return '';
        }

        $position = mb_stripos($text, $query, 0, 'UTF-8');
        if ($position === false) {
            return Str::limit($text, 160);
        }

        $start = max(0, $position - 55);
        $piece = mb_substr($text, $start, 160, 'UTF-8');

        return ($start > 0 ? '...' : '') . $piece . (mb_strlen($text, 'UTF-8') > $start + 160 ? '...' : '');
    }
}
