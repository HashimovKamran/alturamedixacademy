<?php

namespace App\Http\Controllers\Admin;

use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\Http\Controllers\Controller;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class PageBuilderApiController extends Controller
{
    public function __construct(private readonly AlturaPageBuilderService $builder) {}

    public function bootstrap(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->builder->bootstrap($this->language($request), $this->pageKey($request))]);
    }

    public function pages(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->builder->pageList($this->language($request))]);
    }

    private function language(Request $request): string
    {
        return AdminLanguage::selected($request);
    }

    private function pageKey(Request $request): string
    {
        return (string) $request->query('page_key', $request->input('page_key', 'index'));
    }
}
