<?php

namespace App\AlturaPageBuilder\Catalog;

final class AlturaComponentCatalog
{
    /**
     * The catalog is deliberately code-owned. Content authors can only choose
     * components and fields explicitly declared here; they can never select a
     * Blade view, CSS class, PHP callable or arbitrary HTML template.
     */
    public function payload(): array
    {
        return [
            'sections' => $this->sections(),
            'blocks' => $this->blocks(),
            'templates' => [
                'default' => ['label' => 'Standart Alturamedix səhifəsi'],
                'article' => ['label' => 'Məqalə detalı'],
                'profile' => ['label' => 'Profil'],
            ],
            'theme_settings' => [
                [
                    'name' => 'Brend görünüşü',
                    'settings' => [
                        $this->field('colors.primary', 'Əsas rəng', 'color', '#0f3850'),
                        $this->field('colors.accent', 'Vurğu rəngi', 'color', '#df7412'),
                        $this->field('colors.surface', 'Səth rəngi', 'color', '#ffffff'),
                    ],
                ],
            ],
        ];
    }

    public function component(string $kind, string $type): ?array
    {
        return ($kind === 'section' ? $this->sections() : $this->blocks())[$type] ?? null;
    }

    public function defaultDocument(string $pageKey): array
    {
        $node = fn (string $type, array $settings = [], array $blocks = [], array $order = [], string $slot = 'default'): array => [
            'type' => $type,
            '_name' => null,
            'disabled' => false,
            'slot_key' => $slot,
            'settings' => array_merge($this->defaultsFor($type, 'section') ?: $this->defaultsFor($type, 'block'), $settings),
            'blocks' => $blocks,
            'order' => $order,
        ];
        $id = fn (string $seed): string => $seed.'_'.substr(hash('sha256', $pageKey.'|'.$seed), 0, 12);
        $main = ['sections' => [], 'order' => []];

        if ($pageKey === '__header') {
            $key = $id('header');
            $main['sections'][$key] = $node('site_header');
            $main['order'][] = $key;
        } elseif ($pageKey === '__footer') {
            $key = $id('footer');
            $main['sections'][$key] = $node('site_footer');
            $main['order'][] = $key;
        } elseif ($pageKey === 'index') {
            $hero = $id('home_hero');
            $grid = $id('home_content_grid');
            $journal = $id('home_journal');
            $articles = $id('article_listing');
            $trainings = $id('training_listing');
            $features = $id('feature_listing');
            $partners = $id('partner_listing');
            $ads = $id('advertisement_listing');
            $main['sections'][$hero] = $node('home_hero');
            $main['sections'][$grid] = $node('home_content_grid', [], [
                $journal => $node('home_journal', [], [], [], 'main'),
                $articles => $node('article_listing', ['limit' => 12], [], [], 'main'),
                $trainings => $node('training_listing', ['limit' => 6], [], [], 'main'),
                $features => $node('feature_listing', [], [], [], 'main'),
                $partners => $node('partner_listing', ['limit' => 20], [], [], 'main'),
                $ads => $node('advertisement_listing', ['position' => 'sidebar', 'limit' => 4], [], [], 'sidebar'),
            ], [$journal, $articles, $trainings, $features, $partners, $ads]);
            $main['order'] = [$hero, $grid];
        } else {
            $type = match ($pageKey) {
                'contact' => 'contact_grid',
                'certificates' => 'certificate_lookup',
                'gallery' => 'gallery_listing',
                'trainings' => 'training_listing',
                'articles' => 'article_archive',
                'article_detail' => 'article_detail',
                'profile' => 'profile_card',
                default => 'page_content',
            };
            $key = $id($type);
            $blocks = [];
            $order = [];
            if ($type === 'contact_grid') {
                $info = $id('contact_info');
                $form = $id('contact_form');
                $blocks = [
                    $info => $node('contact_info', [], [], [], 'info'),
                    $form => $node('contact_form', [], [], [], 'form'),
                ];
                $order = [$info, $form];
            }
            $main['sections'][$key] = $node($type, [], $blocks, $order);
            $main['order'][] = $key;
        }

        return [
            'schema_version' => 1,
            'layout' => [
                'type' => 'alturamedix',
                'header' => ['sections' => [], 'order' => []],
                'footer' => ['sections' => [], 'order' => []],
            ],
            'sections' => $main['sections'],
            'order' => $main['order'],
        ];
    }

    public function defaultsFor(string $type, string $kind): array
    {
        $component = $this->component($kind, $type);
        if (! $component) return [];
        $defaults = [];
        foreach ($component['fields'] as $key => $field) $defaults[$key] = $field['default'] ?? null;
        return $defaults;
    }

    private function sections(): array
    {
        $content = $this->contentFields();
        $mainOnly = ['main'];
        return [
            'site_header' => $this->componentDef('Alturamedix Header', 'Sistem', $mainOnly, [
                $this->field('show_brand', 'Logo və brend', 'checkbox', true),
                $this->field('show_languages', 'Dil seçimi', 'checkbox', true),
                $this->field('show_social', 'Sosial linklər', 'checkbox', true),
                $this->field('show_navigation', 'Əsas menyu', 'checkbox', true),
                $this->field('show_auth', 'Giriş / qeydiyyat', 'checkbox', true),
                $this->field('show_search', 'Axtarış', 'checkbox', true),
            ], [], [], true),
            'site_footer' => $this->componentDef('Alturamedix Footer', 'Sistem', $mainOnly, [
                $this->field('show_about', 'Brend məlumatı', 'checkbox', true),
                $this->field('show_links', 'Sürətli keçidlər', 'checkbox', true),
                $this->field('show_contact', 'Əlaqə məlumatı', 'checkbox', true),
                $this->field('show_newsletter', 'Bülleten', 'checkbox', true),
                $this->field('copyright_text', 'Copyright mətni', 'text', ''),
            ], [], [], true),
            'home_hero' => $this->componentDef('Ana səhifə hero / slider', 'Ana səhifə', $mainOnly, [
                $this->field('show_stats', 'Statistikaları göstər', 'checkbox', true),
                $this->field('autoplay_ms', 'Slayd intervalı (ms)', 'number', 6200, min: 2500, max: 20000),
            ]),
            'home_content_grid' => $this->componentDef('Ana səhifə məzmun grid-i', 'Ana səhifə', $mainOnly, [], $this->contentBlockTypes(), ['main', 'sidebar']),
            'contact_grid' => $this->componentDef('Əlaqə səhifəsi grid-i', 'Səhifə', $mainOnly, [], ['contact_info', 'contact_form', 'map_embed'], ['info', 'form']),
            'page_content' => $this->componentDef('Standart səhifə məzmunu', 'Səhifə', $mainOnly, [
                $this->field('title', 'Başlıq override', 'text', ''),
                $this->field('subtitle', 'Alt başlıq override', 'textarea', ''),
                $this->field('html', 'Mətn override', 'rich_text', ''),
                $this->field('show_image', 'Əsas şəkli göstər', 'checkbox', true),
            ]),
            'article_archive' => $this->componentDef('Akademik yazılar arxivi', 'Dinamik', $mainOnly, [
                $this->field('title', 'Başlıq', 'text', ''),
                $this->field('intro', 'Giriş mətni', 'textarea', ''),
                $this->field('limit', 'Kateqoriya üzrə limit', 'number', 12, min: 1, max: 48),
            ]),
            'article_detail' => $this->componentDef('Məqalə detalı', 'Dinamik', $mainOnly, [
                $this->field('show_cover', 'Cover şəkli göstər', 'checkbox', true),
                $this->field('show_meta', 'Metadata göstər', 'checkbox', true),
                $this->field('show_category', 'Kateqoriya göstər', 'checkbox', true),
            ]),
            'certificate_lookup' => $this->componentDef('Sertifikat doğrulama', 'Dinamik', $mainOnly, [
                $this->field('title', 'Başlıq', 'text', ''),
                $this->field('subtitle', 'Alt başlıq', 'textarea', ''),
                $this->field('show_points', 'Məlumat nişanlarını göstər', 'checkbox', true),
            ]),
            'gallery_listing' => $this->componentDef('Qalereya', 'Dinamik', $mainOnly, [
                $this->field('title', 'Başlıq', 'text', ''),
                $this->field('intro', 'Giriş mətni', 'textarea', ''),
                $this->field('columns', 'Sütun sayı', 'select', '4', options: ['2', '3', '4']),
            ]),
            'training_listing' => $this->componentDef('Təlimlər siyahısı', 'Dinamik', $mainOnly, [
                $this->field('title', 'Başlıq', 'text', ''),
                $this->field('limit', 'Maksimum say', 'number', 6, min: 1, max: 48),
            ]),
            'profile_card' => $this->componentDef('Profil kartı', 'Dinamik', $mainOnly, [
                $this->field('title', 'Başlıq override', 'text', ''),
                $this->field('show_email', 'E-mail göstər', 'checkbox', true),
                $this->field('show_logout', 'Çıxışı göstər', 'checkbox', true),
            ]),
            'rich_text' => $this->componentDef('Mətn', 'Məzmun', $mainOnly, $content['rich_text']),
            'image_text' => $this->componentDef('Şəkil və mətn', 'Məzmun', $mainOnly, $content['image_text']),
            'cards' => $this->componentDef('Kartlar', 'Məzmun', $mainOnly, $content['cards'], ['card']),
            'stat_list' => $this->componentDef('Statistika', 'Məzmun', $mainOnly, $content['stat_list'], ['stat']),
            'faq' => $this->componentDef('FAQ', 'Məzmun', $mainOnly, $content['faq'], ['faq_item']),
            'cta' => $this->componentDef('Çağırış bloku', 'Məzmun', $mainOnly, $content['cta']),
            'video_embed' => $this->componentDef('Video', 'Media', $mainOnly, $content['video_embed']),
            'gallery' => $this->componentDef('Şəkil qalereyası', 'Media', $mainOnly, $content['gallery'], ['gallery_item']),
            'button_group' => $this->componentDef('Düymə qrupu', 'Məzmun', $mainOnly, [], ['button']),
            'spacer' => $this->componentDef('Boşluq', 'Layout', $mainOnly, [$this->field('height', 'Hündürlük', 'number', 48, min: 8, max: 240)]),
            'divider' => $this->componentDef('Ayırıcı xətt', 'Layout', $mainOnly, []),
            'group' => $this->componentDef('Blok qrupu', 'Layout', $mainOnly, [], $this->contentBlockTypes(), ['default']),
            'columns' => $this->componentDef('Sütunlar', 'Layout', $mainOnly, [$this->field('columns', 'Sütun sayı', 'select', '2', options: ['2', '3'])], $this->contentBlockTypes(), ['column_1', 'column_2', 'column_3']),
            'category_listing' => $this->componentDef('Kateqoriyalar', 'Dinamik', $mainOnly, [$this->field('title', 'Başlıq', 'text', ''), $this->field('limit', 'Limit', 'number', 6, min: 1, max: 24)]),
            'feature_listing' => $this->componentDef('Akademik imkanlar', 'Dinamik', $mainOnly, [$this->field('title', 'Başlıq', 'text', ''), $this->field('show_support', 'Dəstək blokunu göstər', 'checkbox', true)]),
            'partner_listing' => $this->componentDef('Tərəfdaşlar', 'Dinamik', $mainOnly, [$this->field('title', 'Başlıq', 'text', ''), $this->field('subtitle', 'Alt başlıq', 'textarea', ''), $this->field('limit', 'Limit', 'number', 20, min: 1, max: 60)]),
            'advertisement_listing' => $this->componentDef('Reklamlar', 'Dinamik', $mainOnly, [$this->field('position', 'Mövqe', 'select', 'bottom', options: ['sidebar', 'bottom']), $this->field('limit', 'Limit', 'number', 4, min: 1, max: 24)]),
            'contact_info' => $this->componentDef('Əlaqə məlumatı', 'Dinamik', $mainOnly, [$this->field('title', 'Başlıq', 'text', ''), $this->field('html', 'Mətn', 'rich_text', '')]),
            'contact_form' => $this->componentDef('Əlaqə formu', 'Dinamik', $mainOnly, [$this->field('title', 'Başlıq', 'text', ''), $this->field('text', 'Açıqlama', 'textarea', '')]),
            'map_embed' => $this->componentDef('Xəritə', 'Dinamik', $mainOnly, [$this->field('title', 'Başlıq', 'text', ''), $this->field('embed_url', 'Embed linki', 'url', '')]),
            'home_journal' => $this->componentDef('MEDEPICENT Journal', 'Ana səhifə', $mainOnly, []),
        ];
    }

    private function blocks(): array
    {
        $blocks = [];
        foreach (['rich_text', 'image_text', 'cards', 'stat_list', 'faq', 'cta', 'video_embed', 'gallery', 'button_group', 'spacer', 'divider', 'group', 'columns', 'category_listing', 'feature_listing', 'partner_listing', 'advertisement_listing', 'contact_info', 'contact_form', 'map_embed', 'article_archive', 'article_detail', 'training_listing', 'gallery_listing', 'certificate_lookup', 'profile_card', 'page_content', 'home_journal'] as $type) {
            $definition = $this->sections()[$type] ?? null;
            if ($definition) {
                $definition['zones'] = [];
                $blocks[$type] = $definition;
            }
        }
        $blocks['card'] = $this->componentDef('Kart elementi', 'Məzmun', [], [$this->field('title', 'Başlıq', 'text', ''), $this->field('text', 'Mətn', 'textarea', ''), $this->field('icon', 'İkon class', 'icon', ''), $this->field('url', 'Link', 'url', '')]);
        $blocks['stat'] = $this->componentDef('Statistika elementi', 'Məzmun', [], [$this->field('value', 'Dəyər', 'text', ''), $this->field('title', 'Başlıq', 'text', ''), $this->field('icon', 'İkon class', 'icon', '')]);
        $blocks['faq_item'] = $this->componentDef('FAQ elementi', 'Məzmun', [], [$this->field('title', 'Sual', 'text', ''), $this->field('text', 'Cavab', 'textarea', '')]);
        $blocks['gallery_item'] = $this->componentDef('Qalereya şəkli', 'Media', [], [$this->field('asset_id', 'Şəkil', 'asset', null), $this->field('title', 'Başlıq', 'text', ''), $this->field('text', 'Açıqlama', 'textarea', '')]);
        $blocks['button'] = $this->componentDef('Düymə', 'Məzmun', [], [$this->field('title', 'Mətn', 'text', ''), $this->field('url', 'Link', 'url', ''), $this->field('style', 'Stil', 'select', 'primary', options: ['primary', 'light', 'dark'])]);
        return $blocks;
    }

    private function contentFields(): array
    {
        return [
            'rich_text' => [$this->field('title', 'Başlıq', 'text', ''), $this->field('html', 'Mətn', 'rich_text', '')],
            'image_text' => [$this->field('eyebrow', 'Üst başlıq', 'text', ''), $this->field('title', 'Başlıq', 'text', ''), $this->field('html', 'Mətn', 'rich_text', ''), $this->field('image_path', 'Şəkil URL', 'url', ''), $this->field('button_text', 'Düymə mətni', 'text', ''), $this->field('button_url', 'Düymə linki', 'url', '')],
            'cards' => [$this->field('title', 'Bölmə başlığı', 'text', '')],
            'stat_list' => [$this->field('title', 'Bölmə başlığı', 'text', '')],
            'faq' => [$this->field('title', 'Bölmə başlığı', 'text', '')],
            'cta' => [$this->field('title', 'Başlıq', 'text', ''), $this->field('text', 'Mətn', 'textarea', ''), $this->field('button_text', 'Düymə mətni', 'text', ''), $this->field('button_url', 'Düymə linki', 'url', '')],
            'video_embed' => [$this->field('title', 'Başlıq', 'text', ''), $this->field('url', 'YouTube/Vimeo linki', 'video_url', ''), $this->field('caption', 'Açıqlama', 'textarea', '')],
            'gallery' => [$this->field('title', 'Başlıq', 'text', '')],
        ];
    }

    private function contentBlockTypes(): array
    {
        return ['rich_text', 'image_text', 'cards', 'stat_list', 'faq', 'cta', 'video_embed', 'gallery', 'button_group', 'spacer', 'divider', 'group', 'columns', 'article_archive', 'article_detail', 'training_listing', 'gallery_listing', 'certificate_lookup', 'profile_card', 'page_content', 'category_listing', 'feature_listing', 'partner_listing', 'advertisement_listing', 'contact_info', 'contact_form', 'map_embed', 'home_journal'];
    }

    private function componentDef(string $label, string $category, array $zones, array $fields, array $blocks = [], array $slots = [], bool $system = false): array
    {
        return ['label' => $label, 'category' => $category, 'zones' => $zones, 'fields' => collect($fields)->keyBy('key')->all(), 'blocks' => $blocks, 'slots' => $slots, 'system' => $system];
    }

    private function field(string $key, string $label, string $type, mixed $default = null, array $options = [], ?int $min = null, ?int $max = null): array
    {
        return array_filter(compact('key', 'label', 'type', 'default', 'options', 'min', 'max'), fn ($value, $name) => $name === 'default' || $value !== null && $value !== [], ARRAY_FILTER_USE_BOTH);
    }
}
