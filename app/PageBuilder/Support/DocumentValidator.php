<?php

namespace App\PageBuilder\Support;

use App\PageBuilder\Models\Asset;
use Illuminate\Validation\ValidationException;
use JsonException;

final class DocumentValidator
{
    /** @var array<int, string> */
    private array $assetIds = [];

    public function __construct(
        private readonly ComponentCatalog $catalog,
        private readonly HtmlSanitizer $sanitizer,
    ) {
    }

    /** @param array<string, mixed> $document */
    public function validate(array $document): array
    {
        $this->assetIds = [];
        $this->assertDocumentSize($document);

        if (($document['schema_version'] ?? null) !== 1) {
            $this->fail('document', 'Unsupported document schema version.');
        }

        $layout = is_array($document['layout'] ?? null) ? $document['layout'] : [];
        $layoutType = $layout['type'] ?? 'default';
        if (! in_array($layoutType, config('page_builder.layout.types', ['default']), true)) {
            $this->fail('layout.type', 'Unknown layout type.');
        }

        $sections = $this->validateSectionMap(
            is_array($document['sections'] ?? null) ? $document['sections'] : [],
            is_array($document['order'] ?? null) ? $document['order'] : [],
            'sections',
            0,
        );

        $zones = [];
        foreach (config('page_builder.layout.zones', ['header', 'footer']) as $zone) {
            $zoneData = is_array($layout[$zone] ?? null) ? $layout[$zone] : [];
            $zones[$zone] = $this->validateSectionMap(
                is_array($zoneData['sections'] ?? null) ? $zoneData['sections'] : [],
                is_array($zoneData['order'] ?? null) ? $zoneData['order'] : [],
                "layout.{$zone}",
                0,
            );
        }

        $this->assertAssetsExist();

        return [
            'schema_version' => 1,
            'layout' => [
                'type' => $layoutType,
                'header' => $zones['header'],
                'footer' => $zones['footer'],
            ],
            'sections' => $sections['sections'],
            'order' => $sections['order'],
        ];
    }

    /** @return array{sections: array<string, array<string, mixed>>, order: array<int, string>} */
    private function validateSectionMap(array $sections, array $order, string $path, int $depth): array
    {
        if (count($sections) > (int) config('page_builder.limits.max_sections', 80)) {
            $this->fail($path, 'Too many sections.');
        }

        $normalized = [];
        foreach ($sections as $id => $section) {
            $this->assertId((string) $id, "{$path}.{$id}");
            if (! is_array($section)) {
                $this->fail("{$path}.{$id}", 'Section data must be an object.');
            }

            $type = $section['type'] ?? null;
            if (! is_string($type) || $this->catalog->section($type) === null) {
                $this->fail("{$path}.{$id}.type", 'Unknown section type.');
            }

            $schema = $this->catalog->section($type);
            $normalized[(string) $id] = [
                'type' => $type,
                '_name' => $this->optionalName($section['_name'] ?? null, "{$path}.{$id}._name"),
                'disabled' => (bool) ($section['disabled'] ?? false),
                'settings' => $this->validateSettings($section['settings'] ?? [], $schema['fields'], "{$path}.{$id}.settings"),
                ...$this->validateBlockMap(
                    is_array($section['blocks'] ?? null) ? $section['blocks'] : [],
                    is_array($section['order'] ?? null) ? $section['order'] : [],
                    $schema['blocks'],
                    "{$path}.{$id}.blocks",
                    $depth + 1,
                ),
            ];
        }

        return ['sections' => $normalized, 'order' => $this->normalizeOrder($order, array_keys($normalized), "{$path}.order")];
    }

    /** @return array{blocks: array<string, array<string, mixed>>, order: array<int, string>} */
    private function validateBlockMap(array $blocks, array $order, array $allowedTypes, string $path, int $depth): array
    {
        if ($depth > (int) config('page_builder.limits.max_tree_depth', 8)) {
            $this->fail($path, 'Nested block depth limit exceeded.');
        }

        if (count($blocks) > (int) config('page_builder.limits.max_blocks_per_parent', 80)) {
            $this->fail($path, 'Too many blocks in one parent.');
        }

        $normalized = [];
        foreach ($blocks as $id => $block) {
            $this->assertId((string) $id, "{$path}.{$id}");
            if (! is_array($block)) {
                $this->fail("{$path}.{$id}", 'Block data must be an object.');
            }

            $type = $block['type'] ?? null;
            if (! is_string($type) || ! in_array($type, $allowedTypes, true)) {
                $this->fail("{$path}.{$id}.type", 'Block type is not allowed by its parent.');
            }

            $schema = $this->catalog->block($type);
            if ($schema === null) {
                $this->fail("{$path}.{$id}.type", 'Unknown block type.');
            }

            $normalized[(string) $id] = [
                'type' => $type,
                '_name' => $this->optionalName($block['_name'] ?? null, "{$path}.{$id}._name"),
                'disabled' => (bool) ($block['disabled'] ?? false),
                'settings' => $this->validateSettings($block['settings'] ?? [], $schema['fields'], "{$path}.{$id}.settings"),
                ...$this->validateBlockMap(
                    is_array($block['blocks'] ?? null) ? $block['blocks'] : [],
                    is_array($block['order'] ?? null) ? $block['order'] : [],
                    $schema['blocks'],
                    "{$path}.{$id}.blocks",
                    $depth + 1,
                ),
            ];
        }

        return ['blocks' => $normalized, 'order' => $this->normalizeOrder($order, array_keys($normalized), "{$path}.order")];
    }

    /** @param array<string, mixed> $values @param array<string, array<string, mixed>> $fields */
    private function validateSettings(mixed $values, array $fields, string $path): array
    {
        if (! is_array($values)) {
            $this->fail($path, 'Settings must be an object.');
        }

        $unknown = array_diff(array_keys($values), array_keys($fields));
        if ($unknown !== []) {
            $this->fail($path, 'Unknown setting field: '.reset($unknown));
        }

        $normalized = [];
        foreach ($fields as $key => $field) {
            $value = array_key_exists($key, $values) ? $values[$key] : ($field['default'] ?? null);
            $normalized[$key] = $this->validateField($key, $value, $field, "{$path}.{$key}");
        }

        return $normalized;
    }

    /** @param array<string, mixed> $field */
    private function validateField(string $key, mixed $value, array $field, string $path): mixed
    {
        $type = $field['type'] ?? 'text';
        $nullable = (bool) ($field['nullable'] ?? false);
        $required = (bool) ($field['required'] ?? false);

        if ($value === null && $nullable) {
            return null;
        }

        return match ($type) {
            'checkbox' => (bool) $value,
            'number', 'range' => $this->number($value, $path),
            'select', 'radio', 'text_alignment' => $this->option($value, $field, $path),
            'color', 'color_background' => $this->color($value, $path),
            'url', 'video_url' => $this->url($value, $path, $nullable),
            'asset', 'image_picker' => $this->asset($value, $path, $nullable),
            'rich_text', 'inline_richtext', 'html' => $this->richText($value, $path, $required),
            'blade' => $this->fail($path, 'Blade source fields are disabled in the application port.'),
            default => $this->text($value, $path, $required),
        };
    }

    private function text(mixed $value, string $path, bool $required): string
    {
        if (! is_scalar($value)) {
            $this->fail($path, 'Expected text.');
        }
        $text = trim((string) $value);
        if ($required && $text === '') {
            $this->fail($path, 'This field is required.');
        }
        if (mb_strlen($text) > 20_000) {
            $this->fail($path, 'Text is too long.');
        }
        return $text;
    }

    private function richText(mixed $value, string $path, bool $required): string
    {
        if (! is_scalar($value)) {
            $this->fail($path, 'Expected HTML text.');
        }
        $clean = $this->sanitizer->sanitize((string) $value);
        if ($required && trim(strip_tags($clean)) === '') {
            $this->fail($path, 'This field is required.');
        }
        return $clean;
    }

    private function number(mixed $value, string $path): int|float
    {
        if (! is_numeric($value)) {
            $this->fail($path, 'Expected a number.');
        }
        return str_contains((string) $value, '.') ? (float) $value : (int) $value;
    }

    /** @param array<string, mixed> $field */
    private function option(mixed $value, array $field, string $path): string
    {
        $option = $this->text($value, $path, false);
        $options = $field['options'] ?? [];
        if (! in_array($option, $options, true)) {
            $this->fail($path, 'Invalid option.');
        }
        return $option;
    }

    private function color(mixed $value, string $path): string
    {
        $color = $this->text($value, $path, false);
        $isColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) === 1;
        $isFunction = preg_match('/^(?:rgba?|hsla?)\([^;{}\r\n]+\)$/', $color) === 1;
        $isVariable = preg_match('/^var\(--[a-zA-Z0-9_-]+\)$/', $color) === 1;

        if ($color !== 'transparent' && ! $isColor && ! $isFunction && ! $isVariable) {
            $this->fail($path, 'Invalid color value.');
        }

        return $color;
    }

    private function url(mixed $value, string $path, bool $nullable): ?string
    {
        if ($value === null && $nullable) {
            return null;
        }
        $url = $this->sanitizer->normalizeUrl($this->text($value, $path, false));
        if ($url === '' && $nullable) {
            return null;
        }
        if (! $this->sanitizer->isSafeUrl($url, allowRelative: true)) {
            $this->fail($path, 'Invalid URL.');
        }
        return $url;
    }

    private function asset(mixed $value, string $path, bool $nullable): ?string
    {
        if ($value === null && $nullable) {
            return null;
        }
        if (! is_string($value) || preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $value) !== 1) {
            $this->fail($path, 'Invalid media asset reference.');
        }
        $this->assetIds[] = $value;
        return $value;
    }

    private function optionalName(mixed $value, string $path): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return $this->text($value, $path, false);
    }

    /** @param array<int, string> $ids */
    private function normalizeOrder(array $order, array $ids, string $path): array
    {
        $ids = array_values($ids);
        if ($order === []) {
            return $ids;
        }
        if (count($order) !== count($ids) || array_diff($order, $ids) !== [] || count(array_unique($order)) !== count($order)) {
            $this->fail($path, 'Order must contain every node exactly once.');
        }
        return array_values($order);
    }

    private function assertAssetsExist(): void
    {
        $ids = array_values(array_unique($this->assetIds));
        if ($ids === []) {
            return;
        }
        if (Asset::query()->whereIn('id', $ids)->count() !== count($ids)) {
            $this->fail('document', 'One or more referenced media assets no longer exist.');
        }
    }

    private function assertId(string $id, string $path): void
    {
        if (preg_match('/^[a-zA-Z0-9_-]{3,100}$/', $id) !== 1) {
            $this->fail($path, 'Invalid node identifier.');
        }
    }

    private function assertDocumentSize(array $document): void
    {
        try {
            $bytes = strlen(json_encode($document, JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            $this->fail('document', 'Document cannot be serialized.');
        }
        if ($bytes > (int) config('page_builder.limits.max_document_bytes', 1_000_000)) {
            $this->fail('document', 'Document is too large.');
        }
    }

    private function fail(string $key, string $message): never
    {
        throw ValidationException::withMessages([$key => [$message]]);
    }
}

