<?php

namespace Tests\Feature;

use App\AlturaPageBuilder\Catalog\AlturaComponentCatalog;
use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\AlturaPageBuilder\Services\AlturaPageRevisionWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AlturaPageRevisionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite PHP extension is required for database feature tests.');
        }
        parent::setUp();
    }

    public function test_earlier_published_revision_can_be_restored_after_later_publish(): void
    {
        $builder = app(AlturaPageBuilderService::class);
        $workflow = app(AlturaPageRevisionWorkflow::class);
        $document = app(AlturaComponentCatalog::class)->defaultDocument('about');
        $nodeId = $document['order'][0];
        $document['sections'][$nodeId]['settings']['title'] = 'Original heading';

        $firstDraft = $builder->saveDraft('az', 'about', [
            'document' => $document,
            'theme_settings' => [],
            'expected_editor_revision' => 0,
            'meta' => ['title' => 'About'],
        ], null);
        $firstPublished = $workflow->publish('az', 'about', $firstDraft['draft']['id'], null);

        $document['sections'][$nodeId]['settings']['title'] = 'Changed heading';
        $secondDraft = $builder->saveDraft('az', 'about', [
            'document' => $document,
            'theme_settings' => [],
            'expected_editor_revision' => 0,
            'meta' => [],
        ], null);
        $workflow->publish('az', 'about', $secondDraft['draft']['id'], null);

        $workflow->rollback('az', 'about', $firstPublished['id'], null);
        $public = $builder->publicDocument('az', 'about');

        $this->assertSame('Original heading', $public['sections'][$nodeId]['settings']['title']);
    }

    public function test_restore_clears_soft_delete_flag_when_a_revision_exists(): void
    {
        $builder = app(AlturaPageBuilderService::class);
        $workflow = app(AlturaPageRevisionWorkflow::class);
        $document = app(AlturaComponentCatalog::class)->defaultDocument('about');
        $draft = $builder->saveDraft('az', 'about', [
            'document' => $document,
            'theme_settings' => [],
            'expected_editor_revision' => 0,
            'meta' => ['title' => 'About'],
        ], null);
        $workflow->publish('az', 'about', $draft['draft']['id'], null);
        $builder->deletePage('az', 'about', null);

        $restored = $workflow->restorePage('az', 'about', null);

        $this->assertFalse($restored['is_deleted']);
        $this->assertFalse($restored['is_archived']);
        $this->assertNotNull($restored['active_revision_id']);
    }
}
