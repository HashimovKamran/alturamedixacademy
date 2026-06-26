<?php

namespace App\Services\Site;

use App\Models\PageBuilderBlock;
use App\Models\PagePublication;
use App\Models\PageRevision;
use App\PageBuilder\Services\PageDocumentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PagePublicationService
{
    public function publishedBlocks(string $language, string $pageKey): Collection
    {
        if (! Schema::hasTable('aa_page_publications')) {
            return $this->workingBlocks($language, $pageKey);
        }

        $publication = PagePublication::query()
            ->where('lang_code', $language)
            ->where('page_key', $pageKey)
            ->first();

        if (! $publication) {
            return collect();
        }

        return collect($publication->blocks_json ?? [])
            ->filter(fn ($row) => (bool) ($row['is_active'] ?? true))
            ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
            ->map(function (array $row): PageBuilderBlock {
                $block = new PageBuilderBlock;
                $block->forceFill($row);
                $block->exists = false;

                return $block;
            })->values();
    }

    public function workingBlocks(string $language, string $pageKey): Collection
    {
        return PageBuilderBlock::query()
            ->where('lang_code', $language)
            ->where('page_key', $pageKey)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function allWorkingBlocks(string $language, string $pageKey): Collection
    {
        return PageBuilderBlock::query()
            ->where('lang_code', $language)
            ->where('page_key', $pageKey)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function publish(string $language, string $pageKey, ?int $adminId, ?string $note = null): PagePublication
    {
        return DB::transaction(function () use ($language, $pageKey, $adminId, $note): PagePublication {
            $rows = $this->allWorkingBlocks($language, $pageKey)
                ->map(fn (PageBuilderBlock $block): array => $this->serialize($block))
                ->values()->all();
            $document = app(PageDocumentService::class)->working($language, $pageKey);

            $publication = PagePublication::query()->where('lang_code', $language)->where('page_key', $pageKey)->lockForUpdate()->first()
                ?: new PagePublication(['lang_code' => $language, 'page_key' => $pageKey]);
            $version = $publication->exists ? ((int) $publication->version + 1) : 1;

            $publication->fill([
                'version' => $version,
                'blocks_json' => $rows,
                'document_json' => $document,
                'document_schema_version' => 2,
                'published_by' => $adminId,
                'published_at' => now(),
            ])->save();

            PageRevision::query()->create([
                'lang_code' => $language,
                'page_key' => $pageKey,
                'version' => $version,
                'blocks_json' => $rows,
                'document_json' => $document,
                'document_schema_version' => 2,
                'change_note' => mb_substr(trim((string) $note), 0, 255),
                'created_by' => $adminId,
            ]);

            return $publication;
        });
    }

    public function restore(PageRevision $revision, ?int $adminId): PagePublication
    {
        return DB::transaction(function () use ($revision, $adminId): PagePublication {
            $publication = PagePublication::query()->where('lang_code', $revision->lang_code)->where('page_key', $revision->page_key)->lockForUpdate()->first()
                ?: new PagePublication(['lang_code' => $revision->lang_code, 'page_key' => $revision->page_key]);
            $version = $publication->exists ? ((int) $publication->version + 1) : 1;
            $blocks = $revision->blocks_json ?? [];
            $document = is_array($revision->document_json ?? null)
                ? app(PageDocumentService::class)->ensureDocument($revision->document_json, $revision->lang_code, $revision->page_key)
                : null;

            if ($document) {
                app(PageDocumentService::class)->save($revision->lang_code, $revision->page_key, $document, $adminId);
            } else {
                PageBuilderBlock::query()
                    ->where('lang_code', $revision->lang_code)
                    ->where('page_key', $revision->page_key)
                    ->delete();
                foreach ($blocks as $row) {
                    PageBuilderBlock::query()->create(collect($row)
                        ->except(['id', 'created_at', 'updated_at'])
                        ->put('lang_code', $revision->lang_code)
                        ->put('page_key', $revision->page_key)
                        ->all());
                }
            }

            $publication->fill([
                'version' => $version,
                'blocks_json' => $blocks,
                'document_json' => $document,
                'document_schema_version' => $document ? 2 : null,
                'published_by' => $adminId,
                'published_at' => now(),
            ])->save();

            PageRevision::query()->create([
                'lang_code' => $revision->lang_code,
                'page_key' => $revision->page_key,
                'version' => $version,
                'blocks_json' => $blocks,
                'document_json' => $document,
                'document_schema_version' => $document ? 2 : null,
                'change_note' => 'Restored from version '.$revision->version,
                'created_by' => $adminId,
            ]);

            return $publication;
        });
    }

    private function serialize(PageBuilderBlock $block): array
    {
        return collect($block->getAttributes())
            ->except(['created_at', 'updated_at'])
            ->all();
    }
}
