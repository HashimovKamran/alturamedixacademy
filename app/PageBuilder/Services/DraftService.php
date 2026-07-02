<?php

namespace App\PageBuilder\Services;

use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\PageActivity;
use App\PageBuilder\Models\PageRevision;
use App\PageBuilder\Models\ThemeSetting;
use App\PageBuilder\Support\DocumentValidationService;
use App\PageBuilder\Support\SlugNormalizer;
use App\PageBuilder\Support\ThemeSettingsValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JsonException;
use RuntimeException;

final class DraftService
{
    public function __construct(
        private readonly DocumentValidationService $validator,
        private readonly ThemeSettingsValidator $themeValidator,
        private readonly SlugNormalizer $slugs,
        private readonly PageMetadataWriter $metadata,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function save(string $slug, array $payload, ?int $actorId = null): array
    {
        $slug = $this->slugs->normalize($slug);
        $document = $this->validator->validate($payload['document'] ?? []);
        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
        $theme = $this->themeValidator->validate(is_array($payload['theme_settings'] ?? null) ? $payload['theme_settings'] : []);
        $expected = (int) ($payload['expected_editor_revision'] ?? 0);

        return DB::transaction(function () use ($slug, $document, $meta, $theme, $expected, $actorId): array {
            $page = Page::withTrashed()->where('slug', $slug)->lockForUpdate()->first() ?? new Page(['slug' => $slug]);
            $isNew = ! $page->exists;
            $wasDeleted = $page->trashed();

            if ($wasDeleted) {
                $page->restore();
                $page->active_revision_id = null;
                PageRevision::query()
                    ->where('page_id', $page->getKey())
                    ->where('status', PageRevision::STATUS_DRAFT)
                    ->update(['status' => PageRevision::STATUS_ARCHIVED]);
            }

            $this->metadata->fill($page, $meta, str($slug)->afterLast('/')->replace('-', ' ')->title()->toString());
            if (! $isNew) {
                $page->lock_version++;
            }
            $page->save();

            $draft = $page->revisions()
                ->where('status', PageRevision::STATUS_DRAFT)
                ->lockForUpdate()
                ->first();

            if ($draft === null) {
                if ($expected !== 0) {
                    throw new RuntimeException('The page draft is stale. Reload and try again.');
                }
                $draft = $this->makeDraft($page, $document, $theme, $actorId);
            } else {
                if ($draft->editor_revision !== $expected) {
                    throw new RuntimeException('The page draft is stale. Reload and try again.');
                }
                $draft->forceFill([
                    'document' => $document,
                    'theme_settings' => $theme,
                    'content_hash' => $this->hash($document, $theme),
                    'editor_revision' => $draft->editor_revision + 1,
                ])->save();
            }

            ThemeSetting::query()->updateOrCreate(['scope' => 'global'], ['values' => $theme]);
            PageActivity::query()->create([
                'page_id' => $page->getKey(),
                'revision_id' => $draft->getKey(),
                'action' => $wasDeleted ? 'page.reopened_with_draft' : 'draft.saved',
                'properties' => ['editor_revision' => $draft->editor_revision],
                'actor_id' => $actorId,
            ]);

            return ['page' => $page->fresh(), 'draft' => $draft->fresh()];
        });
    }

    /** @param array<string, mixed> $document @param array<string, mixed> $theme */
    private function makeDraft(Page $page, array $document, array $theme, ?int $actorId): PageRevision
    {
        return PageRevision::query()->create([
            'page_id' => $page->getKey(),
            'revision_number' => ((int) $page->revisions()->max('revision_number')) + 1,
            'status' => PageRevision::STATUS_DRAFT,
            'document' => $document,
            'theme_settings' => $theme,
            'content_hash' => $this->hash($document, $theme),
            'editor_revision' => 1,
            'created_by_id' => $actorId,
        ]);
    }

    /** @param array<string, mixed> $document @param array<string, mixed> $theme */
    private function hash(array $document, array $theme): string
    {
        try {
            return hash('sha256', json_encode(['document' => $document, 'theme' => $theme], JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            throw ValidationException::withMessages([
                'document' => ['The page document cannot be serialized.'],
            ]);
        }
    }
}

