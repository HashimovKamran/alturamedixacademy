<?php

namespace App\AlturaPageBuilder\Services;

use App\AlturaPageBuilder\Catalog\AlturaComponentCatalog;
use Illuminate\Support\Facades\DB;

final class PublicVisualPageResolver
{
    public function __construct(private readonly AlturaComponentCatalog $catalog) {}

    public function document(string $language, string $pageKey, bool $preview = false): array
    {
        $key = $this->pageKey($pageKey);
        $page = DB::table('aa_visual_pages')
            ->where('lang_code', $language)
            ->where('page_key', $key)
            ->first();

        // A page that has not yet been opened in the editor remains backed by the code-owned
        // Alturamedix default document. Archived/deleted pages are deliberately different:
        // they must render nothing, never silently revert to a default public page.
        if (! $page) return $this->catalog->defaultDocument($key);
        if ((bool) $page->is_deleted || (bool) $page->is_archived) return $this->emptyDocument();

        $revision = null;
        if ($preview) {
            $revision = DB::table('aa_visual_page_revisions')
                ->where('page_id', $page->id)
                ->where('status', 'draft')
                ->latest('id')
                ->first();
        }

        if (! $revision && $page->active_revision_id) {
            $revision = DB::table('aa_visual_page_revisions')
                ->where('id', $page->active_revision_id)
                ->where('status', 'published')
                ->first();
        }

        return $revision ? $this->decode($revision->document_json) : $this->catalog->defaultDocument($key);
    }

    private function emptyDocument(): array
    {
        return [
            'schema_version' => 1,
            'layout' => [
                'type' => 'alturamedix',
                'header' => ['sections' => [], 'order' => []],
                'footer' => ['sections' => [], 'order' => []],
            ],
            'sections' => [],
            'order' => [],
        ];
    }

    private function decode(mixed $value): array
    {
        if (is_array($value)) return $value;
        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : $this->emptyDocument();
    }

    private function pageKey(string $key): string
    {
        return preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($key))) ?: 'index';
    }
}
