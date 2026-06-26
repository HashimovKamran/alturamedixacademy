<?php

namespace App\PageBuilder\Services;

use App\Models\PageBuilderBlock;
use App\Models\PageBuilderDocument;
use App\Models\PagePublication;
use App\PageBuilder\Registry\BlockDefinitionRegistry;
use App\Support\Cms\StructuredBlockRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PageDocumentService
{
    public function __construct(
        private readonly LegacyBlockDocumentAdapter $adapter,
        private readonly BlockDefinitionRegistry $definitions,
        private readonly StructuredBlockRegistry $legacyRegistry,
    ) {}

    public function working(string $language, string $pageKey): array
    {
        if (Schema::hasTable('aa_page_builder_documents')) {
            $document = PageBuilderDocument::query()
                ->where('lang_code', $language)
                ->where('page_key', $pageKey)
                ->first();

            if ($document && is_array($document->document_json)) {
                return $this->ensureDocument($document->document_json, $language, $pageKey);
            }
        }

        return $this->adapter->fromBlocks($this->legacyBlocks($language, $pageKey, includeInactive: true), $language, $pageKey);
    }

    public function published(string $language, string $pageKey): array
    {
        if (! Schema::hasTable('aa_page_publications')) {
            return $this->working($language, $pageKey);
        }

        $publication = PagePublication::query()
            ->where('lang_code', $language)
            ->where('page_key', $pageKey)
            ->first();

        if (! $publication) {
            return $this->empty($language, $pageKey);
        }

        $document = $publication->document_json ?? null;
        if (is_array($document) && ($publication->document_schema_version ?? null) === 2) {
            return $this->ensureDocument($document, $language, $pageKey);
        }

        $blocks = collect($publication->blocks_json ?? [])
            ->filter(fn ($row) => (bool) ($row['is_active'] ?? true))
            ->values();

        return $this->adapter->fromBlocks($blocks, $language, $pageKey);
    }

    public function forPublic(string $language, string $pageKey, bool $preview = false): array
    {
        return $preview ? $this->working($language, $pageKey) : $this->published($language, $pageKey);
    }

    public function save(string $language, string $pageKey, array $document, ?int $adminId = null): PageBuilderDocument
    {
        $document = $this->ensureDocument($document, $language, $pageKey);

        return PageBuilderDocument::query()->updateOrCreate(
            ['lang_code' => $language, 'page_key' => $pageKey],
            [
                'schema_version' => 2,
                'document_json' => $document,
                'updated_by' => $adminId,
            ],
        );
    }

    public function hasWorkingDocument(string $language, string $pageKey): bool
    {
        return Schema::hasTable('aa_page_builder_documents')
            && PageBuilderDocument::query()
                ->where('lang_code', $language)
                ->where('page_key', $pageKey)
                ->exists();
    }

    public function addSection(string $language, string $pageKey, string $type, ?string $afterUuid = null, ?int $adminId = null): array
    {
        $document = $this->working($language, $pageKey);
        $document = $this->ensureDocument($document, $language, $pageKey);
        $node = $this->defaultNode($type, $pageKey, 'section');
        $id = $node['block_uuid'];

        $document['sections'][$id] = $node;

        $inserted = false;
        if ($afterUuid) {
            foreach ($document['order'] as $index => $sectionId) {
                if ($sectionId === $afterUuid || (($document['sections'][$sectionId]['block_uuid'] ?? null) === $afterUuid)) {
                    array_splice($document['order'], $index + 1, 0, [$id]);
                    $inserted = true;
                    break;
                }
            }
        }

        if (! $inserted) {
            $document['order'][] = $id;
        }

        $this->save($language, $pageKey, $document, $adminId);

        return ['document' => $document, 'node' => $node];
    }

    public function deleteNode(string $language, string $pageKey, string $uuid, ?int $adminId = null): bool
    {
        $document = $this->working($language, $pageKey);
        $document = $this->ensureDocument($document, $language, $pageKey);
        $deleted = $this->deleteFromContainer($document['sections'], $document['order'], $uuid);
        if ($deleted) {
            $this->save($language, $pageKey, $document, $adminId);
        }

        return $deleted;
    }

    public function duplicateNode(string $language, string $pageKey, string $uuid, ?int $adminId = null): ?array
    {
        $document = $this->working($language, $pageKey);
        $document = $this->ensureDocument($document, $language, $pageKey);
        $copy = null;
        $duplicated = $this->duplicateInContainer($document['sections'], $document['order'], $uuid, $pageKey, null, $copy);
        if (! $duplicated || ! $copy) {
            return null;
        }

        $this->save($language, $pageKey, $document, $adminId);

        return ['document' => $document, 'node' => $copy];
    }

    public function moveNode(string $language, string $pageKey, string $uuid, string $direction, ?int $adminId = null): bool
    {
        $document = $this->working($language, $pageKey);
        $document = $this->ensureDocument($document, $language, $pageKey);
        $moved = $this->moveInContainer($document['sections'], $document['order'], $uuid, $direction);
        if ($moved) {
            $this->save($language, $pageKey, $document, $adminId);
        }

        return $moved;
    }

    public function updateNode(string $language, string $pageKey, string $uuid, callable $callback, ?int $adminId = null): ?array
    {
        $document = $this->working($language, $pageKey);
        $document = $this->ensureDocument($document, $language, $pageKey);
        $updated = $this->updateInContainer($document['sections'], $document['order'], $uuid, $callback);
        if (! $updated) {
            return null;
        }

        $this->save($language, $pageKey, $document, $adminId);

        return $document;
    }

    public function findWorkingNode(string $language, string $pageKey, string $uuid): ?array
    {
        $document = $this->working($language, $pageKey);
        $document = $this->ensureDocument($document, $language, $pageKey);

        return $this->findInContainer($document['sections'], $document['order'], $uuid);
    }

    public function defaultNode(string $type, string $pageKey, string $kind = 'section'): array
    {
        $id = (string) Str::uuid();
        $definition = $this->definitions->definition($type, $kind === 'section' ? 'sections' : 'blocks');
        $content = collect($definition['fields'] ?? [])
            ->mapWithKeys(fn (array $field): array => [(string) $field['key'] => $field['default'] ?? ''])
            ->all();

        return [
            'id' => $id,
            'block_uuid' => $id,
            'parent_block_uuid' => null,
            'slot_key' => 'default',
            'region_key' => 'main',
            'page_key' => $pageKey,
            'type' => $type,
            'name' => (string) ($definition['label'] ?? $type),
            'settings' => $this->legacyRegistry->defaults(),
            'content' => $content,
            'disabled' => false,
            'blocks' => [],
            'order' => [],
        ];
    }

    public function fromLegacyBlocks(Collection $blocks, ?string $language = null, ?string $pageKey = null): array
    {
        return $this->adapter->fromBlocks($blocks, $language, $pageKey);
    }

    public function ensureDocument(array $document, string $language, string $pageKey): array
    {
        $document['schema_version'] = 2;
        $document['lang_code'] = $language;
        $document['page_key'] = $pageKey;
        $document['layout'] = is_array($document['layout'] ?? null) ? $document['layout'] : [
            'type' => 'public',
            'header' => ['sections' => [], 'order' => []],
            'footer' => ['sections' => [], 'order' => []],
        ];
        $document['sections'] = is_array($document['sections'] ?? null) ? $document['sections'] : [];
        $document['order'] = is_array($document['order'] ?? null) ? array_values($document['order']) : array_keys($document['sections']);
        $document['meta'] = is_array($document['meta'] ?? null) ? $document['meta'] : [];

        return $document;
    }

    private function legacyBlocks(string $language, string $pageKey, bool $includeInactive = false): Collection
    {
        $query = PageBuilderBlock::query()
            ->where('lang_code', $language)
            ->where('page_key', $pageKey)
            ->orderBy('sort_order')
            ->orderBy('id');

        if (! $includeInactive) {
            $query->active();
        }

        return $query->get();
    }

    private function empty(string $language, string $pageKey): array
    {
        return $this->ensureDocument([
            'sections' => [],
            'order' => [],
        ], $language, $pageKey);
    }

    private function deleteFromContainer(array &$items, array &$order, string $uuid): bool
    {
        $this->normalizeContainer($items, $order);

        foreach ($order as $index => $id) {
            if (! isset($items[$id]) || ! is_array($items[$id])) {
                continue;
            }

            if ($this->nodeMatches($items[$id], (string) $id, $uuid)) {
                unset($items[$id], $order[$index]);
                $order = array_values($order);

                return true;
            }

            $this->ensureNodeContainer($items[$id]);
            if ($this->deleteFromContainer($items[$id]['blocks'], $items[$id]['order'], $uuid)) {
                return true;
            }
        }

        return false;
    }

    private function duplicateInContainer(array &$items, array &$order, string $uuid, string $pageKey, ?string $parentUuid, ?array &$copy): bool
    {
        $this->normalizeContainer($items, $order);

        foreach ($order as $index => $id) {
            if (! isset($items[$id]) || ! is_array($items[$id])) {
                continue;
            }

            if ($this->nodeMatches($items[$id], (string) $id, $uuid)) {
                $copy = $this->cloneNode($items[$id], $pageKey, $parentUuid);
                $copyId = $copy['block_uuid'];
                $items[$copyId] = $copy;
                array_splice($order, $index + 1, 0, [$copyId]);

                return true;
            }

            $this->ensureNodeContainer($items[$id]);
            $nodeUuid = (string) ($items[$id]['block_uuid'] ?? $id);
            if ($this->duplicateInContainer($items[$id]['blocks'], $items[$id]['order'], $uuid, $pageKey, $nodeUuid, $copy)) {
                return true;
            }
        }

        return false;
    }

    private function moveInContainer(array &$items, array &$order, string $uuid, string $direction): bool
    {
        $this->normalizeContainer($items, $order);

        foreach ($order as $index => $id) {
            if (! isset($items[$id]) || ! is_array($items[$id])) {
                continue;
            }

            if ($this->nodeMatches($items[$id], (string) $id, $uuid)) {
                $target = $direction === 'up' ? $index - 1 : $index + 1;
                if (! isset($order[$target])) {
                    return true;
                }

                [$order[$index], $order[$target]] = [$order[$target], $order[$index]];

                return true;
            }

            $this->ensureNodeContainer($items[$id]);
            if ($this->moveInContainer($items[$id]['blocks'], $items[$id]['order'], $uuid, $direction)) {
                return true;
            }
        }

        return false;
    }

    private function updateInContainer(array &$items, array &$order, string $uuid, callable $callback): bool
    {
        $this->normalizeContainer($items, $order);

        foreach ($order as $id) {
            if (! isset($items[$id]) || ! is_array($items[$id])) {
                continue;
            }

            if ($this->nodeMatches($items[$id], (string) $id, $uuid)) {
                $callback($items[$id]);

                return true;
            }

            $this->ensureNodeContainer($items[$id]);
            if ($this->updateInContainer($items[$id]['blocks'], $items[$id]['order'], $uuid, $callback)) {
                return true;
            }
        }

        return false;
    }

    private function findInContainer(array $items, array $order, string $uuid): ?array
    {
        foreach ($order as $id) {
            if (! isset($items[$id]) || ! is_array($items[$id])) {
                continue;
            }

            if ($this->nodeMatches($items[$id], (string) $id, $uuid)) {
                return $items[$id];
            }

            $children = is_array($items[$id]['blocks'] ?? null) ? $items[$id]['blocks'] : [];
            $childOrder = is_array($items[$id]['order'] ?? null) ? $items[$id]['order'] : array_keys($children);
            $found = $this->findInContainer($children, $childOrder, $uuid);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    private function cloneNode(array $node, string $pageKey, ?string $parentUuid): array
    {
        $oldChildren = is_array($node['blocks'] ?? null) ? $node['blocks'] : [];
        $oldOrder = is_array($node['order'] ?? null) ? $node['order'] : array_keys($oldChildren);
        $id = (string) Str::uuid();

        $node['id'] = $id;
        $node['block_uuid'] = $id;
        $node['parent_block_uuid'] = $parentUuid;
        $node['page_key'] = $pageKey;
        $node['blocks'] = [];
        $node['order'] = [];

        foreach ($oldOrder as $childId) {
            if (! isset($oldChildren[$childId]) || ! is_array($oldChildren[$childId])) {
                continue;
            }

            $child = $this->cloneNode($oldChildren[$childId], $pageKey, $id);
            $node['blocks'][$child['block_uuid']] = $child;
            $node['order'][] = $child['block_uuid'];
        }

        return $node;
    }

    private function normalizeContainer(array &$items, array &$order): void
    {
        $order = array_values(array_filter($order, fn ($id): bool => isset($items[$id]) && is_array($items[$id])));
        foreach (array_keys($items) as $id) {
            if (! in_array($id, $order, true)) {
                $order[] = $id;
            }
        }
    }

    private function ensureNodeContainer(array &$node): void
    {
        $node['blocks'] = is_array($node['blocks'] ?? null) ? $node['blocks'] : [];
        $node['order'] = is_array($node['order'] ?? null) ? $node['order'] : array_keys($node['blocks']);
    }

    private function nodeMatches(array $node, string $id, string $uuid): bool
    {
        return $id === $uuid
            || ((string) ($node['id'] ?? '') === $uuid)
            || ((string) ($node['block_uuid'] ?? '') === $uuid);
    }
}
