<?php

namespace App\Http\Controllers\PageBuilder;

use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AssetController extends Controller
{
    public function __construct(private readonly AlturaPageBuilderService $builder)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->builder->assets($request->integer('page', 1), $request->integer('per_page', 48)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'alt_text' => ['nullable', 'string', 'max:500'],
        ]);

        return response()->json([
            'data' => $this->builder->uploadAsset(
                $payload['file'],
                $payload['alt_text'] ?? null,
                $this->actorId($request),
            ),
        ], 201);
    }

    public function update(Request $request, int|string $asset): JsonResponse
    {
        $payload = $request->validate(['alt_text' => ['nullable', 'string', 'max:500']]);

        return response()->json([
            'data' => $this->builder->updateAsset((int) $asset, $payload['alt_text'] ?? null),
        ]);
    }

    public function destroy(int|string $asset): JsonResponse
    {
        $this->builder->deleteAsset((int) $asset);

        return response()->json(status: 204);
    }

    private function actorId(Request $request): ?int
    {
        $id = (int) $request->session()->get('admin_user_id', 0);

        return $id > 0 ? $id : null;
    }
}