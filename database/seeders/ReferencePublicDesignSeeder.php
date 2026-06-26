<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReferencePublicDesignSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (['az', 'en', 'ru', 'tr'] as $lang) {
            $this->settings($lang, $now);
            $this->menus($lang, $now);
        }

        $this->homeAz($now);
    }

    private function settings(string $lang, mixed $now): void
    {
        $settings = [
            'site_name' => 'ALTURAMEDIX ACADEMY',
            'site_slogan' => 'Academy of Emergency & Critical Care',
            'site_description' => 'Klinik bilik paylaşımı, elmi tədris və peşəkar inkişaf üçün yaradılmış müasir akademik platforma.',
            'logo_image' => 'uploads/settings/20260514205402_c2b0f3641d700696.png',
            'section_academic' => 'Akademik yazılar',
            'section_latest' => 'Son dərc olunanlar',
            'section_trainings' => 'Təlim və konfranslar',
            'section_trainings_all' => '',
            'section_features' => 'Akademik imkanlarımız',
            'section_partners' => 'Tərəfdaşlarımız',
            'section_partners_subtitle' => '',
            'all_view' => 'Hamısına bax',
            'btn_register' => 'Qeydiyyat',
            'btn_login' => 'Daxil ol',
            'auth_logout' => 'Çıxış',
            'auth_email' => 'Email hesabı',
            'auth_password' => 'Şifrə',
            'auth_login_title' => 'Daxil ol',
            'auth_login_subtitle' => 'Hesabınıza daxil olaraq yenilikləri izləyin.',
            'auth_register_title' => 'Qeydiyyatdan keç',
            'auth_register_subtitle' => 'Platformaya qoşulun və yeniliklərdən xəbərdar olun.',
            'auth_login_button' => 'Daxil ol',
            'auth_register_button' => 'Qeydiyyatdan keç',
            'footer_about' => 'Elmi bilik, klinik təcrübə və peşəkar inkişaf üçün müasir akademik platforma.',
            'footer_links' => 'Sürətli keçidlər',
            'footer_contact' => 'Əlaqə',
            'footer_newsletter' => 'Bülleten',
            'newsletter_text' => 'Yeniliklər və elmi məqalələr haqqında məlumat almaq üçün abunə olun.',
            'newsletter_placeholder' => 'E-mail ünvanınız',
            'contact_phone' => '+994 50 123 45 67',
            'contact_email' => 'info@alturamedixacademy.az',
            'contact_address' => 'Bakı, Azərbaycan',
            'social_instagram' => '#',
            'social_linkedin' => '#',
            'social_youtube' => '#',
            'home_slider_autoplay_ms' => '999999',
            'home_map_enabled' => '1',
            'home_map_title' => 'Ünvan xəritəsi',
            'home_map_subtitle' => 'Bizi xəritədə tapın',
            'home_map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d115341.67011432594!2d49.77255913101008!3d40.39469399753873!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40307d6bd6211cf9%3A0x343f6b5e7ae56c6b!2zQmFrw7w!5e1!3m2!1str!2saz!4v1778700600178!5m2!1str!2saz',
            'home_map_height' => '360',
            'ad_label' => 'Reklam',
            'ad_default' => 'Sizin reklamınız burada ola bilər',
        ];

        foreach ($settings as $key => $value) {
            DB::table('aa_settings')->updateOrInsert(
                ['lang_code' => $lang, 'setting_key' => $key],
                ['setting_value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    private function menus(string $lang, mixed $now): void
    {
        DB::table('aa_menus')->where('lang_code', $lang)->update(['is_active' => false, 'updated_at' => $now]);

        $items = [
            ['/', 'Ana səhifə'],
            ['/about', 'Haqqımızda'],
            ['/articles', 'Akademik yazılar'],
            ['/certificates', 'Diplom və sertifikatlar'],
            ['/trainings', 'Təlimlər'],
            ['/gallery', 'Qalereya'],
            ['/contact', 'Əlaqə'],
        ];

        foreach ($items as $index => [$url, $title]) {
            DB::table('aa_menus')->updateOrInsert(
                ['lang_code' => $lang, 'url' => $url],
                [
                    'parent_id' => null,
                    'title' => $title,
                    'target' => '_self',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function homeAz(mixed $now): void
    {
        DB::table('aa_sliders')->where('lang_code', 'az')->update(['is_active' => false, 'updated_at' => $now]);
        DB::table('aa_sliders')->insert([
            'lang_code' => 'az',
            'title' => 'ALTURAMEDIX ACADEMY',
            'subtitle' => 'Academy of Emergency & Critical Care',
            'description' => 'Klinik bilik paylaşımı, elmi tədris və peşəkar inkişaf üçün yaradılmış müasir akademik platforma.',
            'image_path' => 'uploads/sliders/20260510225139_875a2233616f8673.jpg',
            'button_1_text' => 'Təlimlərə bax',
            'button_1_url' => '/trainings',
            'button_2_text' => 'Qeydiyyatdan keç',
            'button_2_url' => '#',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('aa_home_stats')->where('lang_code', 'az')->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            ['fa-solid fa-graduation-cap', '50+', 'Təlim proqramı'],
            ['fa-solid fa-users', '1000+', 'Təlimçi və iştirakçı'],
            ['fa-solid fa-globe', '20+', 'Beynəlxalq tərəfdaş'],
        ] as $index => [$icon, $number, $title]) {
            DB::table('aa_home_stats')->insert([
                'lang_code' => 'az',
                'icon_class' => $icon,
                'number_text' => $number,
                'title' => $title,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('aa_article_categories')->where('lang_code', 'az')->update(['is_featured' => false, 'updated_at' => $now]);
        foreach ([
            ['reanimasiya', 'Reanimasiya', 'fa-solid fa-user-doctor', 1],
            ['tecili-kardiologiya', 'Təcili Kardiologiya', 'fa-solid fa-heart-pulse', 2],
            ['travma', 'Travma', 'fa-solid fa-truck-medical', 3],
            ['tecili-nevrologiya', 'Təcili Nevrologiya', 'fa-solid fa-brain', 4],
            ['toksikologiya', 'Toksikologiya', 'fa-solid fa-flask-vial', 5],
            ['intensiv-terapiya', 'İntensiv Terapiya', 'fa-solid fa-bed-pulse', 6],
        ] as [$slug, $title, $icon, $sort]) {
            DB::table('aa_article_categories')->updateOrInsert(
                ['lang_code' => 'az', 'slug' => $slug],
                [
                    'title' => $title,
                    'icon_class' => $icon,
                    'image_path' => '',
                    'is_featured' => true,
                    'sort_order' => $sort,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('aa_articles')->where('lang_code', 'az')->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            ['reanimasiya', 'kardiopulmonar-reanimasiyada-yenilenmis-yanasmalar', 'Kardiopulmonar reanimasiyada yenilənmiş yanaşmalar', '2024-05-20'],
            ['travma', 'travma-pasiyentinin-ilkin-yanasma-prinsipleri', 'Travma pasiyentinin ilkin yanaşma prinsipləri', '2024-05-15'],
            ['tecili-nevrologiya', 'cetin-hava-yolu-idareetmesi', 'Çətin hava yolu idarəetməsi', '2024-05-10'],
            ['tecili-kardiologiya', 'ekq-interpretasiyasinda-kritik-meqamlar', 'EKQ interpretasiyasında kritik məqamlar', '2024-05-08'],
        ] as [$categorySlug, $slug, $title, $date]) {
            $categoryId = DB::table('aa_article_categories')->where('lang_code', 'az')->where('slug', $categorySlug)->value('id');
            DB::table('aa_articles')->updateOrInsert(
                ['lang_code' => 'az', 'slug' => $slug],
                [
                    'category_id' => $categoryId,
                    'title' => $title,
                    'excerpt' => '',
                    'body' => '',
                    'short_description' => '',
                    'content' => '',
                    'cover_image' => '',
                    'author_name' => 'ALTURAMEDIX ACADEMY',
                    'is_featured' => true,
                    'is_active' => true,
                    'published_at' => Carbon::parse($date),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('aa_trainings')->where('lang_code', 'az')->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            ['ACLS Provider Kursu', 'Bakı, Azərbaycan', '2026-05-25'],
            ['PHTLS Provider Kursu', 'Bakı, Azərbaycan', '2026-06-01'],
            ['Advanced Airway Management', 'Bakı, Azərbaycan', '2026-06-08'],
        ] as $index => [$title, $location, $date]) {
            DB::table('aa_trainings')->insert([
                'lang_code' => 'az',
                'title' => $title,
                'location' => $location,
                'training_date' => $date,
                'register_url' => '/register',
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('aa_features')->where('lang_code', 'az')->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            ['Elmi məqalələr', 'Akademik tədqiqatlar və məqalə kolleksiyası', 'fa-solid fa-graduation-cap', 'articles.php'],
            ['Konqres arxivi', 'Keçmiş konfrans və konqres materialları', 'fa-regular fa-file-lines', '#'],
            ['Video təlimatlar', 'Praktik bacarıqlar üçün video materiallar', 'fa-regular fa-circle-play', '#'],
            ['Tədris materialları', 'Yüklənə bilən dərslik və tədris vasitələri', 'fa-regular fa-folder-open', '#'],
            ['Biblioqrafik bazalar', 'Faydalı linklər və elmi mənbə bazaları', 'fa-regular fa-clipboard', '#'],
        ] as $index => [$title, $description, $icon, $url]) {
            DB::table('aa_features')->insert([
                'lang_code' => 'az',
                'title' => $title,
                'description' => $description,
                'icon_class' => $icon,
                'url' => $url,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([
            ['journal', 'MEDEPICENT JOURNAL', '', 'Təcili və kritik tibb sahəsində elmi məqalələri oxuyun.', '', 'Jurnala keçid', '#', 1],
            ['mobile_app', 'ALTURAMEDIX MOBİL TƏTBİQİ', 'YENİ', 'Təlimlərə daha asan çıxış və bildirişlər üçün mobil tətbiqimiz çox yaxında.', '', '', '#', 2],
            ['support', 'Sualınız var? Bizimlə əlaqə saxlayın.', '', 'Komandamız sizə kömək etməyə hazırdır.', '', 'Əlaqə saxla', '/contact', 3],
        ] as [$key, $title, $subtitle, $body, $image, $button, $url, $sort]) {
            DB::table('aa_blocks')->updateOrInsert(
                ['lang_code' => 'az', 'block_key' => $key],
                [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'body' => $body,
                    'image_path' => $image,
                    'button_text' => $button,
                    'button_url' => $url,
                    'sort_order' => $sort,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('aa_ads')->where('lang_code', 'az')->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            ['sidebar', 1],
            ['sidebar', 2],
            ['bottom', 1],
        ] as $index => [$position, $sort]) {
            DB::table('aa_ads')->insert([
                'lang_code' => 'az',
                'position_key' => $position,
                'title' => 'Sizin reklamınız burada ola bilər',
                'image_path' => '',
                'url' => '#',
                'sort_order' => $sort,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('aa_partners')->where('lang_code', 'az')->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            ['Senan Aghayev', 'uploads/partners/20260513225630_840968d2f9a1205c.png', 'https://www.senan.az'],
            ['Baku Medikal Plaza', 'uploads/partners/20260513225701_32b6ab81ad8c1dfe.png', 'https://www.bmp.az'],
            ['Tornado Muhafizə xidməti', 'uploads/partners/20260513225744_8184414dfb846ab1.png', 'https://www.tornado.az'],
            ['EUSEM', '', '#'],
            ['AHA Training Center', '', '#'],
        ] as $index => [$title, $logo, $url]) {
            DB::table('aa_partners')->insert([
                'lang_code' => 'az',
                'title' => $title,
                'logo_path' => $logo,
                'url' => $url,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
