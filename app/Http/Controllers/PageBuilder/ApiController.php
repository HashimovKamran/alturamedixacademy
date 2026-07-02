<?php

namespace App\Http\Controllers\PageBuilder;

use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\AlturaPageBuilder\Services\AlturaPageRevisionWorkflow;
use App\Http\Controllers\Controller;
use App\Support\Admin\AdminLanguage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class ApiController extends Controller
{
    public function __construct(
        private readonly AlturaPageBuilderService $builder,
        private readonly AlturaPageRevisionWorkflow $revisions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $language = $this->language($request);

        return response()->json([
            'data' => array_map(fn (array $page): array => $this->pagePayload($page), $this->builder->pageList($language)),
        ]);
    }

    public function bootstrap(Request $request, string $slug): JsonResponse
    {
        $payload = $this->builder->bootstrap($this->language($request), $this->pageKey($slug));

        return response()->json(['data' => $this->bootstrapPayload($payload)]);
    }

    public function save(Request $request, string $slug): JsonResponse
    {
        $payload = $request->validate([
            'document' => ['required', 'array'],
            'theme_settings' => ['nullable', 'array'],
            'expected_editor_revision' => ['required', 'integer', 'min:0'],
            'meta' => ['nullable', 'array'],
            'meta.title' => ['nullable', 'string', 'max:255'],
            'meta.meta_title' => ['nullable', 'string', 'max:255'],
            'meta.meta_description' => ['nullable', 'string', 'max:500'],
            'meta.meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta.template' => ['nullable', 'string', 'max:120'],
        ]);

        $language = $this->language($request);
        $pageKey = $this->pageKey($slug);

        try {
            $result = $this->builder->saveDraft($language, $pageKey, $payload, $this->actorId($request));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return response()->json([
            'data' => [
                'page' => $this->pagePayload($result['page']),
                'draft' => $this->revisionPayload($result['draft']),
            ],
        ]);
    }

    public function publish(Request $request, string $slug): JsonResponse
    {
        $payload = $request->validate(['revision_id' => ['required', 'integer', 'min:1']]);

        return response()->json([
            'data' => $this->revisionPayload($this->revisions->publish(
                $this->language($request),
                $this->pageKey($slug),
                (int) $payload['revision_id'],
                $this->actorId($request),
            )),
        ]);
    }

    public function rollback(Request $request, string $slug): JsonResponse
    {
        $payload = $request->validate(['revision_id' => ['required', 'integer', 'min:1']]);

        return response()->json([
            'data' => $this->revisionPayload($this->revisions->rollback(
                $this->language($request),
                $this->pageKey($slug),
                (int) $payload['revision_id'],
                $this->actorId($request),
            )),
        ]);
    }

    public function archive(Request $request, string $slug): JsonResponse
    {
        $this->revisions->archive($this->language($request), $this->pageKey($slug), $this->actorId($request));

        return response()->json(status: 204);
    }

    public function restore(Request $request, string $slug): JsonResponse
    {
        return response()->json([
            'data' => $this->pagePayload($this->revisions->restorePage(
                $this->language($request),
                $this->pageKey($slug),
                $this->actorId($request),
            )),
        ]);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $this->builder->deletePage($this->language($request), $this->pageKey($slug), $this->actorId($request));

        return response()->json(status: 204);
    }

    public function history(Request $request, string $slug): JsonResponse
    {
        $history = $this->builder->history($this->language($request), $this->pageKey($slug));

        return response()->json([
            'data' => [
                'revisions' => array_map(fn (array $revision): array => $this->revisionPayload($revision), $history['revisions'] ?? []),
                'activities' => collect($history['activities'] ?? [])->map(fn (object|array $activity): array => $this->activityPayload($activity))->values()->all(),
            ],
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function bootstrapPayload(array $payload): array
    {
        return [
            'page' => isset($payload['page']) && is_array($payload['page']) ? $this->pagePayload($payload['page']) : null,
            'document' => $payload['document'],
            'draft' => isset($payload['draft']) && is_array($payload['draft']) ? $this->revisionPayload($payload['draft']) : null,
            'catalog' => $payload['catalog'],
            'theme_settings' => $payload['theme_settings'] ?? [],
            'revisions' => array_map(fn (array $revision): array => $this->revisionPayload($revision), $payload['revisions'] ?? []),
        ];
    }

    /** @param array<string, mixed> $page */
    private function pagePayload(array $page): array
    {
        $pageKey = (string) ($page['page_key'] ?? $page['slug'] ?? 'index');
        $archived = (bool) ($page['is_archived'] ?? false);
        $deleted = (bool) ($page['is_deleted'] ?? false);

        return [
            ...$page,
            'id' => isset($page['id']) ? (string) $page['id'] : null,
            'slug' => $this->slug($pageKey),
            'page_key' => $pageKey,
            'title' => (string) ($page['title'] ?? $this->title($pageKey)),
            'meta_title' => $page['meta_title'] ?? null,
            'meta_description' => $page['meta_description'] ?? null,
            'meta_keywords' => $page['meta_keywords'] ?? null,
            'template' => $page['template'] ?? null,
            'is_active' => ! $archived && ! $deleted,
            'is_archived' => $archived,
            'is_deleted' => $deleted,
        ];
    }

    /** @param array<string, mixed> $revision */
    private function revisionPayload(array $revision): array
    {
        return [
            ...$revision,
            'id' => (string) ($revision['id'] ?? ''),
            'revision_number' => (int) ($revision['revision_number'] ?? 0),
            'editor_revision' => (int) ($revision['editor_revision'] ?? 0),
            'document' => is_array($revision['document'] ?? null) ? $revision['document'] : [],
            'theme_settings' => is_array($revision['theme_settings'] ?? null) ? $revision['theme_settings'] : [],
        ];
    }

    private function activityPayload(object|array $activity): array
    {
        $value = (array) $activity;

        return [
            'id' => (string) ($value['id'] ?? ''),
            'action' => (string) ($value['event'] ?? $value['action'] ?? ''),
            'created_at' => (string) ($value['created_at'] ?? ''),
        ];
    }

    private function language(Request $request): string
    {
        $requested = trim((string) $request->query('lang_code', $request->input('lang_code', '')));

        return $requested !== '' ? AdminLanguage::set($request, $requested) : AdminLanguage::selected($request);
    }

    private function actorId(Request $request): ?int
    {
        $id = (int) $request->session()->get('admin_user_id', 0);

        return $id > 0 ? $id : null;
    }

    private function pageKey(string $slug): string
    {
        $value = trim(strtolower($slug), '/');

        return match ($value) {
            '', 'home', 'index' => 'index',
            'header', 'site-header' => '__header',
            'footer', 'site-footer' => '__footer',
            default => preg_replace('/[^a-z0-9_-]/', '', $value) ?: 'index',
        };
    }

    private function slug(string $pageKey): string
    {
        return match ($pageKey) {
            'index' => 'home',
            '__header' => 'header',
            '__footer' => 'footer',
            default => $pageKey,
        };
    }

    private function title(string $pageKey): string
    {
        return match ($pageKey) {
            'index' => 'Ana sehife',
            '__header' => 'Header',
            '__footer' => 'Footer',
            default => str($pageKey)->replace(['-', '_'], ' ')->title()->toString(),
        };
    }
}