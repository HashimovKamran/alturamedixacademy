<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Services\Site\SiteDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function __invoke(Request $request, SiteDataService $site): View
    {
        $language = $site->language($request);

        return view('public.trainings', array_merge($site->shared($request, $language, 'trainings'), [
            'page' => $site->page($language, 'trainings'),
            'trainings' => Training::query()
                ->forLanguage($language)
                ->active()
                ->orderBy('training_date')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
            'pageBuilderBlocks' => $site->pageBuilderBlocks($language, 'trainings', $site->builderPreview($request)),
            'pageBuilderDocument' => $site->pageBuilderDocument($language, 'trainings', $site->builderPreview($request)),
        ]));
    }
}
