<?php

namespace App\PageBuilder\Components;

use App\PageBuilder\Rendering\EditorAttributes;
use JsonSerializable;

class Component implements JsonSerializable
{
    public string $id;
    public string $block_uuid;
    public string $type;
    public string $block_type;
    public string $name;
    public string $slot_key;
    public string $page_key;
    public string $region_key;
    public ?string $parent_block_uuid;
    public bool $disabled;
    public bool $is_active;
    public array $content_json;
    public SettingsBag $settings;
    public ?string $title;
    public ?string $subtitle;
    public ?string $body;
    public ?string $image_path;
    public ?string $button_text;
    public ?string $button_url;
    public array $raw;

    public function __construct(array $data)
    {
        $this->raw = $data;
        $this->id = (string) ($data['id'] ?? $data['block_uuid'] ?? '');
        $this->block_uuid = (string) ($data['block_uuid'] ?? $this->id);
        $this->type = (string) ($data['type'] ?? $data['block_type'] ?? 'rich_text');
        $this->block_type = $this->type;
        $this->name = (string) ($data['name'] ?? $data['title'] ?? $this->type);
        $this->slot_key = (string) ($data['slot_key'] ?? 'default');
        $this->page_key = (string) ($data['page_key'] ?? '');
        $this->region_key = (string) ($data['region_key'] ?? 'main');
        $this->parent_block_uuid = isset($data['parent_block_uuid']) ? (string) $data['parent_block_uuid'] : null;
        $this->disabled = (bool) ($data['disabled'] ?? false);
        $this->is_active = ! $this->disabled;
        $this->content_json = is_array($data['content'] ?? null) ? $data['content'] : [];
        $this->settings = new SettingsBag(is_array($data['settings'] ?? null) ? $data['settings'] : []);
        $this->title = isset($data['title']) ? (string) $data['title'] : (isset($this->content_json['title']) ? (string) $this->content_json['title'] : null);
        $this->subtitle = isset($data['subtitle']) ? (string) $data['subtitle'] : (isset($this->content_json['subtitle']) ? (string) $this->content_json['subtitle'] : (isset($this->content_json['eyebrow']) ? (string) $this->content_json['eyebrow'] : null));
        $this->body = isset($data['body']) ? (string) $data['body'] : (isset($this->content_json['html']) ? (string) $this->content_json['html'] : (isset($this->content_json['text']) ? (string) $this->content_json['text'] : null));
        $this->image_path = isset($data['image_path']) ? (string) $data['image_path'] : null;
        $this->button_text = isset($data['button_text']) ? (string) $data['button_text'] : (isset($this->content_json['button_text']) ? (string) $this->content_json['button_text'] : null);
        $this->button_url = isset($data['button_url']) ? (string) $data['button_url'] : (isset($this->content_json['button_url']) ? (string) $this->content_json['button_url'] : null);
    }

    public function editorAttributes(string $kind = 'block'): string
    {
        return $kind === 'section'
            ? EditorAttributes::forSection($this)
            : EditorAttributes::forBlock($this);
    }

    public function jsonSerialize(): array
    {
        return $this->raw;
    }
}
