<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('aa_visual_pages') || ! Schema::hasTable('aa_visual_page_revisions')) return;
        foreach (DB::table('aa_visual_pages')->whereIn('page_key', ['__header', '__footer'])->get() as $page) {
            $zone = $page->page_key === '__header' ? 'header' : 'footer';
            foreach (DB::table('aa_visual_page_revisions')->where('page_id', $page->id)->get() as $revision) {
                $document = json_decode((string) $revision->document_json, true);
                if (! is_array($document) || empty($document['sections'])) continue;
                $layout = is_array($document['layout'] ?? null) ? $document['layout'] : [];
                $layout[$zone] = is_array($layout[$zone] ?? null) ? $layout[$zone] : ['sections' => [], 'order' => []];
                if (empty($layout[$zone]['sections'])) {
                    $layout[$zone] = ['sections' => $document['sections'], 'order' => $document['order'] ?? array_keys($document['sections'])];
                    $document['sections'] = [];
                    $document['order'] = [];
                    $document['layout'] = $layout;
                    DB::table('aa_visual_page_revisions')->where('id', $revision->id)->update(['document_json' => json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'updated_at' => now()]);
                }
            }
        }
    }

    public function down(): void {}
};
