<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aa_visual_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('lang_code', 8);
            $table->string('page_key', 120);
            $table->string('title');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('template', 120)->nullable();
            $table->unsignedBigInteger('active_revision_id')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
            $table->unique(['lang_code', 'page_key']);
            $table->index(['lang_code', 'is_archived', 'is_deleted']);
        });

        Schema::create('aa_visual_page_revisions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedInteger('revision_number');
            $table->string('status', 20);
            $table->unsignedInteger('editor_revision')->default(0);
            $table->json('document_json');
            $table->json('theme_settings')->nullable();
            $table->string('change_note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['page_id', 'revision_number']);
            $table->index(['page_id', 'status']);
        });

        Schema::create('aa_visual_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('disk', 40)->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->index(['is_deleted', 'created_at']);
        });

        Schema::create('aa_visual_page_revision_assets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('revision_id');
            $table->unsignedBigInteger('asset_id');
            $table->timestamp('created_at')->nullable();
            $table->unique(['revision_id', 'asset_id']);
            $table->index('asset_id');
        });

        Schema::create('aa_visual_page_activities', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event', 80);
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['page_id', 'created_at']);
        });

        $this->importLegacyDocuments();
        foreach (['aa_page_builder_documents', 'aa_page_builder_blocks', 'aa_page_publications', 'aa_page_revisions', 'aa_block_patterns'] as $table) {
            if (Schema::hasTable($table)) Schema::drop($table);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aa_visual_page_activities');
        Schema::dropIfExists('aa_visual_page_revision_assets');
        Schema::dropIfExists('aa_visual_assets');
        Schema::dropIfExists('aa_visual_page_revisions');
        Schema::dropIfExists('aa_visual_pages');
    }

    private function importLegacyDocuments(): void
    {
        if (! Schema::hasTable('aa_page_builder_documents') && ! Schema::hasTable('aa_page_publications')) return;
        $working = Schema::hasTable('aa_page_builder_documents')
            ? DB::table('aa_page_builder_documents')->get()->keyBy(fn ($row) => $row->lang_code.'|'.$row->page_key)
            : collect();
        $published = Schema::hasTable('aa_page_publications')
            ? DB::table('aa_page_publications')->get()->keyBy(fn ($row) => $row->lang_code.'|'.$row->page_key)
            : collect();
        foreach ($working->keys()->merge($published->keys())->unique() as $key) {
            [$lang, $pageKey] = explode('|', $key, 2);
            $title = Schema::hasTable('aa_pages') ? (DB::table('aa_pages')->where('lang_code', $lang)->where('page_key', $pageKey)->value('title') ?: ucfirst(str_replace(['_', '-'], ' ', $pageKey))) : ucfirst(str_replace(['_', '-'], ' ', $pageKey));
            $pageId = DB::table('aa_visual_pages')->insertGetId([
                'lang_code' => $lang, 'page_key' => $pageKey, 'title' => $title, 'lock_version' => 1,
                'is_archived' => false, 'is_deleted' => false, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $revisionNumber = 0;
            $publication = $published->get($key);
            if ($publication) {
                $revisionNumber++;
                $revisionId = DB::table('aa_visual_page_revisions')->insertGetId([
                    'page_id' => $pageId, 'revision_number' => $revisionNumber, 'status' => 'published', 'editor_revision' => 0,
                    'document_json' => json_encode($this->convertDocument($publication->document_json), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'theme_settings' => json_encode([]), 'change_note' => 'Imported legacy published snapshot',
                    'created_by' => $publication->published_by ?? null, 'published_by' => $publication->published_by ?? null,
                    'published_at' => $publication->published_at ?? now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('aa_visual_pages')->where('id', $pageId)->update(['active_revision_id' => $revisionId]);
            }
            $draft = $working->get($key);
            if ($draft) {
                $revisionNumber++;
                DB::table('aa_visual_page_revisions')->insert([
                    'page_id' => $pageId, 'revision_number' => $revisionNumber, 'status' => 'draft', 'editor_revision' => 1,
                    'document_json' => json_encode($this->convertDocument($draft->document_json), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'theme_settings' => json_encode([]), 'change_note' => 'Imported legacy working draft',
                    'created_by' => $draft->updated_by ?? null, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    private function convertDocument(mixed $value): array
    {
        $old = is_array($value) ? $value : json_decode((string) $value, true);
        if (! is_array($old)) return ['schema_version' => 1, 'layout' => ['type' => 'alturamedix', 'header' => ['sections' => [], 'order' => []], 'footer' => ['sections' => [], 'order' => []]], 'sections' => [], 'order' => []];
        $convertMap = function (array $items, array $order) use (&$convertMap): array {
            $out = [];
            foreach ($order ?: array_keys($items) as $id) {
                $raw = $items[$id] ?? null;
                if (! is_array($raw)) continue;
                $children = $convertMap((array) ($raw['blocks'] ?? []), (array) ($raw['order'] ?? []));
                $type = ($raw['type'] ?? 'rich_text') === 'text' ? 'rich_text' : (string) ($raw['type'] ?? 'rich_text');
                $settings = is_array($raw['content'] ?? null) ? $raw['content'] : (is_array($raw['settings'] ?? null) ? $raw['settings'] : []);
                $out[(string) $id] = [
                    'type' => $type, '_name' => null, 'disabled' => (bool) ($raw['disabled'] ?? false),
                    'slot_key' => (string) ($raw['slot_key'] ?? 'default'), 'settings' => $settings,
                    'blocks' => $children['sections'], 'order' => $children['order'],
                ];
            }
            return ['sections' => $out, 'order' => array_values(array_filter($order ?: array_keys($out), fn ($id) => isset($out[$id])))];
        };
        $main = $convertMap((array) ($old['sections'] ?? []), (array) ($old['order'] ?? []));
        $headerOld = (array) ($old['layout']['header'] ?? []);
        $footerOld = (array) ($old['layout']['footer'] ?? []);
        $header = $convertMap((array) ($headerOld['sections'] ?? []), (array) ($headerOld['order'] ?? []));
        $footer = $convertMap((array) ($footerOld['sections'] ?? []), (array) ($footerOld['order'] ?? []));
        return ['schema_version' => 1, 'layout' => ['type' => 'alturamedix', 'header' => $header, 'footer' => $footer], 'sections' => $main['sections'], 'order' => $main['order']];
    }
};
