<?php

namespace App\Http\Controllers\Admin;

use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\Http\Controllers\Controller;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PageBuilderController extends Controller
{
    public function index(Request $request, AlturaPageBuilderService $builder): View
    {
        $language = AdminLanguage::selected($request);
        $pageKey = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $request->query('page', 'index'))) ?: 'index';
        $page = $builder->bootstrap($language, $pageKey)['page'];
        $pages = $builder->pageList($language);
        $previewUrl = $this->previewUrl($pageKey, $language);

        return view('admin.page_builder.react', compact('language', 'pageKey', 'page', 'pages', 'previewUrl'));
    }

    public function canvas(Request $request): View
    {
        return view('admin.page_builder.canvas', [
            'language' => AdminLanguage::selected($request),
            'pageKey' => preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $request->query('page', 'index'))) ?: 'index',
        ]);
    }

    private function previewUrl(string $pageKey, string $language): string
    {
        $path = match ($pageKey) {
            '__header', '__footer', 'index' => '/',
            'about' => '/about',
            'contact' => '/contact',
            'certificates' => '/certificates',
            'gallery' => '/gallery',
            'trainings' => '/trainings',
            'articles', 'article_detail' => '/articles',
            'profile' => '/profile',
            default => '/page?key='.urlencode($pageKey),
        };
        $separator = str_contains($path, '?') ? '&' : '?';
        return url($path).$separator.http_build_query(['lang' => $language, 'pb_preview' => 1, 'pb_page' => $pageKey]);
    }
}
