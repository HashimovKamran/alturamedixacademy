<?php

namespace Tests\Feature;

use App\AlturaPageBuilder\Catalog\AlturaComponentCatalog;
use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\AlturaPageBuilder\Services\AlturaPageRevisionWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class AlturaVisualPageBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite PHP extension is required for database feature tests.');
        }
        parent::setUp();
    }

    public function test_draft_does_not_change_public_document_until_publish(): void
    {
        $service = app(AlturaPageBuilderService::class);
        $workflow = app(AlturaPageRevisionWorkflow::class);
        $document = app(AlturaComponentCatalog::class)->defaultDocument('about');
        $id = $document['order'][0];
        $document['sections'][$id]['settings']['title'] = 'Draft-only heading';

        $saved = $service->saveDraft('az', 'about', [
            'document' => $document,
            'theme_settings' => [],
            'expected_editor_revision' => 0,
            'meta' => ['title' => 'About'],
        ], null);

        $publicBefore = $service->publicDocument('az', 'about');
        $this->assertNotSame('Draft-only heading', $publicBefore['sections'][$id]['settings']['title'] ?? null);

        $workflow->publish('az', 'about', $saved['draft']['id'], null);
        $publicAfter = $service->publicDocument('az', 'about');
        $this->assertSame('Draft-only heading', $publicAfter['sections'][$id]['settings']['title']);
    }

    public function test_stale_editor_revision_is_rejected(): void
    {
        $service = app(AlturaPageBuilderService::class);
        $document = app(AlturaComponentCatalog::class)->defaultDocument('about');
        $service->saveDraft('az', 'about', ['document' => $document, 'theme_settings' => [], 'expected_editor_revision' => 0, 'meta' => []], null);

        $this->expectException(RuntimeException::class);
        $service->saveDraft('az', 'about', ['document' => $document, 'theme_settings' => [], 'expected_editor_revision' => 0, 'meta' => []], null);
    }

    public function test_partial_metadata_does_not_null_required_page_title(): void
    {
        $service = app(AlturaPageBuilderService::class);
        $document = app(AlturaComponentCatalog::class)->defaultDocument('about');

        $saved = $service->saveDraft('az', 'about', [
            'document' => $document,
            'theme_settings' => [],
            'expected_editor_revision' => 0,
            'meta' => [],
        ], null);

        $this->assertNotEmpty($saved['page']['title']);
    }

    public function test_unknown_component_is_rejected_server_side(): void
    {
        $service = app(AlturaPageBuilderService::class);
        $document = app(AlturaComponentCatalog::class)->defaultDocument('about');
        $id = $document['order'][0];
        $document['sections'][$id]['type'] = 'arbitrary_blade_execution';

        $this->expectException(ValidationException::class);
        $service->saveDraft('az', 'about', ['document' => $document, 'theme_settings' => [], 'expected_editor_revision' => 0, 'meta' => []], null);
    }
}
