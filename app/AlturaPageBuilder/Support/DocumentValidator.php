<?php

namespace App\AlturaPageBuilder\Support;

use App\AlturaPageBuilder\Catalog\AlturaComponentCatalog;
use App\Support\Cms\SafeHtml;
use App\Support\Cms\SafeUrl;
use Illuminate\Validation\ValidationException;

final class DocumentValidator
{
    private const MAX_NODES = 180;
    private const MAX_DEPTH = 6;

    public function __construct(
        private readonly AlturaComponentCatalog $catalog,
        private readonly SafeHtml $html,
    ) {}

    public function validate(array $document): array
    {
        $document = [
            'schema_version' => 1,
            'layout' => is_array($document['layout'] ?? null) ? $document['layout'] : [],
            'sections' => is_array($document['sections'] ?? null) ? $document['sections'] : [],
            'order' => is_array($document['order'] ?? null) ? $document['order'] : [],
        ];
        $counter = 0;
        $document['sections'] = $this->normalizeMap($document['sections'], $document['order'], 'main', 'section', null, 1, $counter);
        $document['order'] = $this->orderedKeys($document['sections'], $document['order']);
        $header = is_array($document['layout']['header'] ?? null) ? $document['layout']['header'] : [];
        $footer = is_array($document['layout']['footer'] ?? null) ? $document['layout']['footer'] : [];
        $document['layout'] = [
            'type' => 'alturamedix',
            'header' => [
                'sections' => $this->normalizeMap((array) ($header['sections'] ?? []), (array) ($header['order'] ?? []), 'header', 'section', null, 1, $counter),
                'order' => [],
            ],
            'footer' => [
                'sections' => $this->normalizeMap((array) ($footer['sections'] ?? []), (array) ($footer['order'] ?? []), 'footer', 'section', null, 1, $counter),
                'order' => [],
            ],
        ];
        $document['layout']['header']['order'] = $this->orderedKeys($document['layout']['header']['sections'], (array) ($header['order'] ?? []));
        $document['layout']['footer']['order'] = $this->orderedKeys($document['layout']['footer']['sections'], (array) ($footer['order'] ?? []));

        return $document;
    }

    private function normalizeMap(array $items, array $order, string $zone, string $kind, ?array $parent, int $depth, int &$counter): array
    {
        if ($depth > self::MAX_DEPTH) throw ValidationException::withMessages(['document' => 'Section/block nesting limiti keçilib.']);
        $normalized = [];
        foreach ($this->orderedKeys($items, $order) as $id) {
            $raw = $items[$id] ?? null;
            if (! is_array($raw)) continue;
            $safeId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $id);
            if ($safeId === '') continue;
            if (++$counter > self::MAX_NODES) throw ValidationException::withMessages(['document' => 'Səhifədə icazə verilən maksimum komponent sayı keçilib.']);
            $type = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($raw['type'] ?? '')));
            $definition = $this->catalog->component($kind, $type);
            if (! $definition) throw ValidationException::withMessages(['document' => "İcazəsiz {$kind} tipi: {$type}"]);
            if ($kind === 'section' && $definition['zones'] !== [] && ! in_array($zone, $definition['zones'], true)) {
                throw ValidationException::withMessages(['document' => "{$definition['label']} {$zone} zonasında istifadə edilə bilməz."]);
            }
            if ($parent && ! in_array($type, $parent['blocks'] ?? [], true)) {
                throw ValidationException::withMessages(['document' => "{$type} seçilən parent daxilində istifadə edilə bilməz."]);
            }
            $slot = preg_replace('/[^a-z0-9_-]/', '', (string) ($raw['slot_key'] ?? 'default')) ?: 'default';
            if ($parent && ($parent['slots'] ?? []) !== [] && ! in_array($slot, $parent['slots'], true)) {
                throw ValidationException::withMessages(['document' => "{$slot} slotu seçilən parent üçün etibarlı deyil."]);
            }
            $children = $this->normalizeMap(
                is_array($raw['blocks'] ?? null) ? $raw['blocks'] : [],
                is_array($raw['order'] ?? null) ? $raw['order'] : [],
                $zone,
                'block',
                $definition,
                $depth + 1,
                $counter,
            );
            $normalized[$safeId] = [
                'type' => $type,
                '_name' => $this->text($raw['_name'] ?? null, 120, nullable: true),
                'disabled' => (bool) ($raw['disabled'] ?? false),
                'slot_key' => $slot,
                'settings' => $this->normalizeSettings((array) ($raw['settings'] ?? []), (array) $definition['fields']),
                'blocks' => $children,
                'order' => $this->orderedKeys($children, is_array($raw['order'] ?? null) ? $raw['order'] : []),
            ];
        }
        return $normalized;
    }

    private function normalizeSettings(array $incoming, array $fields): array
    {
        $out = [];
        foreach ($fields as $key => $field) {
            $value = array_key_exists($key, $incoming) ? $incoming[$key] : ($field['default'] ?? null);
            $type = (string) ($field['type'] ?? 'text');
            $out[$key] = match ($type) {
                'checkbox' => (bool) $value,
                'number', 'range' => $this->number($value, $field),
                'rich_text', 'html', 'inline_richtext' => $this->html->clean(is_scalar($value) ? (string) $value : ''),
                'url', 'video_url' => SafeUrl::clean(is_scalar($value) ? (string) $value : ''),
                'select', 'radio' => $this->option($value, $field),
                'asset' => $this->assetId($value),
                'color', 'color_background' => $this->color($value, $field['default'] ?? '#ffffff'),
                default => $this->text($value, $type === 'textarea' ? 10000 : 1000),
            };
        }
        return $out;
    }

    private function orderedKeys(array $items, array $order): array
    {
        $result = [];
        foreach ($order as $id) if (is_string($id) || is_int($id)) if (array_key_exists((string) $id, $items) && ! in_array((string) $id, $result, true)) $result[] = (string) $id;
        foreach (array_keys($items) as $id) if (! in_array((string) $id, $result, true)) $result[] = (string) $id;
        return $result;
    }

    private function text(mixed $value, int $max, bool $nullable = false): ?string
    {
        if ($nullable && ($value === null || $value === '')) return null;
        return mb_substr(trim(strip_tags(is_scalar($value) ? (string) $value : '')), 0, $max);
    }

    private function number(mixed $value, array $field): int|float
    {
        $number = is_numeric($value) ? (float) $value : (float) ($field['default'] ?? 0);
        if (isset($field['min'])) $number = max((float) $field['min'], $number);
        if (isset($field['max'])) $number = min((float) $field['max'], $number);
        return floor($number) === $number ? (int) $number : $number;
    }

    private function option(mixed $value, array $field): string
    {
        $value = is_scalar($value) ? (string) $value : '';
        $options = (array) ($field['options'] ?? []);
        return $options === [] || in_array($value, $options, true) ? $value : (string) ($field['default'] ?? '');
    }

    private function assetId(mixed $value): ?int
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    private function color(mixed $value, mixed $fallback): string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';
        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : (string) $fallback;
    }
}
