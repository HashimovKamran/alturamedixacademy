<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Site\SiteDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __invoke(Request $request, SiteDataService $site): View
    {
        $language = $site->language($request);

        return view('public.gallery', array_merge($site->shared($request, $language, 'gallery'), [
            'gallery' => $site->gallery($language),
            'pageBuilderBlocks' => $site->pageBuilderBlocks($language, 'gallery', $site->builderPreview($request)),
            'pageBuilderDocument' => $site->pageBuilderDocument($language, 'gallery', $site->builderPreview($request)),
        ]));
    }
}
