<?php

namespace App\AlturaPageBuilder\Rendering;

final class VisualComponent
{
    public readonly string $type;
    public readonly string $slot_key;
    public readonly bool $disabled;
    public readonly array $settings;
    public readonly array $content_json;

    public function __construct(array $node, ?VisualAssetPathResolver $assets = null)
    {
        $this->node = $node;
        $this->type = (string) ($node['type'] ?? 'rich_text');
        $this->slot_key = (string) ($node['slot_key'] ?? 'default');
        $this->disabled = (bool) ($node['disabled'] ?? false);
        $settings = is_array($node['settings'] ?? null) ? $node['settings'] : [];
        $assetId = filter_var($settings['image_asset_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($assetId && $assets?->url((int) $assetId)) $settings['image_path'] = $assets->url((int) $assetId);
        $this->settings = $settings;
        $this->content_json = $settings;
    }

    private readonly array $node;

    public function __get(string $key): mixed
    {
        return $this->node[$key] ?? $this->settings[$key] ?? null;
    }

    public function editorAttributes(string $kind = 'section'): string
    {
        return '';
    }
}
