<?php

namespace Tests\Unit;

use App\AlturaPageBuilder\Support\DocumentValidator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class AlturaVisualDocumentValidatorTest extends TestCase
{
    public function test_header_zone_rejects_main_only_section(): void
    {
        $this->expectException(ValidationException::class);

        app(DocumentValidator::class)->validate([
            'layout' => [
                'header' => [
                    'sections' => [
                        'hero_1' => ['type' => 'home_hero', 'settings' => [], 'blocks' => [], 'order' => []],
                    ],
                    'order' => ['hero_1'],
                ],
            ],
        ]);
    }

    public function test_unknown_fields_do_not_persist_in_normalized_document(): void
    {
        $document = app(DocumentValidator::class)->validate([
            'sections' => [
                'text_1' => [
                    'type' => 'rich_text',
                    'settings' => [
                        'title' => 'Safe title',
                        'html' => '<p>Safe body</p>',
                        'unknown_field' => 'must not persist',
                    ],
                    'blocks' => [],
                    'order' => [],
                ],
            ],
            'order' => ['text_1'],
        ]);

        $settings = $document['sections']['text_1']['settings'];
        $this->assertSame('Safe title', $settings['title']);
        $this->assertArrayNotHasKey('unknown_field', $settings);
    }

    public function test_unknown_component_is_rejected_before_persistence(): void
    {
        $this->expectException(ValidationException::class);

        app(DocumentValidator::class)->validate([
            'sections' => [
                'unknown_1' => ['type' => 'not_in_catalog', 'settings' => [], 'blocks' => [], 'order' => []],
            ],
            'order' => ['unknown_1'],
        ]);
    }
}
