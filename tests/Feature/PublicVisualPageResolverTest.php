<?php

namespace Tests\Feature;

use App\AlturaPageBuilder\Catalog\AlturaComponentCatalog;
use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\AlturaPageBuilder\Services\AlturaPageRevisionWorkflow;
use App\AlturaPageBuilder\Services\PublicVisualPageResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PublicVisualPageResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite PHP extension is required for database feature tests.');
        }
        parent::setUp();
    }

    public function test_archived_page_does_not_fall_back_to_default_public_document(): void
    {
        $builder = app(AlturaPageBuilderService::class);
        $workflow = app(AlturaPageRevisionWorkflow::class);
        $document = app(AlturaComponentCatalog::class)->defaultDocument('about');
        $saved = $builder->saveDraft('az', 'about', [
            'document' => $document,
            'theme_settings' => [],
            'expected_editor_revision' => 0,
            'meta' => ['title' => 'About'],
        ], null);
        $workflow->publish('az', 'about', $saved['draft']['id'], null);
        $workflow->archive('az', 'about', null);

        $document = app(PublicVisualPageResolver::class)->document('az', 'about');
        $this->assertSame([], $document['sections']);
    }

    public function test_soft_deleted_page_does_not_reappear_with_default_document(): void
    {
        $builder = app(AlturaPageBuilderService::class);
        $document = app(AlturaComponentCatalog::class)->defaultDocument('about');
        $builder->saveDraft('az', 'about', [
            'document' => $document,
            'theme_settings' => [],
            'expected_editor_revision' => 0,
            'meta' => ['title' => 'About'],
        ], null);
        $builder->deletePage('az', 'about', null);

        $resolved = app(PublicVisualPageResolver::class)->document('az', 'about');
        $this->assertSame([], $resolved['sections']);
    }
}
