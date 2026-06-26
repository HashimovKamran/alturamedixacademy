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

        return view('admin.page_builder.react', compact('language', 'pageKey', 'page'));
    }

    public function canvas(Request $request): View
    {
        return view('admin.page_builder.canvas', [
            'language' => AdminLanguage::selected($request),
            'pageKey' => preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $request->query('page', 'index'))) ?: 'index',
        ]);
    }
}
