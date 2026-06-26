<?php

namespace App\AlturaPageBuilder\Rendering;

use App\AlturaPageBuilder\Catalog\AlturaComponentCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

final class VisualDocumentRenderer
{
    private const DESIGN_TYPES = [
        'home_hero', 'home_content_grid', 'home_journal', 'contact_grid', 'category_listing',
        'article_listing', 'training_listing', 'advertisement_listing', 'feature_listing',
        'partner_listing', 'page_content', 'article_archive', 'gallery_listing',
        'certificate_lookup', 'contact_info', 'contact_form', 'map_embed', 'article_detail',
        'profile_card', 'site_header', 'site_footer',
    ];

    public function __construct(private readonly AlturaComponentCatalog $catalog) {}

    public function renderDocument(array $document, array $context = []): string
    {
        $html = '';
        foreach (['header', 'main', 'footer'] as $zone) {
            $map = $zone === 'main'
                ? ['sections' => (array) ($document['sections'] ?? []), 'order' => (array) ($document['order'] ?? [])]
                : (array) ($document['layout'][$zone] ?? []);
            foreach ((array) ($map['order'] ?? []) as $id) {
                $raw = $map['sections'][$id] ?? null;
                if (! is_array($raw)) continue;
                $node = $this->prepared((string) $id, $raw);
                if ($node['block']->disabled) continue;
                $html .= $this->renderPreparedNode($node, $context, false, 'section');
            }
        }
        return $html;
    }

    public function renderPreparedNode(array $node, array $context = [], bool $embedded = false, string $kind = 'block'): string
    {
        /** @var VisualComponent $component */
        $component = $node['block'];
        $type = preg_replace('/[^a-z0-9_-]/', '', strtolower($component->type)) ?: 'rich_text';
        $content = $component->settings;
        $settings = $component->settings;
        $siteSettings = is_array($context['settings'] ?? null) ? $context['settings'] : [];
        $children = $node['children'];
        $definition = $this->catalog->component($kind === 'section' ? 'section' : 'block', $type)
            ?: $this->catalog->component('section', $type)
            ?: [];
        $view = $this->viewName($type);
        if (! $view) return '';

        $body = (string) view($view, array_merge($context, compact('node', 'component', 'content', 'settings', 'siteSettings', 'children', 'definition', 'type'), [
            'section' => $component,
            'block' => $component,
            'embedded' => $embedded,
        ]))->render();
        if (in_array($type, self::DESIGN_TYPES, true) || $embedded) return $body;

        $nested = '';
        if ($children->isNotEmpty() && ! in_array($type, ['group', 'columns', 'cards', 'stat_list', 'faq', 'gallery', 'button_group'], true)) {
            foreach ($children as $child) $nested .= $this->renderPreparedNode($child, $context, true);
        }
        return '<section class="pb-public-section pb-type-'.e($type).'"><div class="container"><div class="pb-public-box">'.$body.$nested.'</div></div></section>';
    }

    private function prepared(string $id, array $raw): array
    {
        $component = new VisualComponent($raw);
        $blocks = is_array($raw['blocks'] ?? null) ? $raw['blocks'] : [];
        $order = is_array($raw['order'] ?? null) ? $raw['order'] : array_keys($blocks);
        $children = collect();
        foreach ($order as $childId) if (isset($blocks[$childId]) && is_array($blocks[$childId])) $children->push($this->prepared((string) $childId, $blocks[$childId]));
        return ['id' => $id, 'raw' => $raw, 'block' => $component, 'children' => $children];
    }

    private function viewName(string $type): ?string
    {
        foreach (['pagebuilder.sections.'.$type, 'pagebuilder.blocks.'.$type] as $view) if (View::exists($view)) return $view;
        return null;
    }
}
