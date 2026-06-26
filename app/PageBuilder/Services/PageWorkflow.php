<?php

namespace App\PageBuilder\Services;

use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\PageRevision;
use App\PageBuilder\Models\ThemeSetting;
use App\PageBuilder\Support\ComponentCatalog;
use App\PageBuilder\Support\SectionZonePolicy;
use App\PageBuilder\Support\SlugNormalizer;
use App\PageBuilder\Support\TemplateCatalog;
use Illuminate\Validation\ValidationException;

final class PageWorkflow
{
    public function __construct(
        private readonly ComponentCatalog $catalog,
        private readonly TemplateCatalog $templates,
        private readonly SectionZonePolicy $zones,
        private readonly SlugNormalizer $slugs,
        private readonly DraftService $drafts,
        private readonly PublishService $publishing,
    ) {
    }

    /** @return array<string, mixed> */
    public function bootstrap(string $slug): array
    {
        $slug = $this->slugs->normalize($slug);
        $page = Page::query()->where('slug', $slug)->first();
        $draft = $page?->revisions()
            ->where('status', PageRevision::STATUS_DRAFT)
            ->latest('revision_number')
            ->first();
        $source = $draft ?? $page?->activeRevision;
        $sections = $this->catalog->sections();

        foreach (array_keys($sections) as $type) {
            $sections[$type]['zones'] = $this->zones->allowedZones($type);
        }

        return [
            'page' => $page,
            'document' => $source?->document ?? $this->catalog->emptyDocument(),
            'draft' => $draft,
            'catalog' => [
                'sections' => $sections,
                'blocks' => $this->catalog->blocks(),
                'theme_settings' => $this->catalog->themeSettings(),
                'templates' => $this->templates->all(),
            ],
            'theme_settings' => $source?->theme_settings ?? $this->themeValues(),
            'revisions' => $page?->revisions()->latest('revision_number')->get() ?? [],
        ];
    }

    /** @param array<string, mixed> $payload */
    public function saveDraft(string $slug, array $payload, ?int $actorId = null): array
    {
        return $this->drafts->save($slug, $payload, $actorId);
    }

    public function publish(string $slug, string $revisionId, ?int $actorId = null): PageRevision
    {
        return $this->publishing->publish($slug, $revisionId, $actorId);
    }

    public function rollback(string $slug, string $revisionId, ?int $actorId = null): PageRevision
    {
        return $this->publishing->rollback($slug, $revisionId, $actorId);
    }

    /** @return array<string, mixed>|null */
    public function published(string $slug): ?array
    {
        try {
            $slug = $this->slugs->normalize($slug);
        } catch (ValidationException) {
            return null;
        }

        $page = Page::query()->where('slug', $slug)->where('is_active', true)->first();
        $revision = $page?->activeRevision;
        if ($page === null || $revision === null || $revision->status !== PageRevision::STATUS_PUBLISHED) {
            return null;
        }

        return [
            'page' => $page,
            'revision' => $revision,
            'theme_settings' => $revision->theme_settings ?? $this->themeValues(),
        ];
    }

    /** @return array<string, mixed> */
    public function themeValues(): array
    {
        return ThemeSetting::query()
            ->firstOrCreate(['scope' => 'global'], ['values' => []])
            ->values ?? [];
    }
}

