<?php

namespace App\PageBuilder\Services;

use App\Support\Cms\StructuredBlockRegistry;
use Illuminate\Support\Collection;

class LegacyBlockDocumentAdapter
{
    public function __construct(private readonly StructuredBlockRegistry $registry) {}

    public function fromBlocks(Collection $blocks, ?string $language = null, ?string $pageKey = null): array
    {
        $blocks = $blocks->values();
        $first = $blocks->first();
        $language ??= (string) $this->value($first, 'lang_code', app()->getLocale());
        $pageKey ??= (string) $this->value($first, 'page_key', 'index');
        $byParent = $blocks->groupBy(fn ($block): string => (string) ($this->value($block, 'parent_block_uuid') ?: '__root'));
        $roots = collect($byParent->get('__root', collect()))->sortBy([
            fn ($block) => (int) $this->value($block, 'sort_order', 0),
            fn ($block) => (int) $this->value($block, 'id', 0),
        ])->values();

        $sections = [];
        $order = [];
        foreach ($roots as $block) {
            $id = $this->uuid($block);
            $sections[$id] = $this->node($block, $byParent);
            $order[] = $id;
        }

        return [
            'schema_version' => 2,
            'source' => 'legacy_adapter',
            'lang_code' => $language,
            'page_key' => $pageKey,
            'layout' => [
                'type' => 'public',
                'header' => ['sections' => [], 'order' => []],
                'footer' => ['sections' => [], 'order' => []],
            ],
            'sections' => $sections,
            'order' => $order,
            'meta' => [],
        ];
    }

    private function node(mixed $block, Collection $byParent): array
    {
        $id = $this->uuid($block);
        $content = $this->arrayValue($this->value($block, 'content_json'));
        if ($content === []) {
            $content = array_filter([
                'title' => $this->value($block, 'title'),
                'eyebrow' => $this->value($block, 'subtitle'),
                'text' => $this->value($block, 'body'),
                'html' => $this->value($block, 'body'),
                'button_text' => $this->value($block, 'button_text'),
                'button_url' => $this->value($block, 'button_url'),
            ], fn ($value) => $value !== null && $value !== '');
        }

        $settings = array_merge($this->registry->defaults(), $this->arrayValue($this->value($block, 'settings_json')));
        $children = collect($byParent->get($id, collect()))->sortBy([
            fn ($child) => (int) $this->value($child, 'sort_order', 0),
            fn ($child) => (int) $this->value($child, 'id', 0),
        ])->values();

        $blocks = [];
        $order = [];
        foreach ($children as $child) {
            $childId = $this->uuid($child);
            $blocks[$childId] = $this->node($child, $byParent);
            $order[] = $childId;
        }

        $type = (string) ($this->value($block, 'block_type') ?: 'rich_text');
        if ($type === 'text') {
            $type = 'rich_text';
        }

        return [
            'id' => (string) $this->value($block, 'id', $id),
            'block_uuid' => $id,
            'parent_block_uuid' => $this->value($block, 'parent_block_uuid'),
            'slot_key' => (string) ($this->value($block, 'slot_key') ?: 'default'),
            'region_key' => (string) ($this->value($block, 'region_key') ?: 'main'),
            'page_key' => (string) ($this->value($block, 'page_key') ?: ''),
            'type' => $type,
            'settings' => $settings,
            'content' => $content,
            'title' => $this->value($block, 'title'),
            'subtitle' => $this->value($block, 'subtitle'),
            'body' => $this->value($block, 'body'),
            'image_path' => $this->value($block, 'image_path'),
            'button_text' => $this->value($block, 'button_text'),
            'button_url' => $this->value($block, 'button_url'),
            'disabled' => ! (bool) $this->value($block, 'is_active', true),
            'blocks' => $blocks,
            'order' => $order,
        ];
    }

    private function uuid(mixed $block): string
    {
        $uuid = (string) ($this->value($block, 'block_uuid') ?: '');

        return $uuid !== '' ? $uuid : 'legacy_'.$this->value($block, 'id', md5(json_encode($block)));
    }

    private function value(mixed $source, string $key, mixed $default = null): mixed
    {
        if (is_array($source)) {
            return $source[$key] ?? $default;
        }

        return is_object($source) ? ($source->{$key} ?? $default) : $default;
    }

    private function arrayValue(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
