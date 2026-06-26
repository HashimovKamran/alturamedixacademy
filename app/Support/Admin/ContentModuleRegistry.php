<?php

namespace App\Support\Admin;

use App\Models\Advertisement;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Block;
use App\Models\Certificate;
use App\Models\Feature;
use App\Models\GalleryItem;
use App\Models\HomeStat;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Slider;
use App\Models\Training;

class ContentModuleRegistry
{
    public static function all(): array
    {
        return [
            'menus' => [
                'title' => 'Menyular',
                'model' => Menu::class,
                'folder' => 'menus',
                'order' => ['sort_order', 'asc'],
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'url' => ['label' => 'URL', 'type' => 'text', 'default' => '#'],
                    'target' => ['label' => 'Açılma yeri', 'type' => 'select', 'options' => ['_self' => 'Eyni pəncərədə', '_blank' => 'Yeni pəncərədə']],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['title', 'url', 'target', 'sort_order', 'is_active'],
            ],
            'pages' => [
                'title' => 'Statik səhifələr',
                'model' => Page::class,
                'folder' => 'pages',
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'page_key' => ['label' => 'Səhifə açarı', 'type' => 'text', 'required' => true],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'subtitle' => ['label' => 'Alt başlıq', 'type' => 'text'],
                    'body' => ['label' => 'Mətn', 'type' => 'textarea'],
                    'image_path' => ['label' => 'Şəkil', 'type' => 'file'],
                    'button_text' => ['label' => 'Düymə mətni', 'type' => 'text'],
                    'button_url' => ['label' => 'Düymə linki', 'type' => 'text'],
                    'meta_title' => ['label' => 'SEO title', 'type' => 'text'],
                    'meta_description' => ['label' => 'Meta description', 'type' => 'textarea'],
                    'meta_image' => ['label' => 'Social paylaşım şəkli', 'type' => 'file'],
                    'robots' => ['label' => 'Axtarış indekslənməsi', 'type' => 'select', 'options' => ['index,follow' => 'Index, follow', 'noindex,follow' => 'Noindex, follow', 'noindex,nofollow' => 'Noindex, nofollow']],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['page_key', 'title', 'image_path', 'is_active'],
            ],
            'sliders' => [
                'title' => 'Sliderlər',
                'model' => Slider::class,
                'folder' => 'sliders',
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'subtitle' => ['label' => 'Alt başlıq', 'type' => 'text'],
                    'description' => ['label' => 'Açıqlama', 'type' => 'textarea'],
                    'image_path' => ['label' => 'Şəkil', 'type' => 'file'],
                    'button_1_text' => ['label' => '1-ci düymə mətni', 'type' => 'text'],
                    'button_1_url' => ['label' => '1-ci düymə linki', 'type' => 'text'],
                    'button_2_text' => ['label' => '2-ci düymə mətni', 'type' => 'text'],
                    'button_2_url' => ['label' => '2-ci düymə linki', 'type' => 'text'],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['title', 'image_path', 'sort_order', 'is_active'],
            ],
            'stats' => [
                'title' => 'Ana səhifə statistikaları',
                'model' => HomeStat::class,
                'folder' => 'stats',
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'icon_class' => ['label' => 'Icon class', 'type' => 'text'],
                    'number_text' => ['label' => 'Rəqəm', 'type' => 'text', 'required' => true],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['icon_class', 'number_text', 'title', 'sort_order', 'is_active'],
            ],
            'categories' => [
                'title' => 'Akademik kateqoriyalar',
                'model' => ArticleCategory::class,
                'folder' => 'categories',
                'order' => ['sort_order', 'asc'],
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'slug' => ['label' => 'Slug', 'type' => 'slug'],
                    'icon_class' => ['label' => 'Vizual', 'type' => 'medical_icon'],
                    'image_path' => ['label' => 'SVG şəkil', 'type' => 'svg_file'],
                    'is_featured' => ['label' => 'Əsas səhifədə göstər', 'type' => 'checkbox'],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['title', 'slug', 'visual', 'is_featured', 'sort_order', 'is_active'],
            ],
            'articles' => [
                'title' => 'Məqalələr',
                'model' => Article::class,
                'folder' => 'articles',
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'category_id' => ['label' => 'Kateqoriya', 'type' => 'category'],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'slug' => ['label' => 'Slug', 'type' => 'slug'],
                    'excerpt' => ['label' => 'Qısa açıqlama', 'type' => 'textarea'],
                    'body' => ['label' => 'Məqalə mətni', 'type' => 'textarea'],
                    'cover_image' => ['label' => 'Şəkil', 'type' => 'file'],
                    'author_name' => ['label' => 'Müəllif', 'type' => 'text'],
                    'meta_title' => ['label' => 'SEO title', 'type' => 'text'],
                    'meta_description' => ['label' => 'Meta description', 'type' => 'textarea'],
                    'meta_image' => ['label' => 'Social paylaşım şəkli', 'type' => 'file'],
                    'robots' => ['label' => 'Axtarış indekslənməsi', 'type' => 'select', 'options' => ['index,follow' => 'Index, follow', 'noindex,follow' => 'Noindex, follow', 'noindex,nofollow' => 'Noindex, nofollow']],
                    'published_at' => ['label' => 'Tarix', 'type' => 'datetime'],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['title', 'slug', 'cover_image', 'published_at', 'is_active'],
            ],
            'trainings' => [
                'title' => 'Təlimlər',
                'model' => Training::class,
                'folder' => 'trainings',
                'order' => ['sort_order', 'asc'],
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'location' => ['label' => 'Məkan', 'type' => 'text'],
                    'training_date' => ['label' => 'Tarix', 'type' => 'date'],
                    'register_url' => ['label' => 'Qeydiyyat linki', 'type' => 'text'],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['title', 'location', 'training_date', 'sort_order', 'is_active'],
            ],
            'features' => [
                'title' => 'Akademik imkanlar',
                'model' => Feature::class,
                'folder' => 'features',
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'description' => ['label' => 'Açıqlama', 'type' => 'textarea'],
                    'icon_class' => ['label' => 'Icon class', 'type' => 'text'],
                    'url' => ['label' => 'URL', 'type' => 'text'],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['title', 'icon_class', 'sort_order', 'is_active'],
            ],
            'blocks' => [
                'title' => 'Blok mətnləri',
                'model' => Block::class,
                'folder' => 'blocks',
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'block_key' => ['label' => 'Blok açarı', 'type' => 'select', 'options' => ['journal' => 'journal', 'mobile_app' => 'mobile_app', 'support' => 'support']],
                    'title' => ['label' => 'Başlıq', 'type' => 'text'],
                    'subtitle' => ['label' => 'Alt başlıq', 'type' => 'text'],
                    'body' => ['label' => 'Mətn', 'type' => 'textarea'],
                    'image_path' => ['label' => 'Şəkil', 'type' => 'file'],
                    'button_text' => ['label' => 'Düymə mətni', 'type' => 'text'],
                    'button_url' => ['label' => 'Düymə linki', 'type' => 'text'],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['block_key', 'title', 'image_path', 'is_active'],
            ],
            'partners' => [
                'title' => 'Tərəfdaşlar',
                'model' => Partner::class,
                'folder' => 'partners',
                'order' => ['sort_order', 'asc'],
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'title' => ['label' => 'Ad', 'type' => 'text', 'required' => true],
                    'logo_path' => ['label' => 'Logo', 'type' => 'file'],
                    'url' => ['label' => 'URL', 'type' => 'text'],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['title', 'logo_path', 'url', 'sort_order', 'is_active'],
            ],
            'ads' => [
                'title' => 'Reklamlar',
                'model' => Advertisement::class,
                'folder' => 'ads',
                'order' => ['sort_order', 'asc'],
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'position_key' => ['label' => 'Mövqe', 'type' => 'select', 'options' => ['sidebar' => 'Sidebar', 'bottom' => 'Aşağı']],
                    'title' => ['label' => 'Mətn', 'type' => 'text'],
                    'image_path' => ['label' => 'Şəkil', 'type' => 'file'],
                    'url' => ['label' => 'URL', 'type' => 'text'],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['position_key', 'title', 'image_path', 'sort_order', 'is_active'],
            ],
            'gallery' => [
                'title' => 'Qalereya',
                'model' => GalleryItem::class,
                'folder' => 'gallery',
                'order' => ['sort_order', 'asc'],
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'title' => ['label' => 'Başlıq', 'type' => 'text', 'required' => true],
                    'description' => ['label' => 'Açıqlama', 'type' => 'textarea'],
                    'image_path' => ['label' => 'Şəkil', 'type' => 'file'],
                    'sort_order' => ['label' => 'Sıra', 'type' => 'number', 'default' => 0],
                    'is_active' => ['label' => 'Status', 'type' => 'checkbox', 'default' => true],
                ],
                'columns' => ['title', 'image_path', 'sort_order', 'is_active'],
            ],
            'certificates_manage' => [
                'title' => 'Sertifikat bazası',
                'model' => Certificate::class,
                'folder' => 'certificates',
                'fields' => [
                    'lang_code' => ['label' => 'Dil', 'type' => 'language'],
                    'cert_no' => ['label' => 'Sənəd nömrəsi', 'type' => 'text'],
                    'full_name' => ['label' => 'Ad soyad', 'type' => 'text', 'required' => true],
                    'course_title' => ['label' => 'Təlim / sənəd başlığı', 'type' => 'text', 'required' => true],
                    'certificate_type' => ['label' => 'Tip', 'type' => 'select', 'options' => ['certificate' => 'Sertifikat', 'diploma' => 'Diplom', 'attendance' => 'İştirak sənədi']],
                    'issue_date' => ['label' => 'Verilmə tarixi', 'type' => 'date'],
                    'validity_period' => ['label' => 'Sənədin etibarlılıq müddəti', 'type' => 'certificate_validity'],
                    'file_path' => ['label' => 'Fayl', 'type' => 'file'],
                    'qr_x' => ['label' => 'QR yerləşimi', 'type' => 'qr_position'],
                    'is_active' => ['label' => 'Aktivlik', 'type' => 'checkbox', 'default' => true, 'passive_label' => 'Deaktiv'],
                    'revocation' => ['label' => 'Ləğv etmə', 'type' => 'certificate_revocation'],
                    'note' => ['label' => 'Qeyd', 'type' => 'textarea'],
                ],
                'columns' => ['cert_no', 'full_name', 'course_title', 'status', 'is_active'],
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
