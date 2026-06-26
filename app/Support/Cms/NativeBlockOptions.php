<?php

namespace App\Support\Cms;

class NativeBlockOptions
{
    public function resolve(?array $node): array
    {
        if (! $node || empty($node['block'])) return [];

        $block = $node['block'];
        $definition = (new StructuredBlockRegistry)->definition((string) $block->block_type);
        $defaults = collect($definition['fields'] ?? [])->mapWithKeys(
            fn (array $field): array => [$field['key'] => $field['default'] ?? '']
        )->all();
        $content = is_array($block->content_json)
            ? $block->content_json
            : (json_decode((string) $block->content_json, true) ?: []);

        return array_merge($defaults, $content);
    }

    public static function text(array $options, string $key, mixed $fallback = ''): mixed
    {
        $value = $options[$key] ?? null;
        return is_string($value) && trim($value) === '' ? $fallback : ($value ?? $fallback);
    }

    public static function enabled(array $options, string $key, bool $fallback = true): bool
    {
        return array_key_exists($key, $options) ? (bool) $options[$key] : $fallback;
    }
}
