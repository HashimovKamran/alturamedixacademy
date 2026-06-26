<?php

namespace App\PageBuilder\Services;

use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\PageActivity;
use App\PageBuilder\Models\PageRevision;
use App\PageBuilder\Models\ThemeSetting;
use App\PageBuilder\Support\SlugNormalizer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PublishService
{
    public function __construct(private readonly SlugNormalizer $slugs)
    {
    }

    public function publish(string $slug, string $revisionId, ?int $actorId = null): PageRevision
    {
        $slug = $this->slugs->normalize($slug);

        return DB::transaction(function () use ($slug, $revisionId, $actorId): PageRevision {
            $page = Page::query()->where('slug', $slug)->lockForUpdate()->first();
            if ($page === null) {
                throw new ModelNotFoundException();
            }

            $draft = PageRevision::query()->whereKey($revisionId)->lockForUpdate()->firstOrFail();
            if ($draft->page_id !== $page->getKey() || $draft->status !== PageRevision::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'revision_id' => ['Only the current draft revision can be published.'],
                ]);
            }

            PageRevision::query()
                ->where('page_id', $page->getKey())
                ->where('status', PageRevision::STATUS_PUBLISHED)
                ->update(['status' => PageRevision::STATUS_ARCHIVED]);

            $draft->forceFill([
                'status' => PageRevision::STATUS_PUBLISHED,
                'published_at' => now(),
                'published_by_id' => $actorId,
            ])->save();

            $page->forceFill([
                'is_active' => true,
                'active_revision_id' => $draft->getKey(),
                'lock_version' => $page->lock_version + 1,
            ])->save();

            $this->activity($page, $draft, 'revision.published', $actorId, [
                'revision_number' => $draft->revision_number,
            ]);

            return $draft->fresh();
        });
    }

    public function rollback(string $slug, string $revisionId, ?int $actorId = null): PageRevision
    {
        $slug = $this->slugs->normalize($slug);

        return DB::transaction(function () use ($slug, $revisionId, $actorId): PageRevision {
            $page = Page::query()->where('slug', $slug)->lockForUpdate()->first();
            if ($page === null) {
                throw new ModelNotFoundException();
            }

            $source = PageRevision::query()->whereKey($revisionId)->lockForUpdate()->firstOrFail();
            if ($source->page_id !== $page->getKey()
                || ! in_array($source->status, [PageRevision::STATUS_PUBLISHED, PageRevision::STATUS_ARCHIVED], true)) {
                throw ValidationException::withMessages([
                    'revision_id' => ['Select a published or archived revision to restore.'],
                ]);
            }

            PageRevision::query()
                ->where('page_id', $page->getKey())
                ->whereIn('status', [PageRevision::STATUS_DRAFT, PageRevision::STATUS_PUBLISHED])
                ->update(['status' => PageRevision::STATUS_ARCHIVED]);

            $revision = PageRevision::query()->create([
                'page_id' => $page->getKey(),
                'revision_number' => ((int) $page->revisions()->max('revision_number')) + 1,
                'status' => PageRevision::STATUS_PUBLISHED,
                'document' => $source->document,
                'theme_settings' => $source->theme_settings ?? [],
                'content_hash' => $source->content_hash,
                'editor_revision' => 1,
                'created_by_id' => $actorId,
                'published_by_id' => $actorId,
                'published_at' => now(),
            ]);

            $page->forceFill([
                'is_active' => true,
                'active_revision_id' => $revision->getKey(),
                'lock_version' => $page->lock_version + 1,
            ])->save();

            ThemeSetting::query()->updateOrCreate(
                ['scope' => 'global'],
                ['values' => $revision->theme_settings ?? []],
            );

            $this->activity($page, $revision, 'revision.rolled_back', $actorId, [
                'source_revision_id' => $source->getKey(),
                'source_revision_number' => $source->revision_number,
            ]);

            return $revision->fresh();
        });
    }

    /** @param array<string, mixed> $properties */
    private function activity(Page $page, PageRevision $revision, string $action, ?int $actorId, array $properties): void
    {
        PageActivity::query()->create([
            'page_id' => $page->getKey(),
            'revision_id' => $revision->getKey(),
            'action' => $action,
            'properties' => $properties,
            'actor_id' => $actorId,
        ]);
    }
}

