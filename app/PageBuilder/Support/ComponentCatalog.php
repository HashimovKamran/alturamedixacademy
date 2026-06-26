<?php

namespace App\PageBuilder\Support;

final class ComponentCatalog
{
    /** @return array<string, array<string, mixed>> */
    public function sections(): array
    {
        return [
            'announcement' => [
                'label' => 'Announcement',
                'view' => 'generic-pagebuilder.sections.announcement',
                'fields' => [
                    'text' => $this->text('Text', 'Free shipping on orders over $50'),
                    'link_text' => $this->text('Link label', 'Learn more'),
                    'link_url' => $this->url('Link URL', null, true),
                    'background' => $this->color('Background', '#111827'),
                    'color' => $this->color('Text color', '#ffffff'),
                ],
                'blocks' => [],
            ],
            'header' => [
                'label' => 'Header',
                'view' => 'generic-pagebuilder.sections.header',
                'fields' => [
                    'logo_text' => $this->text('Logo text', 'My Site', true),
                    'logo_url' => $this->url('Logo URL', '/'),
                    'sticky' => ['type' => 'checkbox', 'label' => 'Sticky header', 'default' => true],
                    'cta_label' => $this->text('CTA label', 'Get Started'),
                    'cta_url' => $this->url('CTA URL', '#'),
                ],
                'blocks' => ['nav_link'],
            ],
            'hero' => [
                'label' => 'Hero',
                'view' => 'generic-pagebuilder.sections.hero',
                'fields' => [
                    'eyebrow' => $this->text('Eyebrow', 'Build without limits'),
                    'title' => $this->text('Title', 'Build pages visually', required: true),
                    'description' => $this->textarea('Description', 'Create high quality pages with a flexible section and block editor.'),
                    'image_id' => $this->asset('Hero image'),
                    'primary_text' => $this->text('Primary button label', 'Get started'),
                    'primary_url' => $this->url('Primary button URL', '#'),
                    'secondary_text' => $this->text('Secondary button label', 'Learn more'),
                    'secondary_url' => $this->url('Secondary button URL', null, true),
                    'alignment' => $this->select('Alignment', 'left', ['left', 'center']),
                    'variant' => $this->select('Variant', 'light', ['light', 'dark', 'brand']),
                ],
                'blocks' => [],
            ],
            'rich_text' => [
                'label' => 'Rich text',
                'view' => 'generic-pagebuilder.sections.rich-text',
                'fields' => [
                    'title' => $this->text('Heading', 'Tell your story'),
                    'content' => $this->richText('Content', '<p>Start adding content here.</p>', true),
                    'container' => $this->select('Container', 'narrow', ['narrow', 'default', 'wide']),
                ],
                'blocks' => [],
            ],
            'feature_grid' => [
                'label' => 'Feature grid',
                'view' => 'generic-pagebuilder.sections.feature-grid',
                'fields' => [
                    'eyebrow' => $this->text('Eyebrow', 'Features'),
                    'title' => $this->text('Title', 'Everything you need'),
                    'description' => $this->textarea('Description', 'Compose a flexible feature grid with reusable blocks.'),
                    'columns' => $this->select('Columns', '3', ['2', '3', '4']),
                ],
                'blocks' => ['feature_card'],
            ],
            'content' => [
                'label' => 'Content builder',
                'view' => 'generic-pagebuilder.sections.content',
                'fields' => [
                    'background' => $this->color('Background', '#ffffff'),
                    'padding' => $this->select('Vertical spacing', 'large', ['small', 'medium', 'large']),
                ],
                'blocks' => ['row', 'text', 'button', 'image'],
            ],
            'cta' => [
                'label' => 'Call to action',
                'view' => 'generic-pagebuilder.sections.cta',
                'fields' => [
                    'title' => $this->text('Title', 'Ready to get started?', required: true),
                    'description' => $this->textarea('Description', 'Create the next page in minutes.'),
                    'button_text' => $this->text('Button label', 'Start now'),
                    'button_url' => $this->url('Button URL', '#'),
                    'variant' => $this->select('Variant', 'brand', ['brand', 'dark', 'muted']),
                ],
                'blocks' => [],
            ],
            'footer' => [
                'label' => 'Footer',
                'view' => 'generic-pagebuilder.sections.footer',
                'fields' => [
                    'copyright_text' => $this->text('Copyright text', 'Â© Your Company. All rights reserved.'),
                    'privacy_url' => $this->url('Privacy URL', '/privacy'),
                    'terms_url' => $this->url('Terms URL', '/terms'),
                    'facebook_url' => $this->url('Facebook URL', null, true),
                    'instagram_url' => $this->url('Instagram URL', null, true),
                    'twitter_url' => $this->url('X URL', null, true),
                ],
                'blocks' => ['footer_column'],
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public function blocks(): array
    {
        return [
            'nav_link' => [
                'label' => 'Navigation link',
                'view' => 'generic-pagebuilder.blocks.nav-link',
                'fields' => [
                    'label' => $this->text('Label', 'Link', true),
                    'url' => $this->url('URL', '#'),
                ],
                'blocks' => [],
            ],
            'row' => [
                'label' => 'Row',
                'view' => 'generic-pagebuilder.blocks.row',
                'fields' => [
                    'columns' => $this->select('Columns', '2', ['1', '2', '3', '4']),
                    'gap' => $this->select('Gap', 'medium', ['small', 'medium', 'large']),
                ],
                'blocks' => ['column'],
            ],
            'column' => [
                'label' => 'Column',
                'view' => 'generic-pagebuilder.blocks.column',
                'fields' => [
                    'padding' => $this->select('Padding', 'medium', ['none', 'small', 'medium', 'large']),
                    'background' => $this->color('Background', 'transparent'),
                ],
                'blocks' => ['text', 'button', 'image', 'feature_card'],
            ],
            'text' => [
                'label' => 'Text',
                'view' => 'generic-pagebuilder.blocks.text',
                'fields' => [
                    'content' => $this->richText('Content', '<p>Write your content here.</p>', true),
                    'alignment' => $this->select('Alignment', 'left', ['left', 'center', 'right']),
                ],
                'blocks' => [],
            ],
            'button' => [
                'label' => 'Button',
                'view' => 'generic-pagebuilder.blocks.button',
                'fields' => [
                    'text' => $this->text('Label', 'Button', required: true),
                    'url' => $this->url('URL', '#'),
                    'style' => $this->select('Style', 'primary', ['primary', 'secondary', 'link']),
                    'alignment' => $this->select('Alignment', 'left', ['left', 'center', 'right']),
                ],
                'blocks' => [],
            ],
            'image' => [
                'label' => 'Image',
                'view' => 'generic-pagebuilder.blocks.image',
                'fields' => [
                    'asset_id' => $this->asset('Image'),
                    'alt' => $this->text('Alt text', ''),
                    'radius' => $this->select('Corner radius', 'medium', ['none', 'small', 'medium', 'large']),
                ],
                'blocks' => [],
            ],
            'feature_card' => [
                'label' => 'Feature card',
                'view' => 'generic-pagebuilder.blocks.feature-card',
                'fields' => [
                    'icon' => ['type' => 'icon', 'label' => 'Icon', 'default' => 'sparkles'],
                    'title' => $this->text('Title', 'Feature title', required: true),
                    'description' => $this->textarea('Description', 'Explain the feature and its value.'),
                    'url' => $this->url('Link URL', null, true),
                ],
                'blocks' => [],
            ],
            'footer_column' => [
                'label' => 'Footer column',
                'view' => 'generic-pagebuilder.blocks.footer-column',
                'fields' => [
                    'title' => $this->text('Heading', 'Column', true),
                ],
                'blocks' => ['footer_link'],
            ],
            'footer_link' => [
                'label' => 'Footer link',
                'view' => 'generic-pagebuilder.blocks.footer-link',
                'fields' => [
                    'label' => $this->text('Label', 'Link', true),
                    'url' => $this->url('URL', '#'),
                ],
                'blocks' => [],
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function themeSettings(): array
    {
        return [
            ['name' => 'Colors', 'settings' => [
                ['key' => 'colors.primary', 'label' => 'Primary', 'type' => 'color', 'default' => '#2563eb', 'css_var' => '--pb-primary'],
                ['key' => 'colors.background', 'label' => 'Background', 'type' => 'color', 'default' => '#ffffff', 'css_var' => '--pb-background'],
                ['key' => 'colors.text', 'label' => 'Text', 'type' => 'color', 'default' => '#111827', 'css_var' => '--pb-text'],
            ]],
            ['name' => 'Typography', 'settings' => [
                ['key' => 'fonts.display', 'label' => 'Display font', 'type' => 'text', 'default' => 'Inter, sans-serif', 'css_var' => '--pb-font-display'],
                ['key' => 'fonts.body', 'label' => 'Body font', 'type' => 'text', 'default' => 'Inter, sans-serif', 'css_var' => '--pb-font-body'],
            ]],
        ];
    }

    public function section(string $type): ?array
    {
        return $this->sections()[$type] ?? null;
    }

    public function block(string $type): ?array
    {
        return $this->blocks()[$type] ?? null;
    }

    /** @return array<string, mixed> */
    public function emptyDocument(): array
    {
        return [
            'schema_version' => 1,
            'layout' => ['type' => 'default', 'header' => ['sections' => [], 'order' => []], 'footer' => ['sections' => [], 'order' => []]],
            'sections' => [],
            'order' => [],
        ];
    }

    private function text(string $label, string $default, bool $required = false): array
    {
        return ['type' => 'text', 'label' => $label, 'default' => $default, 'required' => $required];
    }

    private function textarea(string $label, string $default): array
    {
        return ['type' => 'textarea', 'label' => $label, 'default' => $default];
    }

    private function richText(string $label, string $default, bool $required = false): array
    {
        return ['type' => 'rich_text', 'label' => $label, 'default' => $default, 'required' => $required];
    }

    private function select(string $label, string $default, array $options): array
    {
        return ['type' => 'select', 'label' => $label, 'default' => $default, 'options' => $options];
    }

    private function color(string $label, string $default): array
    {
        return ['type' => 'color', 'label' => $label, 'default' => $default];
    }

    private function url(string $label, ?string $default, bool $nullable = false): array
    {
        return ['type' => 'url', 'label' => $label, 'default' => $default, 'nullable' => $nullable];
    }

    private function asset(string $label): array
    {
        return ['type' => 'asset', 'label' => $label, 'default' => null, 'nullable' => true];
    }
}

