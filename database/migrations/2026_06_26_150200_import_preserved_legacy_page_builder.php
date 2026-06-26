<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('aa_visual_pages')) return;

        $documents = $this->rows('aa_legacy_page_builder_documents');
        $publications = $this->rows('aa_legacy_page_publications');
        $revisions = $this->rows('aa_legacy_page_revisions');
        $blocks = $this->rows('aa_legacy_page_builder_blocks');
        $keys = collect([$documents, $publications, $revisions, $blocks])
            ->flatMap(fn (Collection $rows) => $rows->map(fn ($row) => $this->get($row, 'lang_code', 'az').'|'.$this->get($row, 'page_key', 'index')))
            ->filter(fn (string $key) => str_contains($key, '|'))
            ->unique()
            ->values();

        foreach ($keys as $key) {
            [$language, $pageKey] = explode('|', $key, 2);
            $this->importPage($language, $pageKey, $documents, $publications, $revisions, $blocks);
        }

        // aa_legacy_* tables deliberately remain as an archived rollback safety net.
    }

    public function down(): void
    {
        // The legacy backup rename migration restores original names after visual tables are rolled back.
    }

    private function importPage(string $language, string $pageKey, Collection $documents, Collection $publications, Collection $revisions, Collection $blocks): void
    {
        DB::transaction(function () use ($language, $pageKey, $documents, $publications, $revisions, $blocks): void {
            $page = $this->page($language, $pageKey);
            if (DB::table('aa_visual_page_activities')->where('page_id', $page->id)->where('event', 'legacy_imported')->exists()) {
                return;
            }

            $number = (int) DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->max('revision_number');
            $activeId = null;
            $legacyRevisions = $revisions
                ->filter(fn ($row) => $this->get($row, 'lang_code') === $language && $this->get($row, 'page_key') === $pageKey)
                ->sortBy(fn ($row) => (int) $this->get($row, 'revision_number', $this->get($row, 'version', $this->get($row, 'id', 0))));

            foreach ($legacyRevisions as $legacy) {
                $document = $this->document($this->get($legacy, 'document_json'), $this->decode($this->get($legacy, 'blocks_json')));
                if ($this->empty($document)) continue;
                $activeId = $this->revision($page->id, ++$number, 'published', $document, $this->get($legacy, 'created_by'), $this->get($legacy, 'created_at'), 'Imported legacy revision');
            }

            $publication = $publications
                ->filter(fn ($row) => $this->get($row, 'lang_code') === $language && $this->get($row, 'page_key') === $pageKey)
                ->sortByDesc(fn ($row) => (string) $this->get($row, 'published_at', $this->get($row, 'updated_at', '')))
                ->first();
            if ($publication) {
                $document = $this->document($this->get($publication, 'document_json'), $this->decode($this->get($publication, 'blocks_json')));
                if (! $this->empty($document)) {
                    $activeId = $this->revision($page->id, ++$number, 'published', $document, $this->get($publication, 'published_by'), $this->get($publication, 'published_at'), 'Imported legacy public snapshot');
                }
            }

            $draft = $documents
                ->filter(fn ($row) => $this->get($row, 'lang_code') === $language && $this->get($row, 'page_key') === $pageKey)
                ->sortByDesc(fn ($row) => (string) $this->get($row, 'updated_at', $this->get($row, 'id', '')))
                ->first();
            $document = $draft
                ? $this->document($this->get($draft, 'document_json'))
                : $this->fromBlocks($blocks->filter(fn ($row) => $this->get($row, 'lang_code') === $language && $this->get($row, 'page_key') === $pageKey));
            if (! $this->empty($document)) {
                $this->revision($page->id, ++$number, 'draft', $document, $draft ? $this->get($draft, 'updated_by', $this->get($draft, 'created_by')) : null, null, 'Imported legacy working draft', 1);
            }

            if ($activeId) {
                DB::table('aa_visual_pages')->where('id', $page->id)->update(['active_revision_id' => $activeId, 'updated_at' => now()]);
            }
            DB::table('aa_visual_page_activities')->insert([
                'page_id' => $page->id,
                'actor_id' => null,
                'event' => 'legacy_imported',
                'payload' => json_encode(['language' => $language, 'page_key' => $pageKey]),
                'created_at' => now(),
            ]);
        });
    }

    private function rows(string $table): Collection
    {
        return Schema::hasTable($table) ? DB::table($table)->get() : collect();
    }

    private function page(string $language, string $pageKey): object
    {
        $page = DB::table('aa_visual_pages')->where('lang_code', $language)->where('page_key', $pageKey)->lockForUpdate()->first();
        if ($page) return $page;
        $title = Schema::hasTable('aa_pages') ? DB::table('aa_pages')->where('lang_code', $language)->where('page_key', $pageKey)->value('title') : null;
        $id = DB::table('aa_visual_pages')->insertGetId([
            'lang_code' => $language,
            'page_key' => $pageKey,
            'title' => $title ?: ucfirst(str_replace(['_', '-'], ' ', $pageKey)),
            'is_archived' => false,
            'is_deleted' => false,
            'lock_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return DB::table('aa_visual_pages')->where('id', $id)->first();
    }

    private function revision(int $pageId, int $number, string $status, array $document, mixed $actor = null, mixed $publishedAt = null, string $note = '', int $editorRevision = 0): int
    {
        return DB::table('aa_visual_page_revisions')->insertGetId([
            'page_id' => $pageId,
            'revision_number' => $number,
            'status' => $status,
            'editor_revision' => $editorRevision,
            'document_json' => json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'theme_settings' => json_encode([]),
            'change_note' => $note,
            'created_by' => $actor,
            'published_by' => $status === 'published' ? $actor : null,
            'published_at' => $status === 'published' ? ($publishedAt ?: now()) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function document(mixed $raw, array $fallback = []): array
    {
        $old = $this->decode($raw);
        if ($old === [] && $fallback !== []) return $this->fromRows($fallback);
        if ($old === []) return $this->blank();
        $main = $this->convertMap((array) ($old['sections'] ?? []), (array) ($old['order'] ?? []));
        $header = $this->convertMap((array) ($old['layout']['header']['sections'] ?? []), (array) ($old['layout']['header']['order'] ?? []));
        $footer = $this->convertMap((array) ($old['layout']['footer']['sections'] ?? []), (array) ($old['layout']['footer']['order'] ?? []));
        return ['schema_version' => 1, 'layout' => ['type' => 'alturamedix', 'header' => $header, 'footer' => $footer], 'sections' => $main['sections'], 'order' => $main['order']];
    }

    private function convertMap(array $items, array $order): array
    {
        $sections = [];
        foreach (($order ?: array_keys($items)) as $id) {
            $row = $items[$id] ?? null;
            if (! is_array($row)) continue;
            $children = $this->convertMap((array) ($row['blocks'] ?? []), (array) ($row['order'] ?? []));
            $settings = is_array($row['content'] ?? null) ? $row['content'] : (is_array($row['settings'] ?? null) ? $row['settings'] : []);
            $sections[(string) $id] = [
                'type' => ($row['type'] ?? 'rich_text') === 'text' ? 'rich_text' : ($row['type'] ?? 'rich_text'),
                '_name' => null,
                'disabled' => (bool) ($row['disabled'] ?? false),
                'slot_key' => $row['slot_key'] ?? 'default',
                'settings' => $settings,
                'blocks' => $children['sections'],
                'order' => $children['order'],
            ];
        }
        return ['sections' => $sections, 'order' => array_values(array_filter($order ?: array_keys($sections), fn ($id) => isset($sections[$id])))];
    }

    private function fromBlocks(Collection $rows): array
    {
        return $this->fromRows($rows->map(fn ($row) => (array) $row)->all());
    }

    private function fromRows(array $rows): array
    {
        $byParent = collect($rows)->groupBy(fn ($row) => $row['parent_block_uuid'] ?? '__root');
        $build = function (string $parent = '__root') use (&$build, $byParent): array {
            $nodes = [];
            $order = [];
            foreach (($byParent->get($parent, collect()))->sortBy('sort_order') as $row) {
                $uuid = (string) ($row['block_uuid'] ?? ('legacy_'.($row['id'] ?? uniqid())));
                $content = $this->decode($row['content_json'] ?? null);
                if ($content === []) {
                    $content = array_filter([
                        'title' => $row['title'] ?? null,
                        'eyebrow' => $row['subtitle'] ?? null,
                        'html' => $row['body'] ?? null,
                        'button_text' => $row['button_text'] ?? null,
                        'button_url' => $row['button_url'] ?? null,
                    ], fn ($value) => $value !== null && $value !== '');
                }
                [$children, $childOrder] = $build($uuid);
                $nodes[$uuid] = [
                    'type' => ($row['block_type'] ?? 'rich_text') === 'text' ? 'rich_text' : ($row['block_type'] ?? 'rich_text'),
                    '_name' => null,
                    'disabled' => ! (bool) ($row['is_active'] ?? true),
                    'slot_key' => $row['slot_key'] ?? 'default',
                    'settings' => $content,
                    'blocks' => $children,
                    'order' => $childOrder,
                ];
                $order[] = $uuid;
            }
            return [$nodes, $order];
        };
        [$sections, $order] = $build();
        return ['schema_version' => 1, 'layout' => ['type' => 'alturamedix', 'header' => ['sections' => [], 'order' => []], 'footer' => ['sections' => [], 'order' => []]], 'sections' => $sections, 'order' => $order];
    }

    private function decode(mixed $value): array
    {
        if (is_array($value)) return $value;
        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function get(mixed $row, string $key, mixed $default = null): mixed
    {
        return is_array($row) ? ($row[$key] ?? $default) : ($row->{$key} ?? $default);
    }

    private function blank(): array
    {
        return ['schema_version' => 1, 'layout' => ['type' => 'alturamedix', 'header' => ['sections' => [], 'order' => []], 'footer' => ['sections' => [], 'order' => []]], 'sections' => [], 'order' => []];
    }

    private function empty(array $document): bool
    {
        return $document['sections'] === [] && ($document['layout']['header']['sections'] ?? []) === [] && ($document['layout']['footer']['sections'] ?? []) === [];
    }
};
