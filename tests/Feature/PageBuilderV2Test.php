<?php

namespace Tests\Feature;

use App\Models\PageBuilderBlock;
use App\Models\PageBuilderDocument;
use App\Models\PagePublication;
use App\PageBuilder\Registry\BlockDefinitionRegistry;
use App\PageBuilder\Rendering\Renderer;
use App\PageBuilder\Services\PageDocumentService;
use App\Services\Site\PagePublicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PageBuilderV2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite PHP extension is required for database feature tests.');
        }

        parent::setUp();
    }

    public function test_section_registry_keeps_legacy_labels_for_ported_blade_schemas(): void
    {
        $definition = app(BlockDefinitionRegistry::class)->definition('rich_text', 'sections');

        $this->assertNotSame('rich_text.blade', $definition['label']);
        $this->assertContains('html', array_column($definition['fields'], 'key'));
    }

    public function test_legacy_block_tree_is_adapted_to_v2_document(): void
    {
        $rootUuid = (string) Str::uuid();
        $childUuid = (string) Str::uuid();

        PageBuilderBlock::query()->create([
            'lang_code' => 'az',
            'page_key' => 'index',
            'block_uuid' => $rootUuid,
            'block_type' => 'columns',
            'content_json' => ['columns' => '2'],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        PageBuilderBlock::query()->create([
            'lang_code' => 'az',
            'page_key' => 'index',
            'block_uuid' => $childUuid,
            'parent_block_uuid' => $rootUuid,
            'slot_key' => 'column_1',
            'block_type' => 'rich_text',
            'content_json' => ['title' => 'Nested', 'html' => '<p>Body</p>'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $document = app(PageDocumentService::class)->working('az', 'index');

        $this->assertSame(2, $document['schema_version']);
        $this->assertSame([$rootUuid], $document['order']);
        $this->assertSame('columns', $document['sections'][$rootUuid]['type']);
        $this->assertSame([$childUuid], $document['sections'][$rootUuid]['order']);
        $this->assertSame('column_1', $document['sections'][$rootUuid]['blocks'][$childUuid]['slot_key']);
    }

    public function test_v2_document_mutators_and_renderer_use_json_source(): void
    {
        $documents = app(PageDocumentService::class);
        $result = $documents->addSection('az', 'index', 'rich_text', null, 7);
        $uuid = $result['node']['block_uuid'];

        $documents->updateNode('az', 'index', $uuid, function (array &$node): void {
            $node['content'] = ['title' => 'Hello v2', 'html' => '<p>Stored in JSON</p>'];
        }, 7);

        $stored = PageBuilderDocument::query()->firstOrFail();
        $html = app(Renderer::class)->renderDocument($stored->document_json);

        $this->assertSame(7, $stored->updated_by);
        $this->assertStringContainsString('pb-type-rich_text', $html);
        $this->assertStringContainsString('Hello v2', $html);
        $this->assertStringContainsString('Stored in JSON', $html);
        $this->assertStringNotContainsString('data-editor-section', $html);
    }

    public function test_publish_snapshots_v2_document_until_next_publish(): void
    {
        $documents = app(PageDocumentService::class);
        $uuid = (string) Str::uuid();
        $documents->save('az', 'about', [
            'sections' => [
                $uuid => [
                    'id' => $uuid,
                    'block_uuid' => $uuid,
                    'type' => 'text',
                    'settings' => [],
                    'content' => ['title' => 'Published', 'html' => '<p>Version one</p>'],
                    'blocks' => [],
                    'order' => [],
                ],
            ],
            'order' => [$uuid],
        ]);

        $publisher = app(PagePublicationService::class);
        $publisher->publish('az', 'about', null, 'Initial');
        $documents->updateNode('az', 'about', $uuid, function (array &$node): void {
            $node['content']['title'] = 'Draft only';
        });

        $publication = PagePublication::query()->firstOrFail();
        $this->assertSame(2, $publication->document_schema_version);
        $this->assertSame('Published', $publication->document_json['sections'][$uuid]['content']['title']);

        $publisher->publish('az', 'about', null, 'Second');
        $publication->refresh();

        $this->assertSame(2, $publication->version);
        $this->assertSame('Draft only', $publication->document_json['sections'][$uuid]['content']['title']);
    }
}
