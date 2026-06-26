<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Site\SiteDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request, SiteDataService $site): View|RedirectResponse
    {
        $language = $site->language($request);
        $user = $site->currentUser($request);

        if (!$user) {
            return redirect()->route('home', ['lang' => $language]);
        }

        return view('public.profile', array_merge($site->shared($request, $language, 'profile'), [
            'user' => $user,
            'pageBuilderBlocks' => $site->pageBuilderBlocks($language, 'profile', $site->builderPreview($request)),
            'pageBuilderDocument' => $site->pageBuilderDocument($language, 'profile', $site->builderPreview($request)),
        ]));
    }
}
