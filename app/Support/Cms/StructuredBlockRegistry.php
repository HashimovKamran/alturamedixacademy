<?php

namespace App\Support\Cms;

use Illuminate\Support\Arr;

class StructuredBlockRegistry extends PageBlockRegistry
{
    public const SCHEMA_VERSION = 1;

    public function definitions(): array
    {
        return [
            'home_journal' => $this->build('Ana səhifə jurnal bloku', 'Dinamik', []),
            'hero' => $this->build('Hero / böyük giriş', 'Məzmun', [
                $this->field('eyebrow', 'Üst başlıq'), $this->field('title', 'Başlıq', 'text', true),
                $this->field('text', 'Mətn', 'textarea'), $this->field('button_text', 'Düymə mətni'),
                $this->field('button_url', 'Düymə linki', 'url'),
            ]),
            'rich_text' => $this->build('Mətn', 'Məzmun', [
                $this->field('title', 'Başlıq'), $this->field('html', 'Mətn', 'richtext', true),
            ]),
            'image_text' => $this->build('Şəkil + mətn', 'Məzmun', [
                $this->field('eyebrow', 'Üst başlıq'), $this->field('title', 'Başlıq'),
                $this->field('html', 'Mətn', 'richtext'), $this->field('button_text', 'Düymə mətni'),
                $this->field('button_url', 'Düymə linki', 'url'),
            ]),
            'cards' => $this->build('Kartlar', 'Siyahılar', [
                $this->field('title', 'Bölmə başlığı'),
                $this->field('items', 'Kartlar', 'repeater', true, ['title', 'text', 'icon', 'url']),
            ]),
            'stat_list' => $this->build('Statistika siyahısı', 'Siyahılar', [
                $this->field('title', 'Bölmə başlığı'),
                $this->field('items', 'Statistikalar', 'repeater', true, ['value', 'title', 'icon']),
            ]),
            'faq' => $this->build('Sual-cavab', 'Siyahılar', [
                $this->field('title', 'Bölmə başlığı'),
                $this->field('items', 'Suallar', 'repeater', true, ['title', 'text']),
            ]),
            'cta' => $this->build('Çağırış bloku', 'Məzmun', [
                $this->field('title', 'Başlıq', 'text', true), $this->field('text', 'Mətn', 'textarea'),
                $this->field('button_text', 'Düymə mətni'), $this->field('button_url', 'Düymə linki', 'url'),
            ]),
            'video_embed' => $this->build('Video', 'Media', [
                $this->field('title', 'Başlıq'), $this->field('url', 'YouTube/Vimeo linki', 'url', true),
                $this->field('caption', 'Açıqlama', 'textarea'),
            ]),
            'gallery' => $this->build('Şəkil qalereyası', 'Media', [
                $this->field('title', 'Başlıq'),
                $this->field('items', 'Şəkillər', 'repeater', true, ['image', 'title', 'text']),
            ]),
            'button_group' => $this->build('Düymə qrupu', 'Məzmun', [
                $this->field('items', 'Düymələr', 'repeater', true, ['title', 'url', 'style']),
            ]),
            'spacer' => $this->build('Boşluq', 'Layout', []),
            'divider' => $this->build('Ayırıcı xətt', 'Layout', []),
            'group' => $this->build('Blok qrupu', 'Layout', [], ['default'], ['*']),
            'columns' => $this->build('Sütunlar', 'Layout', [
                $this->field('columns', 'Sütun sayı', 'select', true, [], ['2' => '2 sütun', '3' => '3 sütun']),
            ], ['column_1', 'column_2', 'column_3'], ['*']),
            'article_listing' => $this->build('Məqalə siyahısı', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('limit', 'Maksimum say', 'number'),
            ]),
            'training_listing' => $this->build('Təlim siyahısı', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('limit', 'Maksimum say', 'number'),
            ]),
            'contact_form' => $this->build('Əlaqə formu', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('text', 'Açıqlama', 'textarea'),
            ]),
            'map_embed' => $this->build('Xəritə', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('embed_url', 'Embed linki', 'url', true),
            ]),
            'home_hero' => $this->build('Ana səhifə hero/slider', 'Dinamik', [
                $this->field('show_stats', 'Statistikalar göstərilsin', 'checkbox', false, [], [], true),
                $this->field('autoplay_ms', 'Slayd intervalı (ms)', 'number', false, [], [], 6200),
            ]),
            'home_content_grid' => $this->build('Ana səhifə kontent grid-i', 'Layout', [], ['main','sidebar'], ['*']),
            'contact_grid' => $this->build('Əlaqə səhifəsi grid-i', 'Layout', [], ['info','form'], ['contact_info','contact_form']),
            'category_listing' => $this->build('Kateqoriya siyahısı', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('limit', 'Limit', 'number', false, [], [], 6),
            ]),
            'feature_listing' => $this->build('İmkanlar siyahısı', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('show_support', 'Dəstək bloku göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'partner_listing' => $this->build('Tərəfdaşlar', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('subtitle', 'Alt başlıq', 'textarea'),
                $this->field('limit', 'Limit', 'number', false, [], [], 20),
            ]),
            'advertisement_listing' => $this->build('Reklam siyahısı', 'Dinamik', [
                $this->field('position', 'Mövqe', 'select', false, [], ['sidebar' => 'Sidebar', 'bottom' => 'Alt hissə'], 'bottom'),
                $this->field('limit', 'Limit', 'number', false, [], [], 4),
            ]),
            'page_content' => $this->build('Səhifə məzmunu', 'Dinamik', [
                $this->field('title', 'Başlıq override'), $this->field('subtitle', 'Alt başlıq override', 'textarea'),
                $this->field('html', 'Mətn override', 'richtext'), $this->field('show_image', 'Şəkil göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'article_archive' => $this->build('Məqalə arxivi', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('intro', 'Giriş mətni', 'textarea'),
                $this->field('limit', 'Kateqoriya üzrə limit', 'number', false, [], [], 12),
            ]),
            'gallery_listing' => $this->build('Qalereya məlumatları', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('intro', 'Giriş mətni', 'textarea'),
                $this->field('columns', 'Sütun sayı', 'select', false, [], ['2' => '2', '3' => '3', '4' => '4'], '4'),
            ]),
            'certificate_lookup' => $this->build('Sertifikat yoxlama', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('subtitle', 'Alt başlıq', 'textarea'),
                $this->field('show_points', 'Məlumat nişanları göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'contact_info' => $this->build('Əlaqə məlumatları', 'Dinamik', [
                $this->field('title', 'Başlıq'), $this->field('html', 'Mətn', 'richtext'),
            ]),
            'article_detail' => $this->build('Məqalə detalı', 'Dinamik', [
                $this->field('show_cover', 'Cover göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_meta', 'Metadata göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_category', 'Kateqoriya göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'profile_card' => $this->build('Profil kartı', 'Dinamik', [
                $this->field('title', 'Başlıq override'), $this->field('show_email', 'E-mail göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_logout', 'Çıxış göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'site_header' => $this->system('Sayt header-i', [
                $this->field('show_brand', 'Logo və brend', 'checkbox', false, [], [], true),
                $this->field('show_languages', 'Dil seçimi', 'checkbox', false, [], [], true),
                $this->field('show_social', 'Sosial linklər', 'checkbox', false, [], [], true),
                $this->field('show_navigation', 'Əsas menyu', 'checkbox', false, [], [], true),
                $this->field('show_auth', 'Login/qeydiyyat', 'checkbox', false, [], [], true),
                $this->field('show_search', 'Axtarış', 'checkbox', false, [], [], true),
            ]),
            'site_footer' => $this->system('Sayt footer-i', [
                $this->field('show_about', 'Brend məlumatı', 'checkbox', false, [], [], true),
                $this->field('show_links', 'Sürətli keçidlər', 'checkbox', false, [], [], true),
                $this->field('show_contact', 'Əlaqə məlumatı', 'checkbox', false, [], [], true),
                $this->field('show_newsletter', 'Bülleten', 'checkbox', false, [], [], true),
                $this->field('copyright_text', 'Copyright mətni'),
            ]),
            'native_home' => $this->native('Ana səhifənin əsas modulları', [
                $this->field('show_hero', 'Hero göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_articles', 'Məqalə bölməsi göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_trainings', 'Təlim paneli göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_ads', 'Reklamlar göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_features', 'İmkanlar bölməsi göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_partners', 'Tərəfdaşlar göstərilsin', 'checkbox', false, [], [], true),
                $this->field('latest_title', 'Son məqalələr başlığı'),
                $this->field('features_title', 'İmkanlar başlığı'),
                $this->field('partners_title', 'Tərəfdaşlar başlığı'),
                $this->field('article_limit', 'Məqalə limiti', 'number', false, [], [], 4),
                $this->field('training_limit', 'Təlim limiti', 'number', false, [], [], 3),
            ]),
            'native_page' => $this->native('Standart səhifə məzmunu', [
                $this->field('title', 'Başlıq override'),
                $this->field('subtitle', 'Alt başlıq override', 'textarea'),
                $this->field('html', 'Mətn override', 'richtext'),
                $this->field('show_image', 'Əsas şəkil göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_contact_form', 'Əlaqə formu göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_map', 'Xəritə göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'native_articles' => $this->native('Akademik yazılar modulu', [
                $this->field('title', 'Başlıq override'), $this->field('intro', 'Giriş mətni override', 'textarea'),
                $this->field('show_categories', 'Kateqoriyalar göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_articles', 'Məqalələr göstərilsin', 'checkbox', false, [], [], true),
                $this->field('articles_per_category', 'Kateqoriya üzrə limit', 'number', false, [], [], 12),
            ]),
            'native_article' => $this->native('Məqalə detal template-i', [
                $this->field('show_cover', 'Cover şəkli göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_meta', 'Tarix və metadata göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_category', 'Kateqoriya göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'native_trainings' => $this->native('Təlimlər modulu', [
                $this->field('title', 'Başlıq override'), $this->field('subtitle', 'Alt başlıq override', 'textarea'),
                $this->field('limit', 'Təlim limiti', 'number', false, [], [], 24),
                $this->field('show_register', 'Qeydiyyat düyməsi göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'native_gallery' => $this->native('Qalereya modulu', [
                $this->field('title', 'Başlıq override'), $this->field('intro', 'Giriş mətni override', 'textarea'),
                $this->field('columns', 'Desktop sütun sayı', 'select', false, [], ['2' => '2 sütun', '3' => '3 sütun', '4' => '4 sütun'], '4'),
            ]),
            'native_certificates' => $this->native('Sertifikat yoxlama modulu', [
                $this->field('title', 'Başlıq override'), $this->field('subtitle', 'Alt başlıq override', 'textarea'),
                $this->field('show_points', 'Üst məlumat nişanları göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'native_profile' => $this->native('İstifadəçi profil template-i', [
                $this->field('title', 'Başlıq override'),
                $this->field('show_email', 'E-mail göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_logout', 'Çıxış düyməsi göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'native_header' => $this->native('Sayt başlığı və menyu', [
                $this->field('show_brand', 'Logo və brend göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_languages', 'Dil seçimi göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_social', 'Sosial linklər göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_navigation', 'Əsas menyu göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_auth', 'Login/qeydiyyat göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_search', 'Axtarış göstərilsin', 'checkbox', false, [], [], true),
            ]),
            'native_footer' => $this->native('Sayt altlığı', [
                $this->field('show_about', 'Brend məlumatı göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_links', 'Sürətli keçidlər göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_contact', 'Əlaqə məlumatı göstərilsin', 'checkbox', false, [], [], true),
                $this->field('show_newsletter', 'Bülleten formu göstərilsin', 'checkbox', false, [], [], true),
                $this->field('copyright_text', 'Copyright mətni override'),
            ]),
        ];
    }

    public function types(bool $includeSystem = true): array
    {
        return collect($this->definitions())
            ->when(! $includeSystem, fn ($items) => $items->reject(fn ($definition) => $definition['system']))
            ->mapWithKeys(fn ($definition, $key) => [$key => $definition['label']])->all();
    }

    public function definition(string $type): array
    {
        return $this->definitions()[$type] ?? $this->definitions()['rich_text'];
    }

    public function schemas(): array
    {
        return collect($this->definitions())->map(fn ($definition) => Arr::only($definition, [
            'label', 'category', 'fields', 'slots', 'allowed_children', 'system',
        ]))->all();
    }

    public function normalizeContent(string $type, array $input, SafeHtml $html): array
    {
        $output = [];
        foreach ($this->definition($type)['fields'] as $field) {
            $key = $field['key'];
            $value = $input[$key] ?? null;
            if ($field['type'] === 'repeater') {
                $items = is_string($value) ? json_decode($value, true) : $value;
                $output[$key] = collect(is_array($items) ? $items : [])->take(50)->map(function ($item) use ($field): array {
                    $item = is_array($item) ? $item : [];
                    return collect($field['columns'])->mapWithKeys(function ($column) use ($item): array {
                        $value = trim((string) ($item[$column] ?? ''));
                        if ($column === 'url') $value = SafeUrl::clean($value);
                        if ($column === 'image') $value = SafeUrl::clean($value, '');
                        if ($column === 'text') $value = strip_tags($value);
                        return [$column => mb_substr($value, 0, $column === 'text' ? 2000 : 700)];
                    })->all();
                })->filter(fn ($item) => collect($item)->contains(fn ($value) => $value !== ''))->values()->all();
            } elseif ($field['type'] === 'checkbox') {
                $output[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } elseif ($field['type'] === 'number') {
                $output[$key] = max(0, min(100, (int) $value));
            } elseif ($field['type'] === 'richtext') {
                $output[$key] = $html->clean((string) $value);
            } elseif ($field['type'] === 'url') {
                $output[$key] = SafeUrl::clean($value);
            } elseif ($field['type'] === 'select') {
                $output[$key] = $this->choice($value, array_keys($field['options']), (string) array_key_first($field['options']));
            } else {
                $output[$key] = mb_substr(trim(strip_tags((string) $value)), 0, $field['type'] === 'textarea' ? 5000 : 700);
            }
        }
        return $output;
    }

    public function content(string $type, ?string $body, mixed $json, SafeHtml $html): array
    {
        $decoded = is_string($json) ? json_decode($json, true) : $json;
        if (is_array($decoded) && $decoded !== []) return $this->normalizeContent($type, $decoded, $html);
        if (in_array($type, ['cards', 'faq', 'stat_list'], true)) {
            $items = collect(preg_split('/\r\n|\r|\n/', (string) $body))->filter()->map(function ($line): array {
                $parts = array_map('trim', explode('|', (string) $line, 4));
                return ['title' => $parts[0] ?? '', 'text' => $parts[1] ?? '', 'value' => $parts[0] ?? '', 'icon' => $parts[2] ?? '', 'url' => $parts[3] ?? ''];
            })->values()->all();
            return $this->normalizeContent($type, ['items' => $items], $html);
        }
        return $this->normalizeContent($type, ['html' => $body, 'text' => $body], $html);
    }

    public function canParent(string $parentType, string $childType, string $slot): bool
    {
        if ($parentType === 'contact_grid' && $childType === 'map_embed' && $slot === 'map') return true;
        $definition = $this->definition($parentType);
        return in_array($slot, $definition['slots'], true)
            && (in_array('*', $definition['allowed_children'], true) || in_array($childType, $definition['allowed_children'], true));
    }

    public function isSystem(string $type): bool
    {
        return (bool) ($this->definition($type)['system'] ?? false);
    }

    private function build(string $label, string $category, array $fields, array $slots = [], array $allowedChildren = []): array
    {
        return compact('label', 'category', 'fields') + ['slots' => $slots, 'allowed_children' => $allowedChildren, 'system' => false];
    }

    private function native(string $label, array $fields = []): array
    {
        return ['label' => $label, 'category' => 'Sistem', 'fields' => $fields, 'slots' => [], 'allowed_children' => [], 'system' => true];
    }

    private function system(string $label, array $fields = []): array
    {
        return ['label' => $label, 'category' => 'Template', 'fields' => $fields, 'slots' => [], 'allowed_children' => [], 'system' => true];
    }

    private function field(string $key, string $label, string $type = 'text', bool $required = false, array $columns = [], array $options = [], mixed $default = ''): array
    {
        return compact('key', 'label', 'type', 'required', 'columns', 'options', 'default');
    }

    private function choice(mixed $value, array $allowed, string $default): string
    {
        $value = (string) $value;
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
