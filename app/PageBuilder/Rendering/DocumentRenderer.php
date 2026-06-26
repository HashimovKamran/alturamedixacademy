<?php

namespace App\PageBuilder\Rendering;

use App\PageBuilder\Models\Asset;
use App\PageBuilder\Support\ComponentCatalog;
use App\PageBuilder\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Storage;

final class DocumentRenderer
{
    /** @var array<string, Asset|null> */
    private array $assets = [];

    public function __construct(
        private readonly ComponentCatalog $catalog,
        private readonly HtmlSanitizer $sanitizer,
    ) {
    }

    public function sectionView(string $type): string
    {
        return $this->catalog->section($type)['view'] ?? 'generic-pagebuilder.sections.unknown';
    }

    public function blockView(string $type): string
    {
        return $this->catalog->block($type)['view'] ?? 'generic-pagebuilder.blocks.unknown';
    }

    public function assetUrl(mixed $id): ?string
    {
        if (! is_string($id) || $id === '') {
            return null;
        }

        if (! array_key_exists($id, $this->assets)) {
            $this->assets[$id] = Asset::query()->find($id);
        }

        $asset = $this->assets[$id];
        return $asset === null ? null : Storage::disk($asset->disk)->url($asset->path);
    }

    public function assetAlt(mixed $id, string $fallback = ''): string
    {
        if (! is_string($id) || $id === '') {
            return $fallback;
        }

        if (! array_key_exists($id, $this->assets)) {
            $this->assets[$id] = Asset::query()->find($id);
        }

        return $this->assets[$id]?->alt_text ?: $fallback;
    }

    public function rich(mixed $html): string
    {
        return $this->sanitizer->sanitize((string) $html);
    }

    /** @param array<string, mixed> $theme */
    public function themeCss(array $theme): string
    {
        $pairs = [
            '--pb-primary' => $theme['colors.primary'] ?? '#3256e8',
            '--pb-background' => $theme['colors.background'] ?? '#ffffff',
            '--pb-text' => $theme['colors.text'] ?? '#152033',
            '--pb-font-display' => $theme['fonts.display'] ?? 'Inter, sans-serif',
            '--pb-font-body' => $theme['fonts.body'] ?? 'Inter, sans-serif',
        ];

        return implode('', array_map(
            fn (string $key, mixed $value): string => $key.':'.str_replace([';', '{', '}'], '', (string) $value).';',
            array_keys($pairs),
            array_values($pairs),
        ));
    }
}

