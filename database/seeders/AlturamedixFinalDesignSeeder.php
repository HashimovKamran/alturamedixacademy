<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class AlturamedixFinalDesignSeeder extends Seeder
{
    private string $dir = 'uploads/design-demo';

    public function run(): void
    {
        $now = now();
        $this->assets();
        $this->languagesAndAdmin($now);

        foreach (['az', 'en', 'ru', 'tr'] as $lang) {
            $this->settings($lang, $now);
            $this->menus($lang, $now);
            $this->pages($lang, $now);
            $this->home($lang, $now);
            $this->articles($lang, $now);
            $this->gallery($lang, $now);
            $this->certificates($lang, $now);
        }

        $this->messagesAndUsers($now);
    }

    private function languagesAndAdmin(mixed $now): void
    {
        DB::table('aa_languages')->upsert([
            ['code' => 'az', 'title' => 'Azərbaycan dili', 'native_name' => 'Azərbaycan dili', 'locale' => 'az_AZ', 'is_default' => true, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'en', 'title' => 'English', 'native_name' => 'English', 'locale' => 'en_US', 'is_default' => false, 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ru', 'title' => 'Русский язык', 'native_name' => 'Русский', 'locale' => 'ru_RU', 'is_default' => false, 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'tr', 'title' => 'Türkçe', 'native_name' => 'Türkçe', 'locale' => 'tr_TR', 'is_default' => false, 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ], ['code'], ['title', 'native_name', 'locale', 'is_default', 'is_active', 'sort_order', 'updated_at']);

        DB::table('aa_admin_users')->updateOrInsert(['username' => 'admin'], [
            'email' => 'admin@alturamedixacademy.az',
            'full_name' => 'ALTURAMEDIX Admin',
            'password_hash' => Hash::make('Admin123456'),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function settings(string $lang, mixed $now): void
    {
        $t = $this->t($lang);
        $settings = [
            'site_name' => 'ALTURAMEDIX ACADEMY',
            'site_slogan' => 'Academy of Emergency & Critical Care',
            'site_description' => $t['description'],
            'logo_image' => $this->asset('logo-symbol.svg'),
            'section_academic' => $t['academic'],
            'section_latest' => $t['latest'],
            'section_trainings' => $t['trainings'],
            'section_trainings_all' => $t['all_trainings'],
            'section_features' => $t['features'],
            'section_partners' => $t['partners'],
            'section_partners_subtitle' => $t['partners_subtitle'],
            'all_view' => $t['all_view'],
            'btn_register' => $t['register'],
            'btn_login' => $t['login'],
            'auth_logout' => $t['logout'],
            'auth_email' => 'Email',
            'auth_password' => $t['password'],
            'auth_login_title' => $t['login'],
            'auth_register_title' => $t['register'],
            'auth_login_button' => $t['login'],
            'auth_register_button' => $t['register'],
            'footer_about' => $t['footer_about'],
            'footer_links' => $t['quick_links'],
            'footer_contact' => $t['contact'],
            'footer_newsletter' => $t['newsletter'],
            'newsletter_text' => $t['newsletter_text'],
            'newsletter_placeholder' => $t['newsletter_placeholder'],
            'contact_phone' => '+994 50 123 45 67',
            'contact_email' => 'info@alturamedixacademy.az',
            'contact_address' => $t['address'],
            'social_instagram' => 'https://instagram.com/',
            'social_linkedin' => 'https://linkedin.com/',
            'social_youtube' => 'https://youtube.com/',
            'home_slider_autoplay_ms' => '5800',
            'home_map_enabled' => '1',
            'home_map_title' => $t['map_title'],
            'home_map_subtitle' => $t['map_subtitle'],
            'home_map_embed' => 'https://maps.google.com/maps?q=Baku%20Azerbaijan&t=k&z=10&ie=UTF8&iwloc=&output=embed',
            'home_map_height' => '360',
            'ad_label' => $t['ad'],
            'ad_default' => $t['ad_default'],
        ];

        DB::table('aa_settings')->upsert(array_map(fn ($key, $value) => [
            'lang_code' => $lang,
            'setting_key' => $key,
            'setting_value' => $value,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_keys($settings), $settings), ['lang_code', 'setting_key'], ['setting_value', 'updated_at']);
    }

    private function menus(string $lang, mixed $now): void
    {
        $t = $this->t($lang);
        $items = [
            ['/', $t['home']],
            ['/about', $t['about']],
            ['/articles', $t['academic']],
            ['/certificates', $t['certificates']],
            ['/trainings', $t['trainings_short']],
            ['/gallery', $t['gallery']],
            ['/contact', $t['contact']],
        ];

        foreach ($items as $index => [$url, $title]) {
            DB::table('aa_menus')->updateOrInsert(['lang_code' => $lang, 'url' => $url], [
                'parent_id' => null,
                'title' => $title,
                'target' => '_self',
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function pages(string $lang, mixed $now): void
    {
        $t = $this->t($lang);
        $pages = [
            'about' => [$t['about'], $t['about_subtitle'], $t['about_body'], 'fa-solid fa-shield-heart'],
            'contact' => [$t['contact'], $t['contact_subtitle'], $t['contact_body'], 'fa-solid fa-envelope-open-text'],
            'certificates' => [$t['certificates'], $t['certificates_subtitle'], $t['certificates_body'], 'fa-solid fa-certificate'],
        ];

        foreach ($pages as $key => [$title, $subtitle, $body, $icon]) {
            DB::table('aa_pages')->updateOrInsert(['lang_code' => $lang, 'page_key' => $key], [
                'title' => $title,
                'slug' => $key,
                'subtitle' => $subtitle,
                'body' => $body,
                'content' => $body,
                'image_path' => '',
                'cover_image' => '',
                'button_text' => '',
                'button_url' => '',
                'meta_description' => strip_tags($body),
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function home(string $lang, mixed $now): void
    {
        $t = $this->t($lang);
        DB::table('aa_sliders')->where('lang_code', $lang)->update(['is_active' => false, 'updated_at' => $now]);
        $sliders = [
            ['ALTURAMEDIX ACADEMY', 'Academy of Emergency & Critical Care', $t['hero_1'], 'hero-medical.svg', $t['trainings_cta'], '/trainings', $t['register_cta'], '#'],
            [$t['hero_title_2'], $t['hero_sub_2'], $t['hero_2'], 'hero-simulation.svg', $t['academic'], '/articles', $t['certificates'], '/certificates'],
            [$t['hero_title_3'], $t['hero_sub_3'], $t['hero_3'], 'hero-conference.svg', $t['contact'], '/contact', $t['gallery'], '/gallery'],
        ];

        foreach ($sliders as $i => [$title, $subtitle, $description, $image, $btn1, $url1, $btn2, $url2]) {
            DB::table('aa_sliders')->insert([
                'lang_code' => $lang,
                'title' => $title,
                'subtitle' => $subtitle,
                'description' => $description,
                'image_path' => $this->asset($image),
                'button_1_text' => $btn1,
                'button_1_url' => $url1,
                'button_2_text' => $btn2,
                'button_2_url' => $url2,
                'sort_order' => $i + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('aa_home_stats')->where('lang_code', $lang)->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            ['fa-solid fa-graduation-cap', '50+', $t['stat_programs']],
            ['fa-solid fa-users', '1000+', $t['stat_participants']],
            ['fa-solid fa-globe', '20+', $t['stat_partners']],
        ] as $i => [$icon, $number, $title]) {
            DB::table('aa_home_stats')->insert(['lang_code' => $lang, 'icon_class' => $icon, 'number_text' => $number, 'title' => $title, 'sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::table('aa_trainings')->where('lang_code', $lang)->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            [$t['training_1'], $t['address'], now()->addDays(9)->toDateString()],
            [$t['training_2'], $t['address'], now()->addDays(16)->toDateString()],
            [$t['training_3'], $t['online'], now()->addDays(28)->toDateString()],
        ] as $i => [$title, $location, $date]) {
            DB::table('aa_trainings')->insert(['lang_code' => $lang, 'title' => $title, 'location' => $location, 'training_date' => $date, 'register_url' => '/register', 'sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::table('aa_features')->where('lang_code', $lang)->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            [$t['feature_1'], $t['feature_1_desc'], 'fa-solid fa-graduation-cap', '/articles'],
            [$t['feature_2'], $t['feature_2_desc'], 'fa-regular fa-file-lines', '/trainings'],
            [$t['feature_3'], $t['feature_3_desc'], 'fa-regular fa-circle-play', '/gallery'],
            [$t['feature_4'], $t['feature_4_desc'], 'fa-regular fa-folder-open', '/articles'],
            [$t['feature_5'], $t['feature_5_desc'], 'fa-regular fa-clipboard', '/certificates'],
        ] as $i => [$title, $description, $icon, $url]) {
            DB::table('aa_features')->insert(['lang_code' => $lang, 'title' => $title, 'description' => $description, 'icon_class' => $icon, 'url' => $url, 'sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        foreach ([
            ['journal', 'MEDEPICENT JOURNAL', '', $t['journal_body'], '', $t['journal_cta'], '/articles', 1],
            ['mobile_app', 'ALTURAMEDIX MOBİL TƏTBİQİ', $t['new'], $t['app_body'], '', '', '#', 2],
            ['support', $t['support_title'], '', $t['support_body'], '', $t['contact_cta'], '/contact', 3],
        ] as [$key, $title, $subtitle, $body, $image, $button, $url, $sort]) {
            DB::table('aa_blocks')->updateOrInsert(['lang_code' => $lang, 'block_key' => $key], ['title' => $title, 'subtitle' => $subtitle, 'body' => $body, 'image_path' => $image, 'button_text' => $button, 'button_url' => $url, 'sort_order' => $sort, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::table('aa_partners')->where('lang_code', $lang)->update(['is_active' => false, 'updated_at' => $now]);
        foreach ([
            ['AS Training', 'partner-as.svg'],
            ['EUSEM', 'partner-eusem.svg'],
            ['Tornado Academy', 'partner-tornado.svg'],
            ['AHA Training Center', 'partner-aha.svg'],
            ['Baku Medical', 'partner-baku.svg'],
        ] as $i => [$title, $logo]) {
            DB::table('aa_partners')->insert(['lang_code' => $lang, 'title' => $title, 'logo_path' => $this->asset($logo), 'url' => '#', 'sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    private function articles(string $lang, mixed $now): void
    {
        $t = $this->t($lang);
        DB::table('aa_article_categories')->where('lang_code', $lang)->whereIn('slug', ['emergency-care', 'critical-care', 'clinical-skills', 'training-materials'])->update(['is_active' => false, 'is_featured' => false, 'updated_at' => $now]);
        $categories = [
            ['reanimasiya', $t['cat_1'], 'fa-solid fa-user-doctor'],
            ['tecili-kardiologiya', $t['cat_2'], 'fa-solid fa-heart-pulse'],
            ['travma', $t['cat_3'], 'fa-solid fa-truck-medical'],
            ['tecili-nevrologiya', $t['cat_4'], 'fa-solid fa-brain'],
            ['toksikologiya', $t['cat_5'], 'fa-solid fa-flask-vial'],
            ['intensiv-terapiya', $t['cat_6'], 'fa-solid fa-bed-pulse'],
        ];
        foreach ($categories as $i => [$slug, $title, $icon]) {
            DB::table('aa_article_categories')->updateOrInsert(['lang_code' => $lang, 'slug' => $slug], ['title' => $title, 'icon_class' => $icon, 'image_path' => '', 'is_featured' => true, 'sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        $articles = [
            ['reanimasiya', 'kardiopulmonar-reanimasiya', $t['article_1'], $t['article_1_excerpt'], 'article-1.svg', 1],
            ['travma', 'travma-pasiyentine-ilkin-yanasma', $t['article_2'], $t['article_2_excerpt'], 'article-2.svg', 2],
            ['tecili-nevrologiya', 'hava-yolu-idareetmesi', $t['article_3'], $t['article_3_excerpt'], 'article-3.svg', 3],
            ['tecili-kardiologiya', 'ekq-interpretasiyasi', $t['article_4'], $t['article_4_excerpt'], 'article-4.svg', 4],
            ['toksikologiya', 'zeherlenmelerde-ilk-yardim', $t['article_5'], $t['article_5_excerpt'], 'article-5.svg', 5],
            ['intensiv-terapiya', 'kritik-bakim-monitorinq', $t['article_6'], $t['article_6_excerpt'], 'article-6.svg', 6],
        ];
        foreach ($articles as [$catSlug, $slug, $title, $excerpt, $image, $days]) {
            $categoryId = DB::table('aa_article_categories')->where('lang_code', $lang)->where('slug', $catSlug)->value('id');
            DB::table('aa_articles')->updateOrInsert(['lang_code' => $lang, 'slug' => $slug], [
                'category_id' => $categoryId,
                'title' => $title,
                'excerpt' => $excerpt,
                'short_description' => $excerpt,
                'body' => $t['article_body'],
                'content' => $t['article_body'],
                'cover_image' => $this->asset($image),
                'author_name' => 'ALTURAMEDIX Editorial',
                'is_featured' => true,
                'is_active' => true,
                'published_at' => Carbon::now()->subDays($days),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function gallery(string $lang, mixed $now): void
    {
        $t = $this->t($lang);
        foreach ([
            [$t['gallery_1'], 'gallery-1.svg'],
            [$t['gallery_2'], 'gallery-2.svg'],
            [$t['gallery_3'], 'gallery-3.svg'],
            [$t['gallery_4'], 'gallery-4.svg'],
            [$t['gallery_5'], 'gallery-5.svg'],
            [$t['gallery_6'], 'gallery-6.svg'],
        ] as $i => [$title, $image]) {
            DB::table('aa_gallery')->updateOrInsert(['lang_code' => $lang, 'title' => $title], ['description' => $t['gallery_desc'], 'image_path' => $this->asset($image), 'sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    private function certificates(string $lang, mixed $now): void
    {
        foreach ([
            ['ALT-2026-1001', 'Aylin Məmmədova', 'ACLS Provider Kursu', 'valid'],
            ['ALT-2026-1002', 'Murad Əliyev', 'PHTLS Provider Kursu', 'valid'],
            ['ALT-2025-2201', 'Leyla Həsənova', 'Advanced Airway Management', 'expired'],
        ] as [$no, $name, $course, $status]) {
            DB::table('aa_certificates')->updateOrInsert(['cert_no' => $no], ['lang_code' => $lang, 'full_name' => $name, 'course_title' => $course, 'certificate_type' => 'certificate', 'issue_date' => now()->subMonths(4)->toDateString(), 'expire_date' => now()->addYear()->toDateString(), 'file_path' => $this->asset('certificate-demo.svg'), 'status' => $status, 'note' => 'Demo məlumatıdır.', 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    private function messagesAndUsers(mixed $now): void
    {
        DB::table('aa_site_users')->updateOrInsert(['email' => 'demo@alturamedixacademy.az'], ['full_name' => 'Demo User', 'phone' => '+994 50 000 00 00', 'password_hash' => Hash::make('Demo123456'), 'email_notify' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('aa_contact_messages')->updateOrInsert(['email' => 'partner@example.com', 'subject' => 'Əməkdaşlıq'], ['full_name' => 'Demo Partner', 'phone' => '+994 50 222 22 22', 'message' => 'Təlim proqramları üzrə əməkdaşlıq təklifi.', 'ip_address' => '127.0.0.1', 'is_read' => false, 'created_at' => $now, 'updated_at' => $now]);
    }

    private function assets(): void
    {
        $path = public_path($this->dir);
        File::ensureDirectoryExists($path);
        $files = [
            'logo-symbol.svg' => $this->logo(),
            'hero-medical.svg' => $this->hero('ALTURAMEDIX', '#dcebf6', '#9fb7c9'),
            'hero-simulation.svg' => $this->hero('SIMULATION', '#eef5fb', '#b5cfe1'),
            'hero-conference.svg' => $this->hero('CONFERENCE', '#edf7ff', '#a9c1d6'),
            'article-1.svg' => $this->articleSvg('CPR'), 'article-2.svg' => $this->articleSvg('TRAUMA'), 'article-3.svg' => $this->articleSvg('AIRWAY'), 'article-4.svg' => $this->articleSvg('ECG'), 'article-5.svg' => $this->articleSvg('TOX'), 'article-6.svg' => $this->articleSvg('ICU'),
            'gallery-1.svg' => $this->gallerySvg('TƏLİM'), 'gallery-2.svg' => $this->gallerySvg('SIM LAB'), 'gallery-3.svg' => $this->gallerySvg('KONFRANS'), 'gallery-4.svg' => $this->gallerySvg('TEAM'), 'gallery-5.svg' => $this->gallerySvg('EMS'), 'gallery-6.svg' => $this->gallerySvg('CERT'),
            'partner-as.svg' => $this->partnerSvg('AS'), 'partner-eusem.svg' => $this->partnerSvg('EUSEM'), 'partner-tornado.svg' => $this->partnerSvg('TORNADO'), 'partner-aha.svg' => $this->partnerSvg('AHA Training\nCenter'), 'partner-baku.svg' => $this->partnerSvg('Baku\nMedical'),
            'certificate-demo.svg' => $this->certificateSvg(),
        ];
        foreach ($files as $name => $content) {
            File::put($path . DIRECTORY_SEPARATOR . $name, $content);
        }
    }

    private function asset(string $name): string
    {
        return $this->dir . '/' . $name;
    }

    private function svg(string $body, int $w = 1200, int $h = 720): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">' . $body . '</svg>';
    }

    private function logo(): string
    {
        return $this->svg('<circle cx="300" cy="300" r="260" fill="#071728"/><path d="M300 150l140 50v112c0 99-67 165-140 205-73-40-140-106-140-205V200l140-50z" fill="#fff"/><path d="M300 244c34-55 120-25 104 48-12 56-74 86-104 112-30-26-92-56-104-112-16-73 70-103 104-48z" fill="#ff8a1c"/>', 600, 600);
    }

    private function hero(string $text, string $bg, string $line): string
    {
        return $this->svg('<defs><linearGradient id="g" x1="0" x2="1"><stop stop-color="' . $bg . '"/><stop offset="1" stop-color="#ffffff"/></linearGradient></defs><rect width="1200" height="720" fill="url(#g)"/><circle cx="870" cy="290" r="170" fill="#fff" opacity=".55"/><circle cx="880" cy="300" r="110" fill="none" stroke="' . $line . '" stroke-width="34"/><path d="M690 460c160-72 300-74 430-20" fill="none" stroke="#8aa6ba" stroke-width="22" stroke-linecap="round" opacity=".55"/><path d="M810 80c32 94 48 186 47 276" stroke="#0a2a46" stroke-width="18" stroke-linecap="round" opacity=".22"/><text x="130" y="610" font-family="Arial" font-size="70" font-weight="900" fill="#0b6f91" opacity=".28">' . htmlspecialchars($text, ENT_QUOTES) . '</text></svg>');
    }

    private function articleSvg(string $text): string
    {
        return $this->svg('<rect width="900" height="600" fill="#e8eef5"/><rect x="80" y="90" width="740" height="420" rx="32" fill="#fff"/><circle cx="235" cy="250" r="75" fill="#ff8a1c" opacity=".18"/><path d="M190 275h90M235 230v90" stroke="#061727" stroke-width="22" stroke-linecap="round"/><text x="450" y="290" font-family="Arial" font-size="56" font-weight="900" fill="#061727" text-anchor="middle">' . htmlspecialchars($text, ENT_QUOTES) . '</text></svg>', 900, 600);
    }

    private function gallerySvg(string $text): string
    {
        return $this->svg('<rect width="900" height="600" fill="#f4f8fc"/><rect x="70" y="80" width="760" height="440" rx="38" fill="#0a2a46"/><circle cx="690" cy="190" r="60" fill="#ff8a1c"/><path d="M130 455l180-160 135 110 110-92 220 142z" fill="#dcebf6"/><text x="450" y="190" font-family="Arial" font-size="52" font-weight="900" fill="#fff" text-anchor="middle">' . htmlspecialchars($text, ENT_QUOTES) . '</text></svg>', 900, 600);
    }

    private function partnerSvg(string $text): string
    {
        $lines = explode('\\n', $text);
        $body = '<rect width="360" height="160" rx="28" fill="#fff"/><rect x="1" y="1" width="358" height="158" rx="28" fill="none" stroke="#dce6f1"/><text x="180" y="75" font-family="Arial" font-size="34" font-weight="900" fill="#0a2a46" text-anchor="middle">' . htmlspecialchars($lines[0], ENT_QUOTES) . '</text>';
        if (isset($lines[1])) $body .= '<text x="180" y="112" font-family="Arial" font-size="26" font-weight="800" fill="#0a2a46" text-anchor="middle">' . htmlspecialchars($lines[1], ENT_QUOTES) . '</text>';
        return $this->svg($body, 360, 160);
    }

    private function certificateSvg(): string
    {
        return $this->svg('<rect width="1200" height="720" fill="#fffaf5"/><rect x="95" y="75" width="1010" height="570" rx="44" fill="#fff" stroke="#ff8a1c" stroke-width="8"/><text x="600" y="220" font-family="Arial" font-size="66" font-weight="900" fill="#061727" text-anchor="middle">CERTIFICATE</text><text x="600" y="300" font-family="Arial" font-size="30" font-weight="800" fill="#64748b" text-anchor="middle">ALTURAMEDIX ACADEMY</text><rect x="330" y="380" width="540" height="30" rx="15" fill="#dce6f1"/><circle cx="600" cy="530" r="60" fill="#ff8a1c" opacity=".22"/><path d="M600 482l52 20v38c0 36-25 60-52 76-27-16-52-40-52-76v-38l52-20z" fill="#ff8a1c"/></svg>');
    }

    private function t(string $lang): array
    {
        $az = [
            'description' => 'Klinik bilik paylaşımı, elmi tədris və peşəkar inkişaf üçün yaradılmış müasir akademik platforma.',
            'home' => 'Ana səhifə', 'about' => 'Haqqımızda', 'academic' => 'Akademik yazılar', 'certificates' => 'Diplom və sertifikatlar', 'trainings_short' => 'Təlimlər', 'gallery' => 'Qalereya', 'contact' => 'Əlaqə',
            'latest' => 'Son dərc olunanlar', 'trainings' => 'Təlim və konfranslar', 'all_trainings' => 'Hamısına bax', 'features' => 'Akademik imkanlarımız', 'partners' => 'Tərəfdaşlarımız', 'partners_subtitle' => 'Akademik inkişaf yolunda əməkdaşlıq etdiyimiz qurumlar.', 'all_view' => 'Hamısına bax',
            'register' => 'Qeydiyyat', 'login' => 'Daxil ol', 'logout' => 'Çıxış', 'password' => 'Şifrə', 'quick_links' => 'Sürətli keçidlər', 'newsletter' => 'Bülleten', 'newsletter_text' => 'Yeniliklər və elmi məqalələr haqqında məlumat almaq üçün abunə olun.', 'newsletter_placeholder' => 'E-mail ünvanınız', 'address' => 'Bakı, Azərbaycan', 'footer_about' => 'Elmi bilik, klinik təcrübə və peşəkar inkişaf üçün müasir akademik platforma.',
            'map_title' => 'Ünvan xəritəsi', 'map_subtitle' => 'Bizi xəritədə tapın.', 'ad' => 'Reklam', 'ad_default' => 'Sizin reklamınız burada ola bilər',
            'about_subtitle' => 'Alturamedix Academy haqqında', 'about_body' => '<p>Alturamedix Academy tibbi təhsil, klinik bilik paylaşımı və peşəkar inkişaf üçün yaradılmış müasir akademik platformadır.</p><p>Məqsədimiz təcili və kritik tibb sahəsində keyfiyyətli bilikləri həkimlərə, tibb işçilərinə və tələbələrə daha əlçatan etməkdir.</p>',
            'contact_subtitle' => 'Bizimlə əlaqə saxlayın', 'contact_body' => '<p>Sualınız, təklifiniz və ya əməkdaşlıq müraciətiniz varsa, aşağıdakı forma vasitəsilə bizimlə əlaqə saxlaya bilərsiniz.</p>',
            'certificates_subtitle' => 'Sənəd yoxlanışı', 'certificates_body' => 'Sənəd nömrəsini daxil edərək statusunu onlayn yoxlaya bilərsiniz.',
            'hero_1' => 'Klinik bilik paylaşımı, elmi tədris və peşəkar inkişaf üçün yaradılmış müasir akademik platforma.', 'hero_title_2' => 'Simulyasiya əsaslı təlimlər', 'hero_sub_2' => 'Praktik bacarıqları real ssenarilərlə gücləndirin', 'hero_2' => 'Təcili və kritik tibbi qərarvermə üçün praktik təlim proqramları.', 'hero_title_3' => 'Akademik inkişaf ekosistemi', 'hero_sub_3' => 'Məqalələr, sertifikatlar və konfranslar', 'hero_3' => 'Peşəkar inkişafı ölçüləbilən və davamlı edən rəqəmsal akademiya.',
            'trainings_cta' => 'Təlimlərə bax', 'register_cta' => 'Qeydiyyatdan keç',
            'stat_programs' => 'Təlim proqramı', 'stat_participants' => 'Təlimçi və iştirakçı', 'stat_partners' => 'Beynəlxalq tərəfdaş',
            'cat_1' => 'Reanimasiya', 'cat_2' => 'Təcili Kardiologiya', 'cat_3' => 'Travma', 'cat_4' => 'Təcili Nevrologiya', 'cat_5' => 'Toksikologiya', 'cat_6' => 'İntensiv Terapiya',
            'article_1' => 'Kardiopulmonar reanimasiyada yenilənmiş yanaşmalar', 'article_1_excerpt' => 'Reanimasiya komandası üçün ilkin qiymətləndirmə və keyfiyyətli kompressiya prinsipləri.', 'article_2' => 'Travma pasiyentinin ilkin yanaşma prinsipləri', 'article_2_excerpt' => 'Travma xəstəsində sürətli qərarvermə və prioritet müdaxilələr.', 'article_3' => 'Çətin hava yolu idarəetməsi', 'article_3_excerpt' => 'Təcili vəziyyətdə hava yolunun təhlükəsiz təmin edilməsi üçün praktik yanaşmalar.', 'article_4' => 'EKQ interpretasiyasında kritik məqamlar', 'article_4_excerpt' => 'Təhlükəli ritmlərin sürətli tanınması və klinik qərarvermə.', 'article_5' => 'Zəhərlənmələrdə ilk yardım', 'article_5_excerpt' => 'Toksikoloji hallarda ilkin qiymətləndirmə və təhlükəsizlik addımları.', 'article_6' => 'Kritik baxımda monitorinq', 'article_6_excerpt' => 'İntensiv terapiyada dinamik izləmə və erkən xəbərdarlıq göstəriciləri.', 'article_body' => '<p>Bu material klinik qərarvermə prosesində praktik, ölçüləbilən və komanda yönümlü yanaşmanı izah edir.</p><p>Məqalədə əsas addımlar, risk nöqtələri və təlim zamanı istifadə edilə biləcək qısa checklist təqdim olunur.</p>',
            'training_1' => 'ACLS Provider Kursu', 'training_2' => 'PHTLS Provider Kursu', 'training_3' => 'Advanced Airway Management', 'online' => 'Onlayn',
            'feature_1' => 'Elmi məqalələr', 'feature_1_desc' => 'Akademik tədqiqatlar və məqalə kolleksiyası', 'feature_2' => 'Konqres arxivi', 'feature_2_desc' => 'Keçmiş konfrans və konqres materialları', 'feature_3' => 'Video təlimatlar', 'feature_3_desc' => 'Praktik bacarıqlar üçün video materiallar', 'feature_4' => 'Tədris materialları', 'feature_4_desc' => 'Yüklənə bilən dərslik və tədris vasitələri', 'feature_5' => 'Biblioqrafik bazalar', 'feature_5_desc' => 'Faydalı linklər və elmi mənbə bazaları',
            'journal_body' => 'Təcili və kritik tibb sahəsində elmi məqalələri oxuyun.', 'journal_cta' => 'Jurnala keçid', 'new' => 'Yeni', 'app_body' => 'Təlimlərə daha asan çıxış və bildirişlər üçün mobil tətbiqimiz çox yaxında.', 'support_title' => 'Sualınız var? Bizimlə əlaqə saxlayın.', 'support_body' => 'Komandamız sizə kömək etməyə hazırdır.', 'contact_cta' => 'Əlaqə saxla',
            'gallery_1' => 'Praktik təlim günü', 'gallery_2' => 'Simulyasiya laboratoriyası', 'gallery_3' => 'Akademik konfrans', 'gallery_4' => 'Klinik komanda işi', 'gallery_5' => 'Təcili yardım ssenarisi', 'gallery_6' => 'Sertifikat təqdimatı', 'gallery_desc' => 'Alturamedix Academy tədbirlərindən görüntü.',
        ];
        if ($lang === 'en') return array_merge($az, ['home'=>'Home','about'=>'About','academic'=>'Academic articles','certificates'=>'Diplomas and certificates','trainings_short'=>'Courses','gallery'=>'Gallery','contact'=>'Contact','register'=>'Register','login'=>'Sign in','logout'=>'Logout','quick_links'=>'Quick links','newsletter'=>'Newsletter','address'=>'Baku, Azerbaijan']);
        if ($lang === 'tr') return array_merge($az, ['description'=>'Klinik bilgi paylaşımı, bilimsel eğitim ve profesyonel gelişim için modern akademik platform.','home'=>'Ana sayfa','about'=>'Hakkımızda','academic'=>'Akademik yazılar','certificates'=>'Diploma ve sertifikalar','trainings_short'=>'Kurslar','gallery'=>'Galeri','contact'=>'İletişim','latest'=>'Son yayınlar','trainings'=>'Kurslar ve konferanslar','features'=>'Akademik kaynaklarımız','partners'=>'Ortaklarımız','all_view'=>'Tümünü görüntüle','register'=>'Kayıt ol','login'=>'Giriş yap','logout'=>'Çıkış','password'=>'Şifre','quick_links'=>'Hızlı bağlantılar','newsletter'=>'Bülten','newsletter_text'=>'Güncellemeler ve akademik yazılar hakkında bilgi almak için abone olun.','newsletter_placeholder'=>'Email adresiniz','address'=>'Bakü, Azerbaycan','footer_about'=>'Bilimsel bilgi, klinik deneyim ve profesyonel gelişim için modern akademik platform.','map_title'=>'Adres haritası','map_subtitle'=>'Bizi haritada bulun.','ad'=>'Reklam','ad_default'=>'Reklamınız burada yer alabilir','about_subtitle'=>'Alturamedix Academy hakkında','contact_subtitle'=>'Bizimle iletişime geçin','certificates_subtitle'=>'Belge doğrulama','certificates_body'=>'Belge numarasını girerek durumunu çevrimiçi doğrulayabilirsiniz.','trainings_cta'=>'Kursları görüntüle','register_cta'=>'Kayıt ol','stat_programs'=>'Eğitim programı','stat_participants'=>'Katılımcı','stat_partners'=>'Uluslararası ortak']);
        if ($lang === 'ru') return array_merge($az, ['home'=>'Главная','about'=>'О нас','academic'=>'Статьи','certificates'=>'Дипломы и сертификаты','trainings_short'=>'Курсы','gallery'=>'Галерея','contact'=>'Контакты','register'=>'Регистрация','login'=>'Войти','logout'=>'Выйти','quick_links'=>'Быстрые ссылки','newsletter'=>'Рассылка','address'=>'Баку, Азербайджан']);
        return $az;
    }
}
