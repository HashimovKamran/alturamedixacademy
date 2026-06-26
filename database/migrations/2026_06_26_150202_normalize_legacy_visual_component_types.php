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

            $document['sections'] = $this->normalizeMap((array) ($document['sections'] ?? []), 'main');
            foreach (['header', 'footer'] as $zone) {
                $layout = (array) ($document['layout'][$zone] ?? []);
                $layout['sections'] = $this->normalizeMap((array) ($layout['sections'] ?? []), $zone);
                $layout['order'] = array_values(array_filter((array) ($layout['order'] ?? []), fn ($id) => isset($layout['sections'][$id])));
                foreach (array_keys($layout['sections']) as $id) {
                    if (! in_array($id, $layout['order'], true)) $layout['order'][] = $id;
                }
                $document['layout'][$zone] = $layout;
            }

            DB::table('aa_visual_page_revisions')->where('id', $revision->id)->update([
                'document_json' => json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void {}

    private function normalizeMap(array $nodes, string $zone): array
    {
        $legacy = [
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
        $allowed = [
            'site_header', 'site_footer', 'home_hero', 'home_content_grid', 'home_journal',
            'page_content', 'contact_grid', 'rich_text', 'image_text', 'cards', 'stat_list',
            'faq', 'cta', 'video_embed', 'gallery', 'button_group', 'spacer', 'divider',
            'group', 'columns', 'article_listing', 'article_archive', 'article_detail',
            'training_listing', 'category_listing', 'feature_listing', 'partner_listing',
            'advertisement_listing', 'gallery_listing', 'certificate_lookup', 'profile_card',
            'contact_info', 'contact_form', 'map_embed', 'card', 'stat', 'faq_item',
            'gallery_item', 'button',
        ];

        foreach ($nodes as $id => $node) {
            if (! is_array($node)) continue;

            $original = (string) ($node['type'] ?? 'rich_text');
            $type = $legacy[$original] ?? $original;
            if (! in_array($type, $allowed, true)) {
                $type = match ($zone) {
                    'header' => 'site_header',
                    'footer' => 'site_footer',
                    default => 'rich_text',
                };
                $node['_name'] = $node['_name'] ?? ('Imported legacy: '.$original);
            }

            $node['type'] = $type;
            $node['blocks'] = $this->normalizeMap((array) ($node['blocks'] ?? []), 'main');
            $node['order'] = array_values((array) ($node['order'] ?? array_keys($node['blocks'])));
            foreach (array_keys($node['blocks']) as $childId) {
                if (! in_array($childId, $node['order'], true)) $node['order'][] = $childId;
            }
            $nodes[$id] = $node;
        }

        return $nodes;
    }
};
