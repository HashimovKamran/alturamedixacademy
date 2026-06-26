<?php

namespace Tests\Feature;

use App\AlturaPageBuilder\Rendering\VisualDocumentRenderer;
use Tests\TestCase;

final class AlturaVisualRendererTest extends TestCase
{
    public function test_cards_render_nested_card_children(): void
    {
        $html = app(VisualDocumentRenderer::class)->renderDocument([
            'layout' => ['header' => ['sections' => [], 'order' => []], 'footer' => ['sections' => [], 'order' => []]],
            'sections' => [
                'cards_1' => [
                    'type' => 'cards',
                    'settings' => ['title' => 'Our cards'],
                    'blocks' => [
                        'card_1' => [
                            'type' => 'card',
                            'settings' => ['title' => 'Nested card', 'text' => 'Nested text', 'url' => '/about', 'icon' => 'fa-solid fa-star'],
                            'blocks' => [],
                            'order' => [],
                        ],
                    ],
                    'order' => ['card_1'],
                ],
            ],
            'order' => ['cards_1'],
        ], ['settings' => [], 'lang' => 'az']);

        $this->assertStringContainsString('Nested card', $html);
        $this->assertStringContainsString('Nested text', $html);
    }

    public function test_faq_renders_nested_faq_item_children(): void
    {
        $html = app(VisualDocumentRenderer::class)->renderDocument([
            'layout' => ['header' => ['sections' => [], 'order' => []], 'footer' => ['sections' => [], 'order' => []]],
            'sections' => [
                'faq_1' => [
                    'type' => 'faq',
                    'settings' => ['title' => 'FAQ'],
                    'blocks' => [
                        'faq_item_1' => [
                            'type' => 'faq_item',
                            'settings' => ['title' => 'Question', 'text' => 'Answer'],
                            'blocks' => [],
                            'order' => [],
                        ],
                    ],
                    'order' => ['faq_item_1'],
                ],
            ],
            'order' => ['faq_1'],
        ], ['settings' => [], 'lang' => 'az']);

        $this->assertStringContainsString('Question', $html);
        $this->assertStringContainsString('Answer', $html);
    }
}
