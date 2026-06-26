<?php

namespace App\AlturaPageBuilder\Services;

use App\AlturaPageBuilder\Catalog\AlturaComponentCatalog;
use App\AlturaPageBuilder\Support\DocumentValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class AlturaPageBuilderService
{
    public function __construct(
        private readonly AlturaComponentCatalog $catalog,
        private readonly DocumentValidator $validator,
    ) {}

    public function catalog(): array
    {
        return $this->catalog->payload();
    }

    public function bootstrap(string $language, string $pageKey): array
    {
        $page = $this->ensurePage($language, $pageKey);
        $draft = DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->where('status', 'draft')->latest('id')->first();
        $revisions = DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->latest('revision_number')->limit(60)->get()->map(fn ($row) => $this->revisionPayload($row))->values()->all();
        $active = $page->active_revision_id ? DB::table('aa_visual_page_revisions')->where('id', $page->active_revision_id)->first() : null;
        $source = $draft ?: $active;
        $document = $source ? $this->decode($source->document_json) : $this->catalog->defaultDocument($pageKey);

        return [
            'page' => $this->pagePayload($page),
            'document' => $document,
            'draft' => $draft ? $this->revisionPayload($draft) : null,
            'catalog' => $this->catalog(),
            'theme_settings' => $source ? $this->decode($source->theme_settings) : [],
            'revisions' => $revisions,
        ];
    }

    public function pageList(string $language): array
    {
        $keys = [
            '__header' => 'Header', 'index' => 'Ana səhifə', 'about' => 'Haqqımızda', 'articles' => 'Akademik yazılar',
            'article_detail' => 'Məqalə detalı', 'certificates' => 'Sertifikatlar', 'trainings' => 'Təlimlər',
            'gallery' => 'Qalereya', 'contact' => 'Əlaqə', 'profile' => 'Profil', '__footer' => 'Footer',
        ];
        foreach (DB::table('aa_pages')->where('lang_code', $language)->get(['page_key', 'title']) as $row) $keys[$this->pageKey($row->page_key)] = $row->title;
        foreach (DB::table('aa_menus')->where('lang_code', $language)->where('is_active', true)->get(['title', 'url']) as $menu) {
            $key = $this->pageKeyFromUrl((string) $menu->url);
            if ($key !== '') $keys[$key] = $menu->title;
        }
        $rows = DB::table('aa_visual_pages')->where('lang_code', $language)->get()->keyBy('page_key');
        $result = [];
        foreach ($keys as $key => $title) {
            $page = $rows->get($key) ?: $this->ensurePage($language, $key, (string) $title);
            $result[] = $this->pagePayload($page);
        }
        foreach ($rows as $key => $page) if (! array_key_exists($key, $keys)) $result[] = $this->pagePayload($page);
        return collect($result)->sortBy('title')->values()->all();
    }

    public function saveDraft(string $language, string $pageKey, array $payload, ?int $actorId): array
    {
        $document = $this->validator->validate((array) ($payload['document'] ?? []));
        $theme = $this->normalizeTheme((array) ($payload['theme_settings'] ?? []));
        $meta = (array) ($payload['meta'] ?? []);
        $expected = (int) ($payload['expected_editor_revision'] ?? 0);

        return DB::transaction(function () use ($language, $pageKey, $document, $theme, $meta, $expected, $actorId): array {
            $page = $this->ensurePage($language, $pageKey, null, true);
            $draft = DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->where('status', 'draft')->lockForUpdate()->latest('id')->first();
            if ($draft && (int) $draft->editor_revision !== $expected) throw new RuntimeException('Bu səhifə başqa pəncərədə dəyişdirilib. Səhifəni yeniləyin.');
            if (! $draft && $expected !== 0) throw new RuntimeException('Draft artıq aktual deyil. Səhifəni yeniləyin.');

            $this->updatePageMeta($page->id, $meta);
            if ($draft) {
                DB::table('aa_visual_page_revisions')->where('id', $draft->id)->update([
                    'document_json' => json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'theme_settings' => json_encode($theme, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'editor_revision' => (int) $draft->editor_revision + 1,
                    'updated_at' => now(),
                ]);
                $revision = DB::table('aa_visual_page_revisions')->where('id', $draft->id)->first();
            } else {
                $number = (int) DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->max('revision_number') + 1;
                $id = DB::table('aa_visual_page_revisions')->insertGetId([
                    'page_id' => $page->id,
                    'revision_number' => $number,
                    'status' => 'draft',
                    'editor_revision' => 1,
                    'document_json' => json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'theme_settings' => json_encode($theme, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_by' => $actorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $revision = DB::table('aa_visual_page_revisions')->where('id', $id)->first();
            }
            $this->syncAssetReferences((int) $revision->id, $document);
            $this->activity((int) $page->id, $actorId, 'draft_saved', ['revision_id' => (int) $revision->id]);
            $freshPage = DB::table('aa_visual_pages')->where('id', $page->id)->first();
            return ['page' => $this->pagePayload($freshPage), 'draft' => $this->revisionPayload($revision)];
        });
    }

    public function publish(string $language, string $pageKey, int $revisionId, ?int $actorId, ?string $note = null): array
    {
        return DB::transaction(function () use ($language, $pageKey, $revisionId, $actorId, $note): array {
            $page = $this->ensurePage($language, $pageKey, null, true);
            $revision = DB::table('aa_visual_page_revisions')->where('id', $revisionId)->where('page_id', $page->id)->lockForUpdate()->first();
            if (! $revision || $revision->status !== 'draft') throw ValidationException::withMessages(['revision_id' => 'Dərc ediləcək aktiv draft tapılmadı.']);
            if ($page->active_revision_id) DB::table('aa_visual_page_revisions')->where('id', $page->active_revision_id)->update(['status' => 'archived', 'updated_at' => now()]);
            DB::table('aa_visual_page_revisions')->where('id', $revision->id)->update([
                'status' => 'published', 'change_note' => mb_substr(trim((string) $note), 0, 255),
                'published_by' => $actorId, 'published_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('aa_visual_pages')->where('id', $page->id)->update(['active_revision_id' => $revision->id, 'is_archived' => false, 'updated_at' => now()]);
            $this->activity((int) $page->id, $actorId, 'published', ['revision_id' => (int) $revision->id]);
            return $this->revisionPayload(DB::table('aa_visual_page_revisions')->where('id', $revision->id)->first());
        });
    }

    public function rollback(string $language, string $pageKey, int $revisionId, ?int $actorId): array
    {
        return DB::transaction(function () use ($language, $pageKey, $revisionId, $actorId): array {
            $page = $this->ensurePage($language, $pageKey, null, true);
            $source = DB::table('aa_visual_page_revisions')->where('id', $revisionId)->where('page_id', $page->id)->where('status', 'published')->lockForUpdate()->first();
            if (! $source) throw ValidationException::withMessages(['revision_id' => 'Bərpa ediləcək published revision tapılmadı.']);
            if ($page->active_revision_id) DB::table('aa_visual_page_revisions')->where('id', $page->active_revision_id)->update(['status' => 'archived', 'updated_at' => now()]);
            $number = (int) DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->max('revision_number') + 1;
            $id = DB::table('aa_visual_page_revisions')->insertGetId([
                'page_id' => $page->id, 'revision_number' => $number, 'status' => 'published', 'editor_revision' => 0,
                'document_json' => $source->document_json, 'theme_settings' => $source->theme_settings,
                'change_note' => 'Restored from revision '.$source->revision_number, 'created_by' => $actorId,
                'published_by' => $actorId, 'published_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('aa_visual_pages')->where('id', $page->id)->update(['active_revision_id' => $id, 'is_archived' => false, 'updated_at' => now()]);
            $document = $this->decode($source->document_json);
            $this->syncAssetReferences($id, $document);
            $this->activity((int) $page->id, $actorId, 'rollback', ['source_revision_id' => (int) $source->id, 'revision_id' => $id]);
            return $this->revisionPayload(DB::table('aa_visual_page_revisions')->where('id', $id)->first());
        });
    }

    public function history(string $language, string $pageKey): array
    {
        $page = $this->ensurePage($language, $pageKey);
        return [
            'revisions' => DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->latest('revision_number')->get()->map(fn ($row) => $this->revisionPayload($row))->all(),
            'activities' => DB::table('aa_visual_page_activities')->where('page_id', $page->id)->latest('id')->limit(100)->get(),
        ];
    }

    public function archive(string $language, string $pageKey, ?int $actorId): void
    {
        DB::transaction(function () use ($language, $pageKey, $actorId): void {
            $page = $this->ensurePage($language, $pageKey, null, true);
            DB::table('aa_visual_pages')->where('id', $page->id)->update(['is_archived' => true, 'active_revision_id' => null, 'updated_at' => now()]);
            $this->activity((int) $page->id, $actorId, 'archived', []);
        });
    }

    public function restorePage(string $language, string $pageKey, ?int $actorId): array
    {
        return DB::transaction(function () use ($language, $pageKey, $actorId): array {
            $page = $this->ensurePage($language, $pageKey, null, true);
            $published = DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->where('status', 'published')->latest('revision_number')->first();
            if (! $published) throw ValidationException::withMessages(['page' => 'Bərpa ediləcək published revision yoxdur.']);
            DB::table('aa_visual_pages')->where('id', $page->id)->update(['is_archived' => false, 'active_revision_id' => $published->id, 'updated_at' => now()]);
            $this->activity((int) $page->id, $actorId, 'restored', ['revision_id' => (int) $published->id]);
            return $this->pagePayload(DB::table('aa_visual_pages')->where('id', $page->id)->first());
        });
    }

    public function deletePage(string $language, string $pageKey, ?int $actorId): void
    {
        DB::transaction(function () use ($language, $pageKey, $actorId): void {
            $page = $this->ensurePage($language, $pageKey, null, true);
            if (in_array($page->page_key, ['index', '__header', '__footer'], true)) throw ValidationException::withMessages(['page' => 'Bu sistem səhifəsi silinə bilməz.']);
            DB::table('aa_visual_pages')->where('id', $page->id)->update(['is_deleted' => true, 'is_archived' => true, 'active_revision_id' => null, 'updated_at' => now()]);
            $this->activity((int) $page->id, $actorId, 'deleted', []);
        });
    }

    public function publicDocument(string $language, string $pageKey, bool $preview = false): array
    {
        $page = DB::table('aa_visual_pages')->where('lang_code', $language)->where('page_key', $this->pageKey($pageKey))->where('is_deleted', false)->first();
        if (! $page) return $this->catalog->defaultDocument($pageKey);
        $revision = null;
        if ($preview) $revision = DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->where('status', 'draft')->latest('id')->first();
        if (! $revision && ! $page->is_archived && $page->active_revision_id) $revision = DB::table('aa_visual_page_revisions')->where('id', $page->active_revision_id)->where('status', 'published')->first();
        return $revision ? $this->decode($revision->document_json) : $this->catalog->defaultDocument($pageKey);
    }

    public function publicTheme(string $language, string $pageKey, bool $preview = false): array
    {
        $page = DB::table('aa_visual_pages')->where('lang_code', $language)->where('page_key', $this->pageKey($pageKey))->where('is_deleted', false)->first();
        if (! $page) return [];
        $revision = $preview ? DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->where('status', 'draft')->latest('id')->first() : null;
        if (! $revision && ! $page->is_archived && $page->active_revision_id) $revision = DB::table('aa_visual_page_revisions')->where('id', $page->active_revision_id)->first();
        return $revision ? $this->decode($revision->theme_settings) : [];
    }

    public function assets(int $page = 1, int $perPage = 36): array
    {
        $perPage = max(1, min(60, $perPage));
        $query = DB::table('aa_visual_assets')->where('is_deleted', false)->orderByDesc('id');
        $total = $query->count();
        $rows = $query->forPage(max(1, $page), $perPage)->get()->map(fn ($asset) => $this->assetPayload($asset))->all();
        return ['data' => $rows, 'meta' => ['page' => max(1, $page), 'per_page' => $perPage, 'total' => $total, 'has_more' => $page * $perPage < $total]];
    }

    public function asset(int $id): array
    {
        $asset = DB::table('aa_visual_assets')->where('id', $id)->where('is_deleted', false)->firstOrFail();
        return $this->assetPayload($asset);
    }

    public function uploadAsset(UploadedFile $file, ?string $alt, ?int $actorId): array
    {
        $mime = (string) $file->getMimeType();
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) throw ValidationException::withMessages(['file' => 'Yalnız JPEG, PNG, WEBP və GIF şəkilləri yüklənə bilər.']);
        if ($file->getSize() > 10 * 1024 * 1024) throw ValidationException::withMessages(['file' => 'Şəkil maksimum 10 MB ola bilər.']);
        $path = $file->store('altura-page-builder/'.now()->format('Y/m'), 'public');
        $size = @getimagesize($file->getRealPath()) ?: [null, null];
        $id = DB::table('aa_visual_assets')->insertGetId([
            'disk' => 'public', 'path' => $path, 'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'mime_type' => $mime, 'width' => $size[0], 'height' => $size[1], 'alt_text' => $this->plain($alt, 255),
            'uploaded_by' => $actorId, 'is_deleted' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->asset($id);
    }

    public function updateAsset(int $id, ?string $alt): array
    {
        DB::table('aa_visual_assets')->where('id', $id)->where('is_deleted', false)->update(['alt_text' => $this->plain($alt, 255), 'updated_at' => now()]);
        return $this->asset($id);
    }

    public function deleteAsset(int $id): void
    {
        $asset = DB::table('aa_visual_assets')->where('id', $id)->where('is_deleted', false)->firstOrFail();
        if (DB::table('aa_visual_page_revision_assets')->where('asset_id', $id)->exists()) throw ValidationException::withMessages(['asset' => 'Bu media dərc edilmiş və ya tarixçədə saxlanılan səhifə revision-u tərəfindən istifadə edilir.']);
        Storage::disk((string) $asset->disk)->delete((string) $asset->path);
        DB::table('aa_visual_assets')->where('id', $id)->update(['is_deleted' => true, 'deleted_at' => now(), 'updated_at' => now()]);
    }

    private function ensurePage(string $language, string $pageKey, ?string $title = null, bool $lock = false): object
    {
        $key = $this->pageKey($pageKey);
        $query = DB::table('aa_visual_pages')->where('lang_code', $language)->where('page_key', $key);
        if ($lock) $query->lockForUpdate();
        $page = $query->first();
        if ($page) return $page;
        $pageTitle = $title ?: $this->pageTitle($language, $key);
        $id = DB::table('aa_visual_pages')->insertGetId([
            'lang_code' => $language, 'page_key' => $key, 'title' => $pageTitle, 'meta_title' => null,
            'meta_description' => null, 'meta_keywords' => null, 'template' => null, 'active_revision_id' => null,
            'is_archived' => false, 'is_deleted' => false, 'lock_version' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return DB::table('aa_visual_pages')->where('id', $id)->first();
    }

    private function updatePageMeta(int $pageId, array $meta): void
    {
        $allowed = [
            'title' => $this->plain($meta['title'] ?? null, 255),
            'meta_title' => $this->plain($meta['meta_title'] ?? null, 255),
            'meta_description' => $this->plain($meta['meta_description'] ?? null, 500),
            'meta_keywords' => $this->plain($meta['meta_keywords'] ?? null, 255),
            'template' => $this->plain($meta['template'] ?? null, 120),
            'is_archived' => false, 'is_deleted' => false, 'lock_version' => DB::raw('lock_version + 1'), 'updated_at' => now(),
        ];
        DB::table('aa_visual_pages')->where('id', $pageId)->update($allowed);
    }

    private function syncAssetReferences(int $revisionId, array $document): void
    {
        $ids = $this->assetIds($document);
        if ($ids !== []) {
            $found = DB::table('aa_visual_assets')->whereIn('id', $ids)->where('is_deleted', false)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (count($found) !== count($ids)) throw ValidationException::withMessages(['document' => 'Seçilən media fayllarından biri artıq mövcud deyil.']);
        }
        DB::table('aa_visual_page_revision_assets')->where('revision_id', $revisionId)->delete();
        foreach ($ids as $assetId) DB::table('aa_visual_page_revision_assets')->insert(['revision_id' => $revisionId, 'asset_id' => $assetId, 'created_at' => now()]);
    }

    private function assetIds(array $document): array
    {
        $ids = [];
        $walk = function (array $items) use (&$walk, &$ids): void {
            foreach ($items as $node) {
                if (! is_array($node)) continue;
                foreach ((array) ($node['settings'] ?? []) as $key => $value) {
                    if ((str_ends_with((string) $key, '_asset_id') || str_ends_with((string) $key, '_image_id') || in_array($key, ['asset_id', 'image_id'], true)) && filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) $ids[] = (int) $value;
                }
                $walk((array) ($node['blocks'] ?? []));
            }
        };
        $walk((array) ($document['sections'] ?? []));
        $walk((array) ($document['layout']['header']['sections'] ?? []));
        $walk((array) ($document['layout']['footer']['sections'] ?? []));
        return array_values(array_unique($ids));
    }

    private function activity(int $pageId, ?int $actorId, string $event, array $payload): void
    {
        DB::table('aa_visual_page_activities')->insert(['page_id' => $pageId, 'actor_id' => $actorId, 'event' => $event, 'payload' => json_encode($payload), 'created_at' => now()]);
    }

    private function revisionPayload(object $row): array
    {
        return [
            'id' => (int) $row->id, 'revision_number' => (int) $row->revision_number, 'status' => $row->status,
            'editor_revision' => (int) $row->editor_revision, 'document' => $this->decode($row->document_json),
            'theme_settings' => $this->decode($row->theme_settings), 'change_note' => $row->change_note,
            'created_at' => $row->created_at, 'published_at' => $row->published_at,
        ];
    }

    private function pagePayload(object $row): array
    {
        return [
            'id' => (int) $row->id, 'lang_code' => $row->lang_code, 'page_key' => $row->page_key, 'title' => $row->title,
            'meta_title' => $row->meta_title, 'meta_description' => $row->meta_description, 'meta_keywords' => $row->meta_keywords,
            'template' => $row->template, 'is_archived' => (bool) $row->is_archived, 'is_deleted' => (bool) $row->is_deleted,
            'lock_version' => (int) $row->lock_version,
        ];
    }

    private function assetPayload(object $asset): array
    {
        return [
            'id' => (int) $asset->id, 'original_name' => $asset->original_name, 'path' => $asset->path,
            'url' => Storage::disk((string) $asset->disk)->url((string) $asset->path), 'mime_type' => $asset->mime_type,
            'width' => $asset->width ? (int) $asset->width : null, 'height' => $asset->height ? (int) $asset->height : null,
            'alt_text' => $asset->alt_text,
        ];
    }

    private function normalizeTheme(array $theme): array
    {
        $out = [];
        foreach (Arr::dot($theme) as $key => $value) if (in_array($key, ['colors.primary', 'colors.accent', 'colors.surface'], true) && is_string($value) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $value)) $out[$key] = $value;
        return $out;
    }

    private function decode(mixed $value): array
    {
        if (is_array($value)) return $value;
        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function pageKey(string $key): string
    {
        return preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($key))) ?: 'index';
    }

    private function pageKeyFromUrl(string $url): string
    {
        if ($url === '' || $url === '#') return '';
        $parts = parse_url($url); parse_str((string) ($parts['query'] ?? ''), $query);
        return isset($query['key']) ? $this->pageKey((string) $query['key']) : $this->pageKey((string) preg_replace('/\.php$/i', '', basename((string) ($parts['path'] ?? $url))));
    }

    private function pageTitle(string $language, string $key): string
    {
        $page = DB::table('aa_pages')->where('lang_code', $language)->where('page_key', $key)->value('title');
        return $page ?: ucfirst(str_replace(['_', '-'], ' ', $key));
    }

    private function plain(mixed $value, int $max): ?string
    {
        $value = trim(strip_tags(is_scalar($value) ? (string) $value : ''));
        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
