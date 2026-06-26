<?php

namespace App\PageBuilder\Registry;

use App\Support\Cms\StructuredBlockRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;

class BlockDefinitionRegistry
{
    public function __construct(
        private readonly SchemaExtractor $extractor,
        private readonly StructuredBlockRegistry $legacy,
    ) {}

    public function all(string $kind = 'sections'): array
    {
        $definitions = $this->legacy->definitions();
        $path = resource_path('views/pagebuilder/'.$kind);

        foreach (glob($path.'/*.blade.php') ?: [] as $file) {
            $type = basename($file, '.blade.php');
            $schema = $this->extractor->extract($file);
            if (! $schema) {
                continue;
            }
            $definitions[$type] = $this->normalizeSchema($type, $schema, $definitions[$type] ?? []);
        }

        return collect($definitions)->map(function (array $definition, string $type) use ($kind): array {
            return $definition + [
                'type' => $type,
                'view' => 'pagebuilder.'.$kind.'.'.$type,
                'exists' => View::exists('pagebuilder.'.$kind.'.'.$type),
            ];
        })->all();
    }

    public function definition(string $type, string $kind = 'sections'): array
    {
        return $this->all($kind)[$type] ?? $this->legacy->definition($type);
    }

    public function schemas(string $kind = 'sections'): array
    {
        return collect($this->all($kind))->map(fn (array $definition): array => Arr::only($definition, [
            'label', 'category', 'fields', 'slots', 'allowed_children', 'system', 'type', 'view',
        ]))->all();
    }

    private function normalizeSchema(string $type, array $schema, array $fallback): array
    {
        $fields = collect($schema['settings'] ?? $schema['fields'] ?? [])
            ->map(function (array $field): array {
                $key = (string) ($field['key'] ?? $field['id'] ?? '');
                $options = $field['options'] ?? [];
                if (array_is_list($options)) {
                    $options = collect($options)->mapWithKeys(fn ($option) => [
                        (string) ($option['value'] ?? '') => (string) ($option['label'] ?? $option['value'] ?? ''),
                    ])->filter(fn ($label, $value) => $value !== '')->all();
                }

                return [
                    'key' => $key,
                    'label' => (string) ($field['label'] ?? $key),
                    'type' => (string) ($field['type'] ?? 'text'),
                    'required' => (bool) ($field['required'] ?? false),
                    'columns' => $field['columns'] ?? [],
                    'options' => $options,
                    'default' => $field['default'] ?? '',
                ];
            })
            ->filter(fn (array $field): bool => $field['key'] !== '')
            ->values()
            ->all();

        $label = (string) ($schema['label'] ?? $schema['name'] ?? '');
        if ($label === '' || str_ends_with($label, '.blade')) {
            $label = (string) ($fallback['label'] ?? $type);
        }

        return [
            'label' => $label,
            'category' => (string) ($schema['category'] ?? $fallback['category'] ?? 'Məzmun'),
            'fields' => $fields ?: ($fallback['fields'] ?? []),
            'slots' => $schema['slots'] ?? $fallback['slots'] ?? [],
            'allowed_children' => $schema['allowed_children'] ?? $fallback['allowed_children'] ?? [],
            'system' => (bool) ($schema['system'] ?? $fallback['system'] ?? false),
        ];
    }
}
