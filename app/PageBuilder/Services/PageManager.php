<?php

namespace App\PageBuilder\Services;

use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\PageActivity;
use App\PageBuilder\Models\PageRevision;
use App\PageBuilder\Support\SlugNormalizer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class PageManager
{
    public function __construct(private readonly SlugNormalizer $slugs)
    {
    }

    public function archive(string $slug, ?int $actorId = null): void
    {
        $slug = $this->slugs->normalize($slug);

        DB::transaction(function () use ($slug, $actorId): void {
            $page = Page::query()->where('slug', $slug)->lockForUpdate()->first();
            if ($page === null) {
                throw new ModelNotFoundException();
            }

            $page->forceFill([
                'is_active' => false,
                'active_revision_id' => null,
                'lock_version' => $page->lock_version + 1,
            ])->save();

            $this->record($page, null, 'page.archived', $actorId);
        });
    }

    public function restore(string $slug, ?int $actorId = null): Page
    {
        $slug = $this->slugs->normalize($slug);

        return DB::transaction(function () use ($slug, $actorId): Page {
            $page = Page::withTrashed()->where('slug', $slug)->lockForUpdate()->first();
            if ($page === null) {
                throw new ModelNotFoundException();
            }

            if ($page->trashed()) {
                $page->restore();
            }

            $latestPublished = PageRevision::query()
                ->where('page_id', $page->getKey())
                ->where('status', PageRevision::STATUS_PUBLISHED)
                ->latest('revision_number')
                ->first();

            $page->forceFill([
                'is_active' => true,
                'active_revision_id' => $latestPublished?->getKey(),
                'lock_version' => $page->lock_version + 1,
            ])->save();

            $this->record($page, $latestPublished, 'page.restored', $actorId);

            return $page->fresh();
        });
    }

    public function delete(string $slug, ?int $actorId = null): void
    {
        $slug = $this->slugs->normalize($slug);

        DB::transaction(function () use ($slug, $actorId): void {
            $page = Page::query()->where('slug', $slug)->lockForUpdate()->first();
            if ($page === null) {
                throw new ModelNotFoundException();
            }

            $this->record($page, $page->activeRevision, 'page.deleted', $actorId);
            $page->delete();
        });
    }

    private function record(Page $page, ?PageRevision $revision, string $action, ?int $actorId): void
    {
        PageActivity::query()->create([
            'page_id' => $page->getKey(),
            'revision_id' => $revision?->getKey(),
            'action' => $action,
            'properties' => [],
            'actor_id' => $actorId,
        ]);
    }
}

