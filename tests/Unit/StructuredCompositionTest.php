<?php

namespace Tests\Unit;

use App\Models\PageBuilderBlock;
use App\Services\Site\BlockTreeService;
use App\Support\Cms\SafeHtml;
use App\Support\Cms\NativeBlockOptions;
use App\Support\Cms\StructuredBlockRegistry;
use PHPUnit\Framework\TestCase;

class StructuredCompositionTest extends TestCase
{
    public function test_registry_exposes_typed_blocks_slots_and_safe_content(): void
    {
        $registry = new StructuredBlockRegistry;

        $this->assertArrayHasKey('columns', $registry->definitions());
        $this->assertArrayHasKey('stat_list', $registry->definitions());
        $this->assertArrayHasKey('article_listing', $registry->definitions());
        $this->assertTrue($registry->canParent('columns', 'rich_text', 'column_1'));
        $this->assertFalse($registry->canParent('hero', 'rich_text', 'default'));

        $content = $registry->normalizeContent('rich_text', [
            'title' => '<b>Safe title</b>',
            'html' => '<p onclick="bad()">Safe</p><script>alert(1)</script>',
        ], new SafeHtml);

        $this->assertSame('Safe title', $content['title']);
        $this->assertStringNotContainsString('script', $content['html']);
        $this->assertStringNotContainsString('onclick', $content['html']);
    }

    public function test_tree_service_builds_nested_slot_structure(): void
    {
        $root = new PageBuilderBlock(['id' => 1, 'block_uuid' => 'root', 'sort_order' => 1, 'block_type' => 'columns']);
        $child = new PageBuilderBlock(['id' => 2, 'block_uuid' => 'child', 'parent_block_uuid' => 'root', 'slot_key' => 'column_1', 'sort_order' => 1, 'block_type' => 'rich_text']);

        $tree = (new BlockTreeService)->build(collect([$child, $root]));

        $this->assertCount(1, $tree);
        $this->assertSame('root', $tree->first()['block']->block_uuid);
        $this->assertSame('child', $tree->first()['children']->first()['block']->block_uuid);
        $this->assertSame('column_1', $tree->first()['children']->first()['block']->slot_key);
    }

    public function test_dynamic_modules_expose_safe_editing_defaults_and_overrides(): void
    {
        $registry = new StructuredBlockRegistry;
        $home = $registry->definition('home_hero');
        $header = $registry->definition('site_header');

        $this->assertFalse($home['system']);
        $this->assertContains('show_stats', array_column($home['fields'], 'key'));
        $this->assertContains('autoplay_ms', array_column($home['fields'], 'key'));
        $this->assertContains('show_navigation', array_column($header['fields'], 'key'));

        $block = new PageBuilderBlock([
            'block_type' => 'article_listing',
            'content_json' => ['title' => 'Seçilmiş yazılar', 'limit' => 4],
        ]);
        $options = (new NativeBlockOptions)->resolve(['block' => $block]);

        $this->assertSame(4, $options['limit']);
        $this->assertSame('Seçilmiş yazılar', NativeBlockOptions::text($options, 'title', 'Fallback'));
    }
}
