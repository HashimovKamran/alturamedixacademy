<?php

namespace App\PageBuilder\Rendering;

use App\PageBuilder\Components\Component;
use App\PageBuilder\Registry\BlockDefinitionRegistry;
use App\Support\Cms\StructuredBlockRegistry;
use Illuminate\Support\Facades\View;

class Renderer
{
    private const RAW_DESIGN_TYPES = [
        'home_hero', 'home_content_grid', 'home_journal', 'contact_grid',
        'category_listing', 'article_listing', 'training_listing',
        'advertisement_listing', 'feature_listing', 'partner_listing',
        'page_content', 'article_archive', 'gallery_listing',
        'certificate_lookup', 'contact_info', 'contact_form', 'map_embed',
        'article_detail', 'profile_card',
    ];

    public function __construct(
        private readonly BlockDefinitionRegistry $definitions,
        private readonly StructuredBlockRegistry $legacyRegistry,
    ) {}

    public function renderDocument(array $document, array $context = []): string
    {
        $html = '';
        $sections = is_array($document['sections'] ?? null) ? $document['sections'] : [];
        $order = is_array($document['order'] ?? null) ? $document['order'] : array_keys($sections);

        foreach ($order as $sectionId) {
            if (! isset($sections[$sectionId]) || ! is_array($sections[$sectionId])) {
                continue;
            }

            $node = $this->node((string) $sectionId, $sections[$sectionId], 'section');
            if ($node['block']->disabled && ! EditorAttributes::active()) {
                continue;
            }

            $html .= $this->renderPreparedNode($node, $context, false, 'section');
        }

        return $html;
    }

    public function renderPreparedNode(array $node, array $context = [], bool $embedded = false, string $kind = 'block'): string
    {
        /** @var Component $component */
        $component = $node['block'];
        $type = preg_replace('/[^a-z0-9_\-]/', '', $component->type) ?: 'rich_text';
        if ($type === 'text') {
            $type = 'rich_text';
        }
        $definition = $this->definitions->definition($type, $kind === 'section' ? 'sections' : 'blocks');
        $content = $component->content_json;
        $settings = array_merge($this->legacyRegistry->defaults(), $component->settings->all());
        $siteSettings = is_array($context['siteSettings'] ?? null)
            ? $context['siteSettings']
            : (is_array($context['settings'] ?? null) ? $context['settings'] : []);
        if ($type === 'profile_card') {
            $settings = array_merge($settings, $siteSettings);
        }
        $children = $node['children'];
        $templateType = in_array($type, ['site_header', 'site_footer'], true);
        $rawDesign = in_array($type, self::RAW_DESIGN_TYPES, true);
        $view = $this->viewName($type, $kind);
        $body = $view
            ? (string) view($view, array_merge($context, compact('node', 'component', 'content', 'settings', 'siteSettings', 'children', 'definition', 'type'), [
                'section' => $component,
                'block' => $component,
                'embedded' => $embedded,
            ]))->render()
            : '<!-- Page builder view not found: '.e($type).' -->';

        if ($templateType) {
            if (! EditorAttributes::active()) {
                return $body;
            }

            return '<div class="pb-editor-template-shell" '.$component->editorAttributes($kind).'>'
                .$this->editorControls($component, $content, $definition, $settings)
                .$body
                .'</div>';
        }

        if ($rawDesign || $embedded) {
            $html = '<div class="pb-design-block pb-design-'.$type.($embedded ? ' pb-embedded-block' : '').'" '.$component->editorAttributes($kind).'>';
            if (EditorAttributes::active()) {
                $html .= $this->editorControls($component, $content, $definition, $settings);
            }
            $html .= $body;
            if (! in_array($type, ['home_content_grid', 'contact_grid'], true) && $children->isNotEmpty()) {
                foreach ($children as $child) {
                    $html .= $this->renderPreparedNode($child, $context, true);
                }
            }

            return $html.'</div>';
        }

        if ((bool) ($definition['system'] ?? false)) {
            return '';
        }

        $decor = $this->decor($settings);
        $html = '<section class="'.$decor['classes'].'" style="'.$decor['style'].'" '.$component->editorAttributes($kind).'>';
        $html .= '<div class="container"><div class="pb-public-box pb-type-'.$type.'">';
        if (EditorAttributes::active()) {
            $html .= $this->editorControls($component, $content, $definition, $settings);
        }
        $html .= $body;
        if (! in_array($type, ['columns', 'group'], true) && $children->isNotEmpty()) {
            $html .= '<div class="pb-nested">';
            foreach ($children as $child) {
                $html .= $this->renderPreparedNode($child, $context, false);
            }
            $html .= '</div>';
        }

        return $html.'</div></div></section>';
    }

    private function node(string $id, array $raw, string $kind = 'block'): array
    {
        $raw['id'] = $raw['id'] ?? $id;
        $raw['block_uuid'] = $raw['block_uuid'] ?? $id;
        $component = new Component($raw);
        $blocks = is_array($raw['blocks'] ?? null) ? $raw['blocks'] : [];
        $order = is_array($raw['order'] ?? null) ? $raw['order'] : array_keys($blocks);
        $children = collect();

        foreach ($order as $childId) {
            if (isset($blocks[$childId]) && is_array($blocks[$childId])) {
                $children->push($this->node((string) $childId, $blocks[$childId]));
            }
        }

        return [
            'id' => $id,
            'kind' => $kind,
            'raw' => $raw,
            'block' => $component,
            'children' => $children,
        ];
    }

    private function viewName(string $type, string $kind): ?string
    {
        $candidates = $kind === 'section'
            ? ['pagebuilder.sections.'.$type, 'pagebuilder.blocks.'.$type]
            : ['pagebuilder.blocks.'.$type, 'pagebuilder.sections.'.$type];

        foreach ($candidates as $candidate) {
            if (View::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function decor(array $settings): array
    {
        $clean = fn ($value) => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $value);
        [$background, $text, $accent] = $this->legacyRegistry->theme((string) ($settings['theme'] ?? 'surface'));
        $radius = max(0, min(32, (int) ($settings['radius'] ?? 24)));
        $paddingY = match ($settings['spacing'] ?? 'large') {
            'small' => 24,
            'medium' => 40,
            default => 56,
        };
        $classes = 'pb-public-section pb-layout-'.$clean($settings['layout'] ?? 'card')
            .' pb-align-'.$clean($settings['align'] ?? 'left')
            .' pb-shadow-'.$clean($settings['shadow'] ?? 'soft')
            .' pb-anim-'.$clean($settings['animation'] ?? 'fade-up')
            .' pb-width-'.$clean($settings['max_width'] ?? 'full')
            .' pb-theme-'.$clean($settings['theme'] ?? 'surface');
        $style = '--pb-bg:'.$background.';--pb-text:'.$text.';--pb-accent:'.$accent.';--pb-radius:'.$radius.'px;--pb-py:'.$paddingY.'px;';

        return compact('classes', 'style');
    }

    private function editorControls(Component $component, array $content, array $definition, array $settings): string
    {
        return (string) view('public.partials.block-editor-controls', [
            'block' => $component,
            'content' => $content,
            'definition' => $definition,
            'settings' => $settings,
        ])->render();
    }
}
