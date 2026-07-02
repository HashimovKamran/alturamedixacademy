<?php

namespace App\Http\Controllers\PageBuilder;

use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class AssetLookupController extends Controller
{
    public function __construct(private readonly AlturaPageBuilderService $builder)
    {
    }

    public function __invoke(int|string $asset): JsonResponse
    {
        return response()->json(['data' => $this->builder->asset((int) $asset)]);
    }
}