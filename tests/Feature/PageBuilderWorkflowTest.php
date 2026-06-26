<?php

namespace Tests\Feature;

use App\Models\PageBuilderBlock;
use App\Models\PagePublication;
use App\Models\PageRevision;
use App\Services\Site\PagePublicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite PHP extension is required for database feature tests.');
        }
        parent::setUp();
    }

    public function test_draft_changes_do_not_modify_published_snapshot_until_publish(): void
    {
        $block = PageBuilderBlock::query()->create([
            'lang_code' => 'az', 'page_key' => 'index', 'block_type' => 'text',
            'title' => 'Version one', 'body' => '<p>One</p>', 'sort_order' => 1, 'is_active' => true,
        ]);
        $service = app(PagePublicationService::class);
        $service->publish('az', 'index', null, 'First publish');

        $block->update(['title' => 'Draft version two']);

        $this->assertSame('Version one', $service->publishedBlocks('az', 'index')->first()->title);
        $this->assertSame('Draft version two', $service->workingBlocks('az', 'index')->first()->title);
        $this->assertSame(1, PagePublication::query()->value('version'));

        $service->publish('az', 'index', null, 'Second publish');
        $this->assertSame('Draft version two', $service->publishedBlocks('az', 'index')->first()->title);
        $this->assertSame(2, PagePublication::query()->value('version'));
        $this->assertCount(2, PageRevision::all());
    }

    public function test_revision_can_be_restored_as_a_new_version(): void
    {
        $block = PageBuilderBlock::query()->create([
            'lang_code' => 'az', 'page_key' => 'about', 'block_type' => 'text',
            'title' => 'Original', 'sort_order' => 1, 'is_active' => true,
        ]);
        $service = app(PagePublicationService::class);
        $service->publish('az', 'about', null);
        $first = PageRevision::query()->firstOrFail();
        $block->update(['title' => 'Changed']);
        $service->publish('az', 'about', null);
        $service->restore($first, null);

        $this->assertSame('Original', $service->publishedBlocks('az', 'about')->first()->title);
        $this->assertSame(3, PagePublication::query()->value('version'));
    }
}
