<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Site\SiteDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(Request $request, SiteDataService $site): View
    {
        return $this->show($request, $site, 'about', 'about', 'fa-solid fa-shield-heart');
    }

    public function contact(Request $request, SiteDataService $site): View
    {
        return $this->show($request, $site, 'contact', 'contact', 'fa-solid fa-envelope');
    }

    public function certificates(Request $request, SiteDataService $site): View
    {
        $language = $site->language($request);
        $certificateNumber = strtoupper(trim((string) $request->query('cert_no', '')));

        return view('public.certificates', array_merge($site->shared($request, $language, 'certificates'), [
            'page' => $site->page($language, 'certificates'),
            'pageBuilderBlocks' => $site->pageBuilderBlocks($language, 'certificates', $site->builderPreview($request)),
            'pageBuilderDocument' => $site->pageBuilderDocument($language, 'certificates', $site->builderPreview($request)),
            'certificateNumber' => $certificateNumber,
            'certificate' => $certificateNumber !== '' ? $site->certificate($certificateNumber) : null,
        ]));
    }

    public function dynamic(Request $request, SiteDataService $site): View
    {
        $pageKey = preg_replace('/[^a-z0-9_\-]/i', '', (string) ($request->route('key') ?: $request->query('key', 'page'))) ?: 'page';

        return $this->show($request, $site, $pageKey, 'page', 'fa-solid fa-file-lines');
    }

    private function show(Request $request, SiteDataService $site, string $pageKey, string $activePage, string $fallbackIcon): View
    {
        $language = $site->language($request);

        return view('public.page', array_merge($site->shared($request, $language, $activePage), [
            'page' => $site->page($language, $pageKey),
            'fallbackIcon' => $fallbackIcon,
            'pageBuilderBlocks' => $site->pageBuilderBlocks($language, $pageKey, $site->builderPreview($request)),
            'pageBuilderDocument' => $site->pageBuilderDocument($language, $pageKey, $site->builderPreview($request)),
        ]));
    }
}
