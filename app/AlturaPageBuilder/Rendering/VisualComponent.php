<?php

namespace App\AlturaPageBuilder\Rendering;

final class VisualComponent
{
    public readonly string $type;
    public readonly string $slot_key;
    public readonly bool $disabled;
    public readonly array $settings;
    public readonly array $content_json;

    public function __construct(private readonly array $node)
    {
        $this->type = (string) ($node['type'] ?? 'rich_text');
        $this->slot_key = (string) ($node['slot_key'] ?? 'default');
        $this->disabled = (bool) ($node['disabled'] ?? false);
        $this->settings = is_array($node['settings'] ?? null) ? $node['settings'] : [];
        // Existing Alturamedix section Blade views expect content_json/content.
        $this->content_json = $this->settings;
    }

    public function __get(string $key): mixed
    {
        return $this->node[$key] ?? $this->settings[$key] ?? null;
    }

    public function editorAttributes(string $kind = 'section'): string
    {
        return '';
    }
}
