<?php

namespace App\PageBuilder\Services;

use App\PageBuilder\Models\PageRevision;

final class AssetReferenceScanner
{
    public function isReferenced(string $assetId): bool
    {
        return PageRevision::query()
            ->whereIn('status', [PageRevision::STATUS_DRAFT, PageRevision::STATUS_PUBLISHED])
            ->cursor()
            ->contains(fn (PageRevision $revision): bool => $this->containsAsset($revision->document, $assetId));
    }

    /** @param array<string, mixed> $document */
    private function containsAsset(array $document, string $assetId): bool
    {
        return $this->scan($document, $assetId);
    }

    private function scan(mixed $value, string $assetId): bool
    {
        if (! is_array($value)) {
            return $value === $assetId;
        }

        foreach ($value as $key => $item) {
            if (($key === 'asset_id' || $key === 'image_id') && $item === $assetId) {
                return true;
            }
            if ($this->scan($item, $assetId)) {
                return true;
            }
        }

        return false;
    }
}

