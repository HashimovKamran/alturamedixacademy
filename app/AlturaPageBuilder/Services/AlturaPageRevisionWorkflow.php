<?php

namespace App\AlturaPageBuilder\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AlturaPageRevisionWorkflow
{
    public function publish(string $language, string $pageKey, int $revisionId, ?int $actorId, ?string $note = null): array
    {
        return DB::transaction(function () use ($language, $pageKey, $revisionId, $actorId, $note): array {
            $page = $this->page($language, $pageKey, true);
            $draft = DB::table('aa_visual_page_revisions')->where('id', $revisionId)->where('page_id', $page->id)->lockForUpdate()->first();
            if (! $draft || $draft->status !== 'draft') {
                throw ValidationException::withMessages(['revision_id' => 'Dərc ediləcək aktiv draft tapılmadı.']);
            }
            DB::table('aa_visual_page_revisions')->where('id', $draft->id)->update([
                'status' => 'published',
                'change_note' => mb_substr(trim((string) $note), 0, 255),
                'published_by' => $actorId,
                'published_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('aa_visual_pages')->where('id', $page->id)->update([
                'active_revision_id' => $draft->id,
                'is_archived' => false,
                'updated_at' => now(),
            ]);
            $this->activity($page->id, $actorId, 'published', ['revision_id' => (int) $draft->id]);
            return $this->payload(DB::table('aa_visual_page_revisions')->where('id', $draft->id)->first());
        });
    }

    public function rollback(string $language, string $pageKey, int $revisionId, ?int $actorId): array
    {
        return DB::transaction(function () use ($language, $pageKey, $revisionId, $actorId): array {
            $page = $this->page($language, $pageKey, true);
            $source = DB::table('aa_visual_page_revisions')->where('id', $revisionId)->where('page_id', $page->id)->whereIn('status', ['published', 'archived'])->lockForUpdate()->first();
            if (! $source) {
                throw ValidationException::withMessages(['revision_id' => 'Bərpa ediləcək revision tapılmadı.']);
            }
            $number = (int) DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->max('revision_number') + 1;
            $id = DB::table('aa_visual_page_revisions')->insertGetId([
                'page_id' => $page->id,
                'revision_number' => $number,
                'status' => 'published',
                'editor_revision' => 0,
                'document_json' => $source->document_json,
                'theme_settings' => $source->theme_settings,
                'change_note' => 'Restored from revision '.$source->revision_number,
                'created_by' => $actorId,
                'published_by' => $actorId,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('aa_visual_pages')->where('id', $page->id)->update(['active_revision_id' => $id, 'is_archived' => false, 'updated_at' => now()]);
            $this->activity($page->id, $actorId, 'rollback', ['source_revision_id' => (int) $source->id, 'revision_id' => $id]);
            return $this->payload(DB::table('aa_visual_page_revisions')->where('id', $id)->first());
        });
    }

    public function archive(string $language, string $pageKey, ?int $actorId): void
    {
        DB::transaction(function () use ($language, $pageKey, $actorId): void {
            $page = $this->page($language, $pageKey, true);
            DB::table('aa_visual_pages')->where('id', $page->id)->update(['is_archived' => true, 'active_revision_id' => null, 'updated_at' => now()]);
            $this->activity($page->id, $actorId, 'archived', []);
        });
    }

    public function restorePage(string $language, string $pageKey, ?int $actorId): array
    {
        return DB::transaction(function () use ($language, $pageKey, $actorId): array {
            $page = $this->page($language, $pageKey, true);
            $revision = DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->whereIn('status', ['published', 'archived'])->latest('revision_number')->first();
            if (! $revision) {
                throw ValidationException::withMessages(['page' => 'Bərpa ediləcək revision yoxdur.']);
            }
            DB::table('aa_visual_pages')->where('id', $page->id)->update(['is_archived' => false, 'active_revision_id' => $revision->id, 'updated_at' => now()]);
            $this->activity($page->id, $actorId, 'restored', ['revision_id' => (int) $revision->id]);
            return ['id' => (int) $page->id, 'page_key' => $page->page_key, 'lang_code' => $page->lang_code, 'active_revision_id' => (int) $revision->id];
        });
    }

    private function page(string $language, string $pageKey, bool $lock): object
    {
        $query = DB::table('aa_visual_pages')->where('lang_code', $language)->where('page_key', $pageKey);
        if ($lock) $query->lockForUpdate();
        $page = $query->first();
        if (! $page) throw ValidationException::withMessages(['page_key' => 'Səhifə tapılmadı.']);
        return $page;
    }

    private function payload(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'revision_number' => (int) $row->revision_number,
            'status' => $row->status,
            'editor_revision' => (int) $row->editor_revision,
            'document' => is_array($row->document_json) ? $row->document_json : (json_decode((string) $row->document_json, true) ?: []),
            'theme_settings' => is_array($row->theme_settings) ? $row->theme_settings : (json_decode((string) $row->theme_settings, true) ?: []),
            'change_note' => $row->change_note,
            'created_at' => $row->created_at,
            'published_at' => $row->published_at,
        ];
    }

    private function activity(int $pageId, ?int $actorId, string $event, array $payload): void
    {
        DB::table('aa_visual_page_activities')->insert(['page_id' => $pageId, 'actor_id' => $actorId, 'event' => $event, 'payload' => json_encode($payload), 'created_at' => now()]);
    }
}
