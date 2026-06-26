<?php

namespace App\Console\Commands;

use App\PageBuilder\Services\PageWorkflow;
use Illuminate\Console\Command;

final class SeedPageBuilderDemo extends Command
{
    protected $signature = 'pagebuilder:seed-demo {--slug=home : Slug for the demo page}';

    protected $description = 'Create and publish a safe demo page for the React Page Builder';

    public function handle(PageWorkflow $workflow): int
    {
        $slug = (string) $this->option('slug');
        $document = [
            'schema_version' => 1,
            'layout' => [
                'type' => 'default',
                'header' => [
                    'sections' => [
                        'header_demo' => [
                            'type' => 'header',
                            '_name' => 'Demo header',
                            'disabled' => false,
                            'settings' => [
                                'logo_text' => 'Page Builder',
                                'logo_url' => '/',
                                'sticky' => true,
                                'cta_label' => 'Open editor',
                                'cta_url' => '/pagebuilder/editor/'.$slug,
                            ],
                            'blocks' => [
                                'nav_features' => ['type' => 'nav_link', '_name' => null, 'disabled' => false, 'settings' => ['label' => 'Features', 'url' => '#features'], 'blocks' => [], 'order' => []],
                                'nav_editor' => ['type' => 'nav_link', '_name' => null, 'disabled' => false, 'settings' => ['label' => 'Editor', 'url' => '/pagebuilder/editor/'.$slug], 'blocks' => [], 'order' => []],
                            ],
                            'order' => ['nav_features', 'nav_editor'],
                        ],
                    ],
                    'order' => ['header_demo'],
                ],
                'footer' => [
                    'sections' => [
                        'footer_demo' => [
                            'type' => 'footer',
                            '_name' => 'Demo footer',
                            'disabled' => false,
                            'settings' => [
                                'copyright_text' => '© Page Builder demo.',
                                'privacy_url' => '#privacy',
                                'terms_url' => '#terms',
                                'facebook_url' => null,
                                'instagram_url' => null,
                                'twitter_url' => null,
                            ],
                            'blocks' => [
                                'footer_product' => [
                                    'type' => 'footer_column',
                                    '_name' => null,
                                    'disabled' => false,
                                    'settings' => ['title' => 'Product'],
                                    'blocks' => [
                                        'footer_link_editor' => ['type' => 'footer_link', '_name' => null, 'disabled' => false, 'settings' => ['label' => 'Visual editor', 'url' => '/pagebuilder/editor/'.$slug], 'blocks' => [], 'order' => []],
                                    ],
                                    'order' => ['footer_link_editor'],
                                ],
                            ],
                            'order' => ['footer_product'],
                        ],
                    ],
                    'order' => ['footer_demo'],
                ],
            ],
            'sections' => [
                'announcement_demo' => [
                    'type' => 'announcement',
                    '_name' => 'Announcement',
                    'disabled' => false,
                    'settings' => [
                        'text' => 'A Laravel 13 and React visual page builder.',
                        'link_text' => 'Open editor',
                        'link_url' => '/pagebuilder/editor/'.$slug,
                        'background' => '#152033',
                        'color' => '#ffffff',
                    ],
                    'blocks' => [],
                    'order' => [],
                ],
                'hero_demo' => [
                    'type' => 'hero',
                    '_name' => 'Demo hero',
                    'disabled' => false,
                    'settings' => [
                        'eyebrow' => 'Laravel + React',
                        'title' => 'Build pages visually without unsafe template execution.',
                        'description' => 'Add sections, nest blocks, upload assets, save drafts and publish immutable revisions.',
                        'image_id' => null,
                        'primary_text' => 'Open editor',
                        'primary_url' => '/pagebuilder/editor/'.$slug,
                        'secondary_text' => 'Learn more',
                        'secondary_url' => '#features',
                        'alignment' => 'left',
                        'variant' => 'light',
                    ],
                    'blocks' => [],
                    'order' => [],
                ],
                'features_demo' => [
                    'type' => 'feature_grid',
                    '_name' => 'Feature grid',
                    'disabled' => false,
                    'settings' => [
                        'eyebrow' => 'Capabilities',
                        'title' => 'A project-level page builder',
                        'description' => 'The demo data is stored in database revisions, not project JSON files.',
                        'columns' => '3',
                    ],
                    'blocks' => [
                        'feature_one' => ['type' => 'feature_card', '_name' => null, 'disabled' => false, 'settings' => ['icon' => 'layers', 'title' => 'Nested content', 'description' => 'Sections and recursive block structures.', 'url' => null], 'blocks' => [], 'order' => []],
                        'feature_two' => ['type' => 'feature_card', '_name' => null, 'disabled' => false, 'settings' => ['icon' => 'shield', 'title' => 'Safe renderer', 'description' => 'Server-owned views, sanitized rich text and validated URLs.', 'url' => null], 'blocks' => [], 'order' => []],
                        'feature_three' => ['type' => 'feature_card', '_name' => null, 'disabled' => false, 'settings' => ['icon' => 'history', 'title' => 'Version workflow', 'description' => 'Draft, publish and immutable rollback.', 'url' => null], 'blocks' => [], 'order' => []],
                    ],
                    'order' => ['feature_one', 'feature_two', 'feature_three'],
                ],
            ],
            'order' => ['announcement_demo', 'hero_demo', 'features_demo'],
        ];

        $saved = $workflow->saveDraft($slug, [
            'document' => $document,
            'theme_settings' => [],
            'expected_editor_revision' => 0,
            'meta' => ['title' => 'Page Builder Demo', 'template' => 'landing'],
        ]);
        $workflow->publish($slug, $saved['draft']->getKey());

        $this->info('Demo page published at /'.($slug === 'home' ? '' : $slug));
        $this->line('Editor: /pagebuilder/editor/'.$slug);

        return self::SUCCESS;
    }
}
