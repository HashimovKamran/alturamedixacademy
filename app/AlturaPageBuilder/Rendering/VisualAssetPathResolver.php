<?php

namespace App\AlturaPageBuilder\Rendering;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class VisualAssetPathResolver
{
    private array $cache = [];

    public function url(?int $assetId): ?string
    {
        if (! $assetId) return null;
        if (array_key_exists($assetId, $this->cache)) return $this->cache[$assetId];

        $asset = DB::table('aa_visual_assets')
            ->where('id', $assetId)
            ->where('is_deleted', false)
            ->first();

        return $this->cache[$assetId] = $asset
            ? Storage::disk((string) $asset->disk)->url((string) $asset->path)
            : null;
    }
}
