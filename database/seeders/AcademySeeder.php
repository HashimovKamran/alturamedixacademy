<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AcademySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('aa_languages')->upsert([
            ['code' => 'az', 'title' => 'Azərbaycan dili', 'native_name' => 'Azərbaycan dili', 'locale' => 'az_AZ', 'is_default' => true, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ru', 'title' => 'Русский язык', 'native_name' => 'Русский', 'locale' => 'ru_RU', 'is_default' => false, 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'en', 'title' => 'English', 'native_name' => 'English', 'locale' => 'en_US', 'is_default' => false, 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'tr', 'title' => 'Türkçe', 'native_name' => 'Türkçe', 'locale' => 'tr_TR', 'is_default' => false, 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ], ['code'], ['title', 'native_name', 'locale', 'is_default', 'is_active', 'sort_order', 'updated_at']);

        DB::table('aa_admin_users')->upsert([
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'full_name' => 'Admin',
                'password_hash' => Hash::make('Admin123456'),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['username'], ['email', 'full_name', 'password_hash', 'is_active', 'updated_at']);

        foreach (['az', 'ru', 'en', 'tr'] as $lang) {
            $this->seedSettings($lang, $now);
            $this->seedMenus($lang, $now);
            $this->seedPages($lang, $now);
            $this->seedHome($lang, $now);
        }
    }

    private function seedSettings(string $lang, mixed $now): void
    {
        $settings = [
            'site_name' => 'ALTURAMEDIX ACADEMY',
            'site_slogan' => 'Academy of Emergency & Critical Care',
            'site_description' => 'Təcili yardım və kritik baxım üzrə akademik bilik platforması.',
            'logo_image' => '',
            'section_academic' => 'Akademik yazılar',
            'section_latest' => 'Son dərc olunanlar',
            'section_trainings' => 'Təlim və konfranslar',
            'section_trainings_all' => 'Bütün təlim və konfranslara bax',
            'section_features' => 'Akademik imkanlarımız',
            'section_partners' => 'Tərəfdaşlarımız',
            'section_partners_subtitle' => 'Akademik inkişaf yolunda əməkdaşlıq etdiyimiz qurumlar',
            'all_view' => 'Hamısına bax',
            'btn_register' => 'Qeydiyyat',
            'btn_login' => 'Daxil ol',
            'auth_profile_title' => 'Profil',
            'auth_logout' => 'Çıxış',
            'auth_login_title' => 'Daxil ol',
            'auth_login_subtitle' => 'Hesabınıza daxil olaraq yenilikləri izləyin.',
            'auth_register_title' => 'Qeydiyyatdan keç',
            'auth_register_subtitle' => 'Akademik yeniliklərdən xəbərdar olmaq üçün hesab yaradın.',
            'auth_email' => 'Email hesabı',
            'auth_password' => 'Şifrə',
            'auth_login_button' => 'Daxil ol',
            'auth_register_button' => 'Qeydiyyatdan keç',
            'auth_google_button' => 'Google hesabı ilə davam et',
            'ad_label' => 'Reklam',
            'ad_default' => 'Sizin reklamınız burada ola bilər',
            'footer_about' => 'Elmi bilik, klinik təcrübə və peşəkar inkişaf üçün müasir akademik platforma.',
            'footer_links' => 'Sürətli keçidlər',
            'footer_contact' => 'Əlaqə',
            'footer_newsletter' => 'Bülleten',
            'newsletter_text' => 'Yeniliklər və elmi məqalələr haqqında məlumat almaq üçün abunə olun.',
            'newsletter_placeholder' => 'E-mail ünvanınız',
            'contact_phone' => '+994 00 000 00 00',
            'contact_email' => 'info@example.com',
            'contact_address' => 'Bakı, Azərbaycan',
            'social_instagram' => '#',
            'social_linkedin' => '#',
            'social_youtube' => '#',
            'home_map_enabled' => '0',
            'home_map_title' => 'Ünvan xəritəsi',
            'home_map_subtitle' => 'Bizi xəritədə tapın',
            'home_map_embed' => '',
            'home_map_height' => '360',
        ];

        $rows = [];
        foreach ($settings as $key => $value) {
            $rows[] = ['lang_code' => $lang, 'setting_key' => $key, 'setting_value' => $value, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('aa_settings')->upsert($rows, ['lang_code', 'setting_key'], ['setting_value', 'updated_at']);
    }

    private function seedMenus(string $lang, mixed $now): void
    {
        $titles = [
            'az' => ['Ana səhifə', 'Haqqımızda', 'Akademik yazılar', 'Sertifikatlar', 'Qalereya', 'Əlaqə'],
            'ru' => ['Главная', 'О нас', 'Статьи', 'Сертификаты', 'Галерея', 'Контакты'],
            'en' => ['Home', 'About', 'Articles', 'Certificates', 'Gallery', 'Contact'],
            'tr' => ['Ana sayfa', 'Hakkımızda', 'Akademik yazılar', 'Sertifikalar', 'Galeri', 'İletişim'],
        ][$lang];

        $urls = ['/', '/about', '/articles', '/certificates', '/gallery', '/contact'];
        $rows = [];
        foreach ($urls as $index => $url) {
            $rows[] = [
                'lang_code' => $lang,
                'parent_id' => null,
                'title' => $titles[$index],
                'url' => $url,
                'target' => '_self',
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($rows as $row) {
            DB::table('aa_menus')->updateOrInsert(
                ['lang_code' => $row['lang_code'], 'url' => $row['url']],
                $row
            );
        }
    }

    private function seedPages(string $lang, mixed $now): void
    {
        $pages = [
            'about' => ['Haqqımızda', 'Peşəkar tibbi akademiya', 'ALTURAMEDIX ACADEMY təcili yardım, kritik baxım və klinik qərarvermə sahəsində bilikləri paylaşan akademik platformadır.'],
            'certificates' => ['Diplom və sertifikatlar', 'Sənəd yoxlanışı', 'Sənəd nömrəsini daxil edərək statusunu yoxlaya bilərsiniz.'],
            'contact' => ['Əlaqə', 'Bizimlə əlaqə saxlayın', 'Sual və təkliflərinizi göndərin. Komandamız ən qısa zamanda cavab verəcək.'],
        ];

        foreach ($pages as $key => [$title, $subtitle, $body]) {
            DB::table('aa_pages')->updateOrInsert(
                ['lang_code' => $lang, 'page_key' => $key],
                [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'body' => $body,
                    'meta_description' => $body,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function seedHome(string $lang, mixed $now): void
    {
        DB::table('aa_sliders')->updateOrInsert(
            ['lang_code' => $lang, 'sort_order' => 1],
            [
                'title' => 'ALTURAMEDIX ACADEMY',
                'subtitle' => 'Emergency & Critical Care',
                'description' => 'Tibbi bilikləri praktik təcrübə ilə birləşdirən akademik platforma.',
                'image_path' => 'uploads/sliders/20260510225139_875a2233616f8673.jpg',
                'button_1_text' => 'Akademik yazılar',
                'button_1_url' => '/articles',
                'button_2_text' => 'Qeydiyyat',
                'button_2_url' => '#',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $stats = [
            ['fa-solid fa-book-medical', '120+', 'Akademik material'],
            ['fa-solid fa-user-doctor', '30+', 'Ekspert müəllif'],
            ['fa-solid fa-certificate', '1000+', 'Sənəd yoxlanışı'],
        ];

        foreach ($stats as $index => [$icon, $number, $title]) {
            DB::table('aa_home_stats')->updateOrInsert(
                ['lang_code' => $lang, 'sort_order' => $index + 1],
                ['icon_class' => $icon, 'number_text' => $number, 'title' => $title, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $categories = [
            ['Təcili yardım', 'tecili-yardim', 'fa-solid fa-truck-medical'],
            ['Kritik baxım', 'kritik-baxim', 'fa-solid fa-heart-pulse'],
            ['Təlim materialları', 'telim-materiallari', 'fa-solid fa-book-open-reader'],
        ];

        foreach ($categories as $index => [$title, $slug, $icon]) {
            DB::table('aa_article_categories')->updateOrInsert(
                ['lang_code' => $lang, 'slug' => $slug],
                ['title' => $title, 'icon_class' => $icon, 'is_featured' => true, 'sort_order' => $index + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $categoryId = DB::table('aa_article_categories')->where('lang_code', $lang)->where('slug', 'tecili-yardim')->value('id');
        DB::table('aa_articles')->updateOrInsert(
            ['lang_code' => $lang, 'slug' => 'klinik-qerarverme'],
            [
                'category_id' => $categoryId,
                'title' => 'Klinik qərarvermədə sistemli yanaşma',
                'excerpt' => 'Təcili yardım komandasının sürətli və əsaslandırılmış qərar verməsi üçün praktik yanaşmalar.',
                'body' => '<p>Təcili yardım və kritik baxım mühitində klinik qərarlar dəqiq müşahidə, komanda koordinasiyası və sübut əsaslı yanaşma tələb edir.</p>',
                'cover_image' => 'uploads/articles/20260514000619_e2ebc3f7b158ae7c.png',
                'is_featured' => true,
                'is_active' => true,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $features = [
            ['Simulyasiya əsaslı təlim', 'Praktik bacarıqları real ssenarilər üzərində inkişaf etdirin.', 'fa-solid fa-stethoscope', '#'],
            ['Sənəd yoxlanışı', 'Verilmiş sənədləri onlayn yoxlayın.', 'fa-solid fa-certificate', '/certificates'],
            ['Akademik yazılar', 'Tibbi mövzular üzrə yenilənən materiallar.', 'fa-solid fa-newspaper', '/articles'],
        ];

        foreach ($features as $index => [$title, $description, $icon, $url]) {
            DB::table('aa_features')->updateOrInsert(
                ['lang_code' => $lang, 'sort_order' => $index + 1],
                ['title' => $title, 'description' => $description, 'icon_class' => $icon, 'url' => $url, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        foreach ([
            ['journal', 'Medepicent Journal', '', 'Akademik məqalələr və klinik təcrübələr üçün jurnal bloku.', '', 'Daha ətraflı', '/articles', 1],
            ['mobile_app', 'Mobil tətbiq', 'YENİ', 'Mobil tətbiq vasitəsilə materiallara daha sürətli çıxış.', '', '', '#', 2],
            ['support', 'Dəstək lazımdır?', '', 'Komandamız suallarınızı cavablandırmağa hazırdır.', '', 'Əlaqə saxla', '/contact', 3],
        ] as [$key, $title, $subtitle, $body, $image, $buttonText, $buttonUrl, $sort]) {
            DB::table('aa_blocks')->updateOrInsert(
                ['lang_code' => $lang, 'block_key' => $key],
                ['title' => $title, 'subtitle' => $subtitle, 'body' => $body, 'image_path' => $image, 'button_text' => $buttonText, 'button_url' => $buttonUrl, 'sort_order' => $sort, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        DB::table('aa_partners')->updateOrInsert(
            ['lang_code' => $lang, 'sort_order' => 1],
            ['title' => 'Altura', 'logo_path' => 'uploads/partners/20260513225630_840968d2f9a1205c.png', 'url' => '#', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
        );
    }
}
