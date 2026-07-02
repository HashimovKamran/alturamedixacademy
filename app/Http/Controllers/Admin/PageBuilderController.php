<?php

namespace App\Http\Controllers\Admin;

use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAuthService;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PageBuilderController extends Controller
{
    public function index(Request $request, AlturaPageBuilderService $builder, AdminAuthService $auth): View
    {
        $language = AdminLanguage::selected($request);
        $pageKey = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $request->query('page', 'index'))) ?: 'index';
        $page = $builder->bootstrap($language, $pageKey)['page'];
        $pages = $builder->pageList($language);
        $previewUrl = $this->previewUrl($pageKey, $language);
        $admin = $auth->user($request);
        $canEdit = (bool) $admin?->hasAnyRole(['super_admin', 'designer', 'editor']);
        $canPublish = (bool) $admin?->hasAnyRole(['super_admin', 'publisher']);

        return view('admin.page_builder.react', compact('language', 'pageKey', 'page', 'pages', 'previewUrl', 'canEdit', 'canPublish'));
    }

    public function canvas(Request $request): RedirectResponse
    {
        $language = AdminLanguage::selected($request);
        $pageKey = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $request->query('page', 'index'))) ?: 'index';
        return redirect()->to($this->previewUrl($pageKey, $language));
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

        return url($path).(str_contains($path, '?') ? '&' : '?').http_build_query([
            'lang' => $language,
            'pb_preview' => 1,
            'pb_page' => $pageKey,
        ]);
    }
}
