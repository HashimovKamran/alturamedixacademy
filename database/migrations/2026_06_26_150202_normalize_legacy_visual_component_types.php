<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('aa_visual_page_revisions')) return;

        foreach (DB::table('aa_visual_page_revisions')->get() as $revision) {
            $document = json_decode((string) $revision->document_json, true);
            if (! is_array($document)) continue;

            $document['sections'] = $this->normalizeMap((array) ($document['sections'] ?? []));
            foreach (['header', 'footer'] as $zone) {
                $layout = (array) ($document['layout'][$zone] ?? []);
                $layout['sections'] = $this->normalizeMap((array) ($layout['sections'] ?? []));
                $layout['order'] = array_values((array) ($layout['order'] ?? []));
                $document['layout'][$zone] = $layout;
            }

            DB::table('aa_visual_page_revisions')->where('id', $revision->id)->update([
                'document_json' => json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void {}

    private function normalizeMap(array $nodes): array
    {
        $types = [
            'text' => 'rich_text',
            'video' => 'video_embed',
            'native_home' => 'home_content_grid',
            'native_page' => 'page_content',
            'native_articles' => 'article_archive',
            'native_article' => 'article_detail',
            'native_gallery' => 'gallery_listing',
            'native_certificates' => 'certificate_lookup',
            'native_trainings' => 'training_listing',
            'native_profile' => 'profile_card',
        ];

        foreach ($nodes as $id => $node) {
            if (! is_array($node)) continue;
            $node['type'] = $types[$node['type'] ?? 'rich_text'] ?? ($node['type'] ?? 'rich_text');
            $node['blocks'] = $this->normalizeMap((array) ($node['blocks'] ?? []));
            $node['order'] = array_values((array) ($node['order'] ?? array_keys($node['blocks'])));
            $nodes[$id] = $node;
        }

        return $nodes;
    }
};
