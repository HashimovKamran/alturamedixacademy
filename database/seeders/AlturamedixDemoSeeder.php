<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AlturamedixDemoSeeder extends Seeder
{
    private string $assetDir = 'uploads/demo';

    public function run(): void
    {
        $now = now();
        $this->ensureDemoAssets();
        $this->seedAdminAndLanguages($now);

        foreach (['az', 'en', 'ru', 'tr'] as $lang) {
            $this->seedSettings($lang, $now);
            $this->seedMenus($lang, $now);
            $this->seedPages($lang, $now);
            $this->seedHomeContent($lang, $now);
            $this->seedArticles($lang, $now);
            $this->seedGallery($lang, $now);
            $this->seedCertificates($lang, $now);
        }

        $this->seedUsersAndMessages($now);
    }

    private function seedAdminAndLanguages(mixed $now): void
    {
        DB::table('aa_languages')->upsert([
            ['code' => 'az', 'title' => 'Azərbaycan dili', 'native_name' => 'Azərbaycan dili', 'locale' => 'az_AZ', 'is_default' => true, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'en', 'title' => 'English', 'native_name' => 'English', 'locale' => 'en_US', 'is_default' => false, 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ru', 'title' => 'Русский язык', 'native_name' => 'Русский', 'locale' => 'ru_RU', 'is_default' => false, 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'tr', 'title' => 'Türkçe', 'native_name' => 'Türkçe', 'locale' => 'tr_TR', 'is_default' => false, 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ], ['code'], ['title', 'native_name', 'locale', 'is_default', 'is_active', 'sort_order', 'updated_at']);

        DB::table('aa_admin_users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'email' => 'admin@alturamedix.local',
                'full_name' => 'ALTURAMEDIX Admin',
                'password_hash' => Hash::make('Admin123456'),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function seedSettings(string $lang, mixed $now): void
    {
        $locale = $this->locale($lang);
        $settings = [
            'site_name' => 'ALTURAMEDIX ACADEMY',
            'site_slogan' => $locale['slogan'],
            'site_description' => $locale['description'],
            'logo_image' => $this->asset('logo-symbol.svg'),
            'section_academic' => $locale['academic'],
            'section_latest' => $locale['latest'],
            'section_trainings' => $locale['trainings'],
            'section_trainings_all' => $locale['all_trainings'],
            'section_features' => $locale['features'],
            'section_partners' => $locale['partners'],
            'section_partners_subtitle' => $locale['partners_subtitle'],
            'all_view' => $locale['all_view'],
            'btn_register' => $locale['register'],
            'btn_login' => $locale['login'],
            'auth_profile_title' => $locale['profile'],
            'auth_logout' => $locale['logout'],
            'auth_login_title' => $locale['login'],
            'auth_login_subtitle' => $locale['login_subtitle'],
            'auth_register_title' => $locale['register'],
            'auth_register_subtitle' => $locale['register_subtitle'],
            'auth_email' => 'Email',
            'auth_password' => $locale['password'],
            'auth_login_button' => $locale['login'],
            'auth_register_button' => $locale['register'],
            'auth_google_button' => 'Google',
            'ad_label' => $locale['ad'],
            'ad_default' => $locale['ad_default'],
            'footer_about' => $locale['footer_about'],
            'footer_links' => $locale['quick_links'],
            'footer_contact' => $locale['contact'],
            'footer_newsletter' => $locale['newsletter'],
            'newsletter_text' => $locale['newsletter_text'],
            'newsletter_placeholder' => $locale['newsletter_placeholder'],
            'contact_phone' => '+994 12 310 44 88',
            'contact_email' => 'info@alturamedix.local',
            'contact_address' => $locale['address'],
            'social_instagram' => 'https://instagram.com/',
            'social_linkedin' => 'https://linkedin.com/',
            'social_youtube' => 'https://youtube.com/',
            'home_slider_autoplay_ms' => '6200',
            'home_map_enabled' => '1',
            'home_map_title' => $locale['map_title'],
            'home_map_subtitle' => $locale['map_subtitle'],
            'home_map_embed' => 'https://maps.google.com/maps?q=Baku%20Azerbaijan&t=&z=12&ie=UTF8&iwloc=&output=embed',
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
        $locale = $this->locale($lang);
        $items = [
            ['/', $locale['home']],
            ['/about', $locale['about']],
            ['/articles', $locale['academic']],
            ['/certificates', $locale['certificates']],
            ['/gallery', $locale['gallery']],
            ['/contact', $locale['contact']],
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

    private function seedPages(string $lang, mixed $now): void
    {
        $locale = $this->locale($lang);
        $pages = [
            'about' => [$locale['about'], $locale['about_subtitle'], $locale['about_body'], 'fa-solid fa-shield-heart', 'page-about.svg'],
            'certificates' => [$locale['certificates_title'], $locale['certificates_subtitle'], $locale['certificates_body'], 'fa-solid fa-certificate', 'page-certificate.svg'],
            'contact' => [$locale['contact'], $locale['contact_subtitle'], $locale['contact_body'], 'fa-solid fa-envelope-open-text', 'page-contact.svg'],
        ];

        foreach ($pages as $key => [$title, $subtitle, $body, $icon, $image]) {
            DB::table('aa_pages')->updateOrInsert(
                ['lang_code' => $lang, 'page_key' => $key],
                [
                    'title' => $title,
                    'slug' => $key,
                    'subtitle' => $subtitle,
                    'body' => $body,
                    'content' => $body,
                    'image_path' => $this->asset($image),
                    'cover_image' => $this->asset($image),
                    'button_text' => $key === 'about' ? $locale['contact'] : '',
                    'button_url' => $key === 'about' ? '/contact' : '',
                    'meta_description' => strip_tags($body),
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function seedHomeContent(string $lang, mixed $now): void
    {
        $locale = $this->locale($lang);
        $sliders = [
            [
                'title' => 'ALTURAMEDIX ACADEMY',
                'subtitle' => $locale['slogan'],
                'description' => $locale['hero_1'],
                'image_path' => $this->asset('hero-1.svg'),
                'button_1_text' => $locale['academic'],
                'button_1_url' => '/articles',
                'button_2_text' => $locale['register'],
                'button_2_url' => '#',
            ],
            [
                'title' => $locale['hero_title_2'],
                'subtitle' => $locale['hero_sub_2'],
                'description' => $locale['hero_2'],
                'image_path' => $this->asset('hero-2.svg'),
                'button_1_text' => $locale['trainings'],
                'button_1_url' => '/trainings',
                'button_2_text' => $locale['certificates'],
                'button_2_url' => '/certificates',
            ],
            [
                'title' => $locale['hero_title_3'],
                'subtitle' => $locale['hero_sub_3'],
                'description' => $locale['hero_3'],
                'image_path' => $this->asset('hero-3.svg'),
                'button_1_text' => $locale['contact'],
                'button_1_url' => '/contact',
                'button_2_text' => $locale['gallery'],
                'button_2_url' => '/gallery',
            ],
        ];

        foreach ($sliders as $index => $slider) {
            DB::table('aa_sliders')->updateOrInsert(
                ['lang_code' => $lang, 'sort_order' => $index + 1],
                array_merge($slider, ['is_active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }

        $stats = [
            ['fa-solid fa-book-medical', '180+', $locale['stat_materials']],
            ['fa-solid fa-user-doctor', '42+', $locale['stat_experts']],
            ['fa-solid fa-certificate', '2500+', $locale['stat_certificates']],
            ['fa-solid fa-hospital-user', '18+', $locale['stat_programs']],
        ];

        foreach ($stats as $index => [$icon, $number, $title]) {
            DB::table('aa_home_stats')->updateOrInsert(
                ['lang_code' => $lang, 'sort_order' => $index + 1],
                ['icon_class' => $icon, 'number_text' => $number, 'title' => $title, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $features = [
            [$locale['feature_1'], $locale['feature_1_desc'], 'fa-solid fa-stethoscope', '/trainings'],
            [$locale['feature_2'], $locale['feature_2_desc'], 'fa-solid fa-certificate', '/certificates'],
            [$locale['feature_3'], $locale['feature_3_desc'], 'fa-solid fa-newspaper', '/articles'],
            [$locale['feature_4'], $locale['feature_4_desc'], 'fa-solid fa-video', '/gallery'],
            [$locale['feature_5'], $locale['feature_5_desc'], 'fa-solid fa-users-gear', '/contact'],
            [$locale['feature_6'], $locale['feature_6_desc'], 'fa-solid fa-chart-line', '/about'],
        ];

        foreach ($features as $index => [$title, $description, $icon, $url]) {
            DB::table('aa_features')->updateOrInsert(
                ['lang_code' => $lang, 'sort_order' => $index + 1],
                ['title' => $title, 'description' => $description, 'icon_class' => $icon, 'url' => $url, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $blocks = [
            ['journal', $locale['journal_title'], '', $locale['journal_body'], '', $locale['read_more'], '/articles', 1],
            ['mobile_app', $locale['app_title'], $locale['new'], $locale['app_body'], '', '', '#', 2],
            ['support', $locale['support_title'], '', $locale['support_body'], '', $locale['contact'], '/contact', 3],
        ];

        foreach ($blocks as [$key, $title, $subtitle, $body, $image, $buttonText, $buttonUrl, $sort]) {
            DB::table('aa_blocks')->updateOrInsert(
                ['lang_code' => $lang, 'block_key' => $key],
                ['title' => $title, 'subtitle' => $subtitle, 'body' => $body, 'image_path' => $image, 'button_text' => $buttonText, 'button_url' => $buttonUrl, 'sort_order' => $sort, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $trainings = [
            [$locale['training_1'], $locale['address'], now()->addDays(8)->toDateString(), '/register'],
            [$locale['training_2'], $locale['online'], now()->addDays(18)->toDateString(), '/register'],
            [$locale['training_3'], $locale['address'], now()->addDays(30)->toDateString(), '/register'],
            [$locale['training_4'], $locale['online'], now()->addDays(45)->toDateString(), '/register'],
        ];

        foreach ($trainings as $index => [$title, $location, $date, $registerUrl]) {
            DB::table('aa_trainings')->updateOrInsert(
                ['lang_code' => $lang, 'title' => $title],
                ['location' => $location, 'training_date' => $date, 'register_url' => $registerUrl, 'sort_order' => $index + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $partners = [
            ['MedAlliance', 'partner-medalliance.svg'],
            ['Baku Health Center', 'partner-baku-health.svg'],
            ['Emergency Pro', 'partner-emergency.svg'],
            ['Critical Care Lab', 'partner-critical.svg'],
            ['SimLab Academy', 'partner-simlab.svg'],
            ['Clinical Hub', 'partner-clinical.svg'],
            ['Nursing Forum', 'partner-nursing.svg'],
            ['Rescue Team', 'partner-rescue.svg'],
        ];

        foreach ($partners as $index => [$title, $logo]) {
            DB::table('aa_partners')->updateOrInsert(
                ['lang_code' => $lang, 'title' => $title],
                ['logo_path' => $this->asset($logo), 'url' => '#', 'sort_order' => $index + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $ads = [
            ['sidebar', $locale['ad_sidebar'], 'ad-course.svg', '/trainings', 1],
            ['bottom', $locale['ad_bottom'], 'ad-conference.svg', '/contact', 1],
        ];

        foreach ($ads as [$position, $title, $image, $url, $sort]) {
            DB::table('aa_ads')->updateOrInsert(
                ['lang_code' => $lang, 'position_key' => $position, 'sort_order' => $sort],
                ['title' => $title, 'image_path' => $this->asset($image), 'url' => $url, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    private function seedArticles(string $lang, mixed $now): void
    {
        $locale = $this->locale($lang);
        $categories = [
            ['emergency-care', $locale['cat_1'], 'fa-solid fa-truck-medical', 'cat-emergency.svg'],
            ['critical-care', $locale['cat_2'], 'fa-solid fa-heart-pulse', 'cat-critical.svg'],
            ['clinical-skills', $locale['cat_3'], 'fa-solid fa-user-nurse', 'cat-skills.svg'],
            ['training-materials', $locale['cat_4'], 'fa-solid fa-book-open-reader', 'cat-training.svg'],
        ];

        foreach ($categories as $index => [$slug, $title, $icon, $image]) {
            DB::table('aa_article_categories')->updateOrInsert(
                ['lang_code' => $lang, 'slug' => $slug],
                ['title' => $title, 'icon_class' => $icon, 'image_path' => $this->asset($image), 'is_featured' => true, 'sort_order' => $index + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $articles = [
            ['emergency-care', 'airway-management', $locale['article_1'], $locale['article_1_excerpt'], $locale['article_body'], 'article-airway.svg', true, 1],
            ['critical-care', 'sepsis-bundle', $locale['article_2'], $locale['article_2_excerpt'], $locale['article_body'], 'article-sepsis.svg', true, 2],
            ['clinical-skills', 'team-communication', $locale['article_3'], $locale['article_3_excerpt'], $locale['article_body'], 'article-team.svg', false, 3],
            ['training-materials', 'simulation-design', $locale['article_4'], $locale['article_4_excerpt'], $locale['article_body'], 'article-sim.svg', true, 4],
            ['emergency-care', 'trauma-checklist', $locale['article_5'], $locale['article_5_excerpt'], $locale['article_body'], 'article-trauma.svg', false, 5],
            ['critical-care', 'ventilation-basics', $locale['article_6'], $locale['article_6_excerpt'], $locale['article_body'], 'article-ventilation.svg', false, 6],
            ['clinical-skills', 'ecg-fast-read', $locale['article_7'], $locale['article_7_excerpt'], $locale['article_body'], 'article-ecg.svg', false, 7],
            ['training-materials', 'learning-pathway', $locale['article_8'], $locale['article_8_excerpt'], $locale['article_body'], 'article-learning.svg', false, 8],
        ];

        foreach ($articles as [$catSlug, $slug, $title, $excerpt, $body, $image, $featured, $days]) {
            $categoryId = DB::table('aa_article_categories')->where('lang_code', $lang)->where('slug', $catSlug)->value('id');
            DB::table('aa_articles')->updateOrInsert(
                ['lang_code' => $lang, 'slug' => $slug],
                [
                    'category_id' => $categoryId,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'short_description' => $excerpt,
                    'content' => $body,
                    'cover_image' => $this->asset($image),
                    'author_name' => 'ALTURAMEDIX Editorial',
                    'is_featured' => $featured,
                    'is_active' => true,
                    'published_at' => Carbon::now()->subDays($days),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function seedGallery(string $lang, mixed $now): void
    {
        $locale = $this->locale($lang);
        $items = [
            [$locale['gallery_1'], $locale['gallery_desc'], 'gallery-1.svg'],
            [$locale['gallery_2'], $locale['gallery_desc'], 'gallery-2.svg'],
            [$locale['gallery_3'], $locale['gallery_desc'], 'gallery-3.svg'],
            [$locale['gallery_4'], $locale['gallery_desc'], 'gallery-4.svg'],
            [$locale['gallery_5'], $locale['gallery_desc'], 'gallery-5.svg'],
            [$locale['gallery_6'], $locale['gallery_desc'], 'gallery-6.svg'],
        ];

        foreach ($items as $index => [$title, $description, $image]) {
            DB::table('aa_gallery')->updateOrInsert(
                ['lang_code' => $lang, 'title' => $title],
                ['description' => $description, 'image_path' => $this->asset($image), 'sort_order' => $index + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    private function seedCertificates(string $lang, mixed $now): void
    {
        $locale = $this->locale($lang);
        $certificates = [
            ['ALT-2026-1001', 'Aylin Məmmədova', $locale['cert_course_1'], 'certificate', now()->subMonths(3), now()->addYear(), 'valid'],
            ['ALT-2026-1002', 'Murad Əliyev', $locale['cert_course_2'], 'diploma', now()->subMonths(2), now()->addMonths(18), 'valid'],
            ['ALT-2025-2201', 'Leyla Həsənova', $locale['cert_course_3'], 'attendance', now()->subYear(), now()->subMonth(), 'expired'],
            ['ALT-2026-1103', 'Kamran Quliyev', $locale['cert_course_4'], 'certificate', now()->subMonth(), now()->addYear(), 'valid'],
        ];

        foreach ($certificates as [$no, $name, $course, $type, $issue, $expire, $status]) {
            DB::table('aa_certificates')->updateOrInsert(
                ['cert_no' => $no],
                [
                    'lang_code' => $lang,
                    'full_name' => $name,
                    'course_title' => $course,
                    'certificate_type' => $type,
                    'issue_date' => $issue->toDateString(),
                    'expire_date' => $expire->toDateString(),
                    'file_path' => $this->asset('certificate-sample.svg'),
                    'status' => $status,
                    'note' => $locale['cert_note'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function seedUsersAndMessages(mixed $now): void
    {
        $users = [
            ['Nigar Rzayeva', '+994 50 111 22 33', 'nigar@example.com'],
            ['Orxan Kərimli', '+994 55 222 33 44', 'orxan@example.com'],
            ['Samirə Abbasova', '+994 70 333 44 55', 'samira@example.com'],
        ];

        foreach ($users as [$name, $phone, $email]) {
            DB::table('aa_site_users')->updateOrInsert(
                ['email' => $email],
                [
                    'full_name' => $name,
                    'phone' => $phone,
                    'password_hash' => Hash::make('User123456'),
                    'google_id' => null,
                    'avatar_url' => '',
                    'email_notify' => true,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $messages = [
            ['Rəşad Məmmədli', 'rashad@example.com', '+994 51 555 11 22', 'Təlim qeydiyyatı', 'Kritik baxım təliminin növbəti tarixləri barədə məlumat almaq istəyirəm.'],
            ['Aysel Qasımova', 'aysel@example.com', '+994 50 444 22 11', 'Sənəd yoxlanışı', 'Sənəd nömrəsinin yoxlama səhifəsində görünməsi üçün dəstək lazımdır.'],
            ['Tural Həsənli', 'tural@example.com', '+994 55 777 88 99', 'Korporativ əməkdaşlıq', 'Komandamız üçün simulyasiya əsaslı təlim paketi ilə maraqlanırıq.'],
        ];

        foreach ($messages as [$name, $email, $phone, $subject, $message]) {
            DB::table('aa_contact_messages')->updateOrInsert(
                ['email' => $email, 'subject' => $subject],
                [
                    'full_name' => $name,
                    'phone' => $phone,
                    'message' => $message,
                    'ip_address' => '127.0.0.1',
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function ensureDemoAssets(): void
    {
        $directory = public_path($this->assetDir);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $assets = [
            'logo-symbol.svg' => $this->logoSvg(),
            'hero-1.svg' => $this->heroSvg('ALTURAMEDIX', 'Emergency & Critical Care', '#eaf6ff', '#0e5b7f'),
            'hero-2.svg' => $this->heroSvg('SIMULATION', 'Hands-on medical training', '#fff4e8', '#ff8a1c'),
            'hero-3.svg' => $this->heroSvg('CLINICAL PATHWAY', 'Guided learning experience', '#eefcf8', '#0ea5b8'),
            'page-about.svg' => $this->sceneSvg('About Academy', 'shield'),
            'page-certificate.svg' => $this->sceneSvg('Certificate Verification', 'certificate'),
            'page-contact.svg' => $this->sceneSvg('Contact Team', 'mail'),
            'cat-emergency.svg' => $this->iconCardSvg('Emergency', '#ff8a1c'),
            'cat-critical.svg' => $this->iconCardSvg('Critical', '#ef4444'),
            'cat-skills.svg' => $this->iconCardSvg('Skills', '#0ea5b8'),
            'cat-training.svg' => $this->iconCardSvg('Training', '#2563eb'),
            'article-airway.svg' => $this->articleSvg('Airway'),
            'article-sepsis.svg' => $this->articleSvg('Sepsis'),
            'article-team.svg' => $this->articleSvg('Team'),
            'article-sim.svg' => $this->articleSvg('Simulation'),
            'article-trauma.svg' => $this->articleSvg('Trauma'),
            'article-ventilation.svg' => $this->articleSvg('Ventilation'),
            'article-ecg.svg' => $this->articleSvg('ECG'),
            'article-learning.svg' => $this->articleSvg('Learning'),
            'gallery-1.svg' => $this->gallerySvg('Workshop'),
            'gallery-2.svg' => $this->gallerySvg('Simulation Lab'),
            'gallery-3.svg' => $this->gallerySvg('Conference'),
            'gallery-4.svg' => $this->gallerySvg('Clinical Team'),
            'gallery-5.svg' => $this->gallerySvg('Emergency Drill'),
            'gallery-6.svg' => $this->gallerySvg('Certificate Day'),
            'ad-course.svg' => $this->adSvg('New Course'),
            'ad-conference.svg' => $this->adSvg('Conference'),
            'certificate-sample.svg' => $this->certificateSvg(),
        ];

        foreach (['MedAlliance', 'Baku Health', 'Emergency Pro', 'Critical Lab', 'SimLab', 'Clinical Hub', 'Nursing Forum', 'Rescue Team'] as $index => $name) {
            $assets['partner-' . Str::slug($name) . '.svg'] = $this->partnerSvg($name, $index);
        }

        // Aliases used above.
        $assets['partner-baku-health.svg'] = $assets['partner-baku-health.svg'] ?? $this->partnerSvg('Baku Health', 1);
        $assets['partner-emergency.svg'] = $this->partnerSvg('Emergency Pro', 2);
        $assets['partner-critical.svg'] = $this->partnerSvg('Critical Care', 3);
        $assets['partner-simlab.svg'] = $this->partnerSvg('SimLab Academy', 4);
        $assets['partner-clinical.svg'] = $this->partnerSvg('Clinical Hub', 5);
        $assets['partner-nursing.svg'] = $this->partnerSvg('Nursing Forum', 6);
        $assets['partner-rescue.svg'] = $this->partnerSvg('Rescue Team', 7);
        $assets['partner-medalliance.svg'] = $this->partnerSvg('MedAlliance', 0);

        foreach ($assets as $filename => $svg) {
            File::put($directory . DIRECTORY_SEPARATOR . $filename, $svg);
        }
    }

    private function asset(string $filename): string
    {
        return $this->assetDir . '/' . $filename;
    }

    private function svgWrap(string $content, string $width = '1200', string $height = '720'): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">' . $content . '</svg>';
    }

    private function logoSvg(): string
    {
        return $this->svgWrap('<defs><linearGradient id="g" x1="0" x2="1"><stop stop-color="#ff9b2f"/><stop offset="1" stop-color="#ff7417"/></linearGradient></defs><rect width="1200" height="720" rx="180" fill="#061727"/><path d="M600 130l260 84v170c0 138-96 238-260 306-164-68-260-168-260-306V214l260-84z" fill="url(#g)"/><path d="M600 318c50-78 166-31 126 58-26 58-126 122-126 122S500 434 474 376c-40-89 76-136 126-58z" fill="#061727"/>');
    }

    private function heroSvg(string $title, string $subtitle, string $bg, string $accent): string
    {
        $title = e($title);
        $subtitle = e($subtitle);
        return $this->svgWrap('<defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="' . $bg . '"/><stop offset="1" stop-color="#ffffff"/></linearGradient><filter id="s"><feDropShadow dx="0" dy="22" stdDeviation="28" flood-color="#061727" flood-opacity=".18"/></filter></defs><rect width="1200" height="720" fill="url(#g)"/><circle cx="930" cy="250" r="210" fill="' . $accent . '" opacity=".14"/><circle cx="1010" cy="430" r="145" fill="#061727" opacity=".08"/><g filter="url(#s)"><rect x="710" y="160" width="330" height="360" rx="52" fill="#fff"/><path d="M835 264h80M875 224v80" stroke="' . $accent . '" stroke-width="38" stroke-linecap="round"/><path d="M780 390c80-55 126 54 188 0" fill="none" stroke="#061727" stroke-width="22" stroke-linecap="round" opacity=".82"/><rect x="780" y="450" width="190" height="22" rx="11" fill="#dbe7f2"/></g><text x="92" y="570" fill="#061727" font-size="52" font-family="Arial" font-weight="900">' . $title . '</text><text x="94" y="620" fill="#456177" font-size="25" font-family="Arial" font-style="italic" font-weight="700">' . $subtitle . '</text>');
    }

    private function sceneSvg(string $title, string $kind): string
    {
        $title = e($title);
        return $this->svgWrap('<defs><linearGradient id="g" x1="0" x2="1"><stop stop-color="#061727"/><stop offset="1" stop-color="#0a2a46"/></linearGradient></defs><rect width="1200" height="720" fill="url(#g)"/><circle cx="900" cy="220" r="180" fill="#ff8a1c" opacity=".18"/><circle cx="250" cy="520" r="210" fill="#0ea5b8" opacity=".14"/><rect x="120" y="150" width="440" height="300" rx="42" fill="#fff" opacity=".10"/><rect x="650" y="210" width="330" height="220" rx="34" fill="#fff" opacity=".12"/><text x="120" y="585" fill="#fff" font-size="62" font-family="Arial" font-weight="900">' . $title . '</text><text x="120" y="635" fill="#dbeafe" font-size="28" font-family="Arial" font-weight="700">ALTURAMEDIX ACADEMY</text>');
    }

    private function iconCardSvg(string $text, string $accent): string
    {
        $text = e($text);
        return $this->svgWrap('<rect width="1200" height="720" rx="90" fill="#ffffff"/><circle cx="600" cy="310" r="150" fill="' . $accent . '" opacity=".14"/><path d="M600 190l142 46v96c0 77-52 136-142 174-90-38-142-97-142-174v-96l142-46z" fill="' . $accent . '"/><text x="600" y="590" fill="#061727" font-size="72" font-family="Arial" font-weight="900" text-anchor="middle">' . $text . '</text>');
    }

    private function articleSvg(string $text): string
    {
        $text = e($text);
        return $this->svgWrap('<defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#edf7ff"/><stop offset="1" stop-color="#ffffff"/></linearGradient></defs><rect width="1200" height="720" fill="url(#g)"/><rect x="120" y="120" width="960" height="480" rx="54" fill="#fff" stroke="#dbe7f2" stroke-width="4"/><circle cx="280" cy="290" r="92" fill="#ff8a1c" opacity=".17"/><path d="M235 292h90M280 247v90" stroke="#ff8a1c" stroke-width="38" stroke-linecap="round"/><rect x="430" y="230" width="420" height="34" rx="17" fill="#061727" opacity=".88"/><rect x="430" y="300" width="560" height="24" rx="12" fill="#8fa1b5" opacity=".45"/><rect x="430" y="350" width="470" height="24" rx="12" fill="#8fa1b5" opacity=".33"/><text x="120" y="665" fill="#061727" font-size="68" font-family="Arial" font-weight="900">' . $text . '</text>');
    }

    private function gallerySvg(string $text): string
    {
        $text = e($text);
        return $this->svgWrap('<defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#061727"/><stop offset="1" stop-color="#0a5a7c"/></linearGradient></defs><rect width="1200" height="720" fill="url(#g)"/><rect x="92" y="92" width="1016" height="536" rx="50" fill="#fff" opacity=".10"/><circle cx="330" cy="275" r="88" fill="#ff8a1c"/><rect x="455" y="220" width="410" height="36" rx="18" fill="#fff" opacity=".78"/><rect x="455" y="295" width="520" height="26" rx="13" fill="#fff" opacity=".46"/><rect x="455" y="350" width="360" height="26" rx="13" fill="#fff" opacity=".34"/><text x="600" y="570" fill="#fff" font-size="66" font-family="Arial" font-weight="900" text-anchor="middle">' . $text . '</text>');
    }

    private function partnerSvg(string $name, int $index): string
    {
        $colors = ['#ff8a1c', '#0ea5b8', '#2563eb', '#ef4444', '#22c55e', '#7c3aed', '#f59e0b', '#0f766e'];
        $accent = $colors[$index % count($colors)];
        $name = e($name);
        return '<svg xmlns="http://www.w3.org/2000/svg" width="420" height="180" viewBox="0 0 420 180"><rect width="420" height="180" rx="34" fill="#fff"/><circle cx="75" cy="90" r="42" fill="' . $accent . '" opacity=".16"/><path d="M75 50l42 16v30c0 30-18 51-42 64-24-13-42-34-42-64V66l42-16z" fill="' . $accent . '"/><text x="138" y="85" fill="#061727" font-size="30" font-family="Arial" font-weight="900">' . $name . '</text><text x="138" y="120" fill="#64748b" font-size="18" font-family="Arial" font-weight="700">Medical partner</text></svg>';
    }

    private function adSvg(string $text): string
    {
        $text = e($text);
        return $this->svgWrap('<rect width="1200" height="720" rx="70" fill="#061727"/><circle cx="890" cy="230" r="180" fill="#ff8a1c" opacity=".18"/><text x="95" y="300" fill="#fff" font-size="78" font-family="Arial" font-weight="900">' . $text . '</text><text x="100" y="380" fill="#dbeafe" font-size="32" font-family="Arial" font-weight="700">ALTURAMEDIX ACADEMY</text><rect x="100" y="455" width="245" height="70" rx="24" fill="#ff8a1c"/></svg>');
    }

    private function certificateSvg(): string
    {
        return $this->svgWrap('<rect width="1200" height="720" fill="#fffaf5"/><rect x="90" y="80" width="1020" height="560" rx="40" fill="#fff" stroke="#ff8a1c" stroke-width="8"/><text x="600" y="210" fill="#061727" font-size="64" font-family="Arial" font-weight="900" text-anchor="middle">CERTIFICATE</text><text x="600" y="292" fill="#64748b" font-size="28" font-family="Arial" text-anchor="middle">ALTURAMEDIX ACADEMY</text><rect x="310" y="370" width="580" height="34" rx="17" fill="#dbe7f2"/><rect x="390" y="440" width="420" height="24" rx="12" fill="#dbe7f2"/><circle cx="600" cy="535" r="55" fill="#ff8a1c" opacity=".22"/><path d="M600 490l50 18v38c0 35-24 58-50 72-26-14-50-37-50-72v-38l50-18z" fill="#ff8a1c"/></svg>');
    }

    private function locale(string $lang): array
    {
        $az = [
            'slogan' => 'Academy of Emergency & Critical Care',
            'description' => 'Təcili yardım, kritik baxım və klinik qərarvermə üzrə müasir akademik platforma.',
            'home' => 'Ana səhifə', 'about' => 'Haqqımızda', 'academic' => 'Akademik yazılar', 'certificates' => 'Sertifikatlar', 'gallery' => 'Qalereya', 'contact' => 'Əlaqə',
            'latest' => 'Son dərc olunanlar', 'trainings' => 'Təlim və konfranslar', 'all_trainings' => 'Bütün təlim və konfranslara bax', 'features' => 'Akademik imkanlarımız', 'partners' => 'Tərəfdaşlarımız', 'partners_subtitle' => 'Klinik təhsil və peşəkar inkişaf yolunda əməkdaşlıq etdiyimiz qurumlar.', 'all_view' => 'Hamısına bax',
            'register' => 'Qeydiyyat', 'login' => 'Daxil ol', 'profile' => 'Profil', 'logout' => 'Çıxış', 'password' => 'Şifrə',
            'login_subtitle' => 'Hesabınıza daxil olaraq materialları və yenilikləri izləyin.', 'register_subtitle' => 'Akademik yeniliklərdən xəbərdar olmaq üçün hesab yaradın.',
            'ad' => 'Reklam', 'ad_default' => 'Sizin reklamınız burada ola bilər', 'quick_links' => 'Sürətli keçidlər', 'newsletter' => 'Bülleten', 'newsletter_text' => 'Yeniliklər və elmi məqalələr haqqında məlumat almaq üçün abunə olun.', 'newsletter_placeholder' => 'E-mail ünvanınız', 'address' => 'Bakı, Azərbaycan', 'footer_about' => 'Elmi bilik, klinik təcrübə və peşəkar inkişaf üçün müasir akademik platforma.',
            'map_title' => 'Akademiyanın ünvanı', 'map_subtitle' => 'Təlim və görüşlər üçün bizi xəritədə tapın.',
            'about_subtitle' => 'Peşəkar tibbi akademiya', 'about_body' => '<p>ALTURAMEDIX ACADEMY təcili yardım, kritik baxım və klinik qərarvermə sahəsində bilikləri praktik təcrübə ilə birləşdirən akademik platformadır.</p><p>Platformada simulyasiya əsaslı təlimlər, akademik yazılar, sertifikat yoxlama sistemi və peşəkar inkişaf imkanları təqdim olunur.</p>',
            'certificates_title' => 'Diplom və sertifikatlar', 'certificates_subtitle' => 'Sənəd yoxlanışı', 'certificates_body' => 'Sənəd nömrəsini daxil edərək statusunu onlayn yoxlaya bilərsiniz.', 'contact_subtitle' => 'Bizimlə əlaqə saxlayın', 'contact_body' => 'Sual və təkliflərinizi göndərin. Komandamız ən qısa zamanda cavab verəcək.',
            'hero_1' => 'Tibbi bilikləri praktik təcrübə ilə birləşdirən, sürətli qərarverməni dəstəkləyən akademik platforma.', 'hero_title_2' => 'Simulyasiya əsaslı təlimlər', 'hero_sub_2' => 'Praktik bacarıqları real ssenarilərlə gücləndirin', 'hero_2' => 'Təcili yardım və kritik baxım komandaları üçün interaktiv təlim proqramları.', 'hero_title_3' => 'Klinik inkişaf ekosistemi', 'hero_sub_3' => 'Məqalələr, sertifikatlar və ekspert dəstəyi', 'hero_3' => 'Peşəkar inkişafı ölçüləbilən, izlənilən və davamlı edən rəqəmsal akademiya.',
            'stat_materials' => 'Akademik material', 'stat_experts' => 'Ekspert müəllif', 'stat_certificates' => 'Sənəd yoxlanışı', 'stat_programs' => 'Təlim proqramı',
            'feature_1' => 'Simulyasiya təlimləri', 'feature_1_desc' => 'Real klinik ssenarilər üzərində praktik bacarıqlar.', 'feature_2' => 'Sənəd yoxlanışı', 'feature_2_desc' => 'Verilmiş sənədləri onlayn və sürətli yoxlayın.', 'feature_3' => 'Akademik yazılar', 'feature_3_desc' => 'Tibbi mövzular üzrə yenilənən məqalələr.', 'feature_4' => 'Video və qalereya', 'feature_4_desc' => 'Təlim anlarını və tədbirləri izləyin.', 'feature_5' => 'Korporativ proqram', 'feature_5_desc' => 'Komandalar üçün fərdi təlim həlləri.', 'feature_6' => 'İnkişaf xəritəsi', 'feature_6_desc' => 'Bilik və bacarıqları sistemli şəkildə artırın.',
            'journal_title' => 'Medepicent Journal', 'journal_body' => 'Akademik məqalələr və klinik təcrübələr üçün jurnal bloku.', 'read_more' => 'Daha ətraflı', 'app_title' => 'Mobil tətbiq', 'new' => 'Yeni', 'app_body' => 'Materiallara və sertifikatlara daha sürətli çıxış.', 'support_title' => 'Dəstək lazımdır?', 'support_body' => 'Komandamız suallarınızı cavablandırmağa hazırdır.',
            'online' => 'Onlayn', 'training_1' => 'Təcili yardımda hava yolu idarəetməsi', 'training_2' => 'Kritik baxımda qərarvermə vebinarı', 'training_3' => 'Travma komandası üçün simulyasiya günü', 'training_4' => 'EKQ-nin sürətli klinik oxunuşu',
            'ad_sidebar' => 'Yeni təlim proqramına qeydiyyat başladı', 'ad_bottom' => 'Korporativ simulyasiya paketi üçün müraciət edin',
            'cat_1' => 'Təcili yardım', 'cat_2' => 'Kritik baxım', 'cat_3' => 'Klinik bacarıqlar', 'cat_4' => 'Təlim materialları',
            'article_1' => 'Hava yolu təhlükəsizliyində ilk 5 dəqiqə', 'article_1_excerpt' => 'Təcili vəziyyətdə sistemli qiymətləndirmə və komanda rollarının düzgün bölüşdürülməsi.', 'article_2' => 'Sepsis protokoluna praktik yanaşma', 'article_2_excerpt' => 'Erkən tanıma, maye strategiyası və monitorinq üzrə qısa klinik bələdçi.', 'article_3' => 'Kritik komandada effektiv kommunikasiya', 'article_3_excerpt' => 'SBAR modeli və qapalı dövr kommunikasiya prinsipləri.', 'article_4' => 'Simulyasiya ssenarisini necə hazırlamalı?', 'article_4_excerpt' => 'Təlim məqsədi, ölçüləbilən nəticə və debrifinq strukturu.', 'article_5' => 'Travma xəstəsində sürətli checklist', 'article_5_excerpt' => 'İlk baxış və prioritet müdaxilələr üçün praktik siyahı.', 'article_6' => 'Mexaniki ventilyasiyaya giriş', 'article_6_excerpt' => 'Əsas parametrlər və təhlükəsiz başlanğıc üçün tövsiyələr.', 'article_7' => 'EKQ-də təhlükəli ritmləri tez tanımaq', 'article_7_excerpt' => 'Klinik risk yaradan ritmlərin qısa vizual yanaşması.', 'article_8' => 'Fərdi öyrənmə xəritəsi necə qurulur?', 'article_8_excerpt' => 'Peşəkar inkişaf üçün mərhələli və izlənən akademik plan.', 'article_body' => '<p>Bu material klinik qərarvermə prosesində praktik, ölçüləbilən və komanda yönümlü yanaşmanı izah edir.</p><p>Məqalədə əsas addımlar, risk nöqtələri və təlim zamanı istifadə edilə biləcək qısa checklist təqdim olunur.</p>',
            'gallery_1' => 'Praktik təlim günü', 'gallery_2' => 'Simulyasiya laboratoriyası', 'gallery_3' => 'Akademik konfrans', 'gallery_4' => 'Klinik komanda işi', 'gallery_5' => 'Təcili yardım ssenarisi', 'gallery_6' => 'Sertifikat təqdimatı', 'gallery_desc' => 'ALTURAMEDIX ACADEMY tədbirlərindən görüntü.',
            'cert_course_1' => 'Emergency Airway Management', 'cert_course_2' => 'Critical Care Decision Making', 'cert_course_3' => 'Simulation Instructor Basics', 'cert_course_4' => 'ECG Rapid Assessment', 'cert_note' => 'Demo məlumatıdır.',
        ];

        if ($lang === 'en') {
            return array_merge($az, [
                'description' => 'A modern academic platform for emergency care, critical care and clinical decision-making.', 'home' => 'Home', 'about' => 'About', 'academic' => 'Academic articles', 'certificates' => 'Certificates', 'gallery' => 'Gallery', 'contact' => 'Contact', 'latest' => 'Latest publications', 'trainings' => 'Courses and conferences', 'all_trainings' => 'View all courses and conferences', 'features' => 'Academic opportunities', 'partners' => 'Our partners', 'partners_subtitle' => 'Organizations collaborating with us in clinical education and professional development.', 'all_view' => 'View all', 'register' => 'Register', 'login' => 'Sign in', 'logout' => 'Logout', 'password' => 'Password', 'quick_links' => 'Quick links', 'newsletter' => 'Newsletter', 'newsletter_placeholder' => 'Your email address', 'address' => 'Baku, Azerbaijan', 'contact_subtitle' => 'Get in touch', 'contact_body' => 'Send us your questions and suggestions. Our team will reply shortly.', 'about_subtitle' => 'Professional medical academy', 'certificates_title' => 'Diplomas and certificates', 'certificates_subtitle' => 'Document verification', 'certificates_body' => 'Enter the document number to verify its status online.',
            ]);
        }

        if ($lang === 'tr') {
            return array_merge($az, [
                'description' => 'Acil bakım, kritik bakım ve klinik karar verme için modern akademik platform.',
                'home' => 'Ana sayfa',
                'about' => 'Hakkımızda',
                'academic' => 'Akademik yazılar',
                'certificates' => 'Sertifikalar',
                'gallery' => 'Galeri',
                'contact' => 'İletişim',
                'latest' => 'Son yayınlar',
                'trainings' => 'Kurslar ve konferanslar',
                'all_trainings' => 'Tüm kurs ve konferansları görüntüle',
                'features' => 'Akademik olanaklarımız',
                'partners' => 'Ortaklarımız',
                'partners_subtitle' => 'Klinik eğitim ve profesyonel gelişim yolunda iş birliği yaptığımız kurumlar.',
                'all_view' => 'Tümünü görüntüle',
                'register' => 'Kayıt ol',
                'login' => 'Giriş yap',
                'logout' => 'Çıkış',
                'password' => 'Şifre',
                'quick_links' => 'Hızlı bağlantılar',
                'newsletter' => 'Bülten',
                'newsletter_placeholder' => 'Email adresiniz',
                'address' => 'Bakü, Azerbaycan',
                'contact_subtitle' => 'Bizimle iletişime geçin',
                'contact_body' => 'Sorularınızı ve önerilerinizi gönderin. Ekibimiz kısa sürede yanıt verecektir.',
                'about_subtitle' => 'Profesyonel tıp akademisi',
                'certificates_title' => 'Diploma ve sertifikalar',
                'certificates_subtitle' => 'Belge doğrulama',
                'certificates_body' => 'Belge numarasını girerek durumunu çevrimiçi doğrulayabilirsiniz.',
                'map_title' => 'Akademinin adresi',
                'map_subtitle' => 'Eğitimler ve görüşmeler için bizi haritada bulun.',
                'ad' => 'Reklam',
                'ad_default' => 'Reklamınız burada yer alabilir',
                'footer_about' => 'Bilimsel bilgi, klinik deneyim ve profesyonel gelişim için modern akademik platform.',
            ]);
        }

        if ($lang === 'ru') {
            return array_merge($az, [
                'description' => 'Современная академическая платформа по неотложной помощи, интенсивной терапии и клиническим решениям.', 'home' => 'Главная', 'about' => 'О нас', 'academic' => 'Статьи', 'certificates' => 'Сертификаты', 'gallery' => 'Галерея', 'contact' => 'Контакты', 'latest' => 'Последние публикации', 'trainings' => 'Курсы и конференции', 'all_trainings' => 'Все курсы и конференции', 'features' => 'Возможности', 'partners' => 'Партнеры', 'partners_subtitle' => 'Организации, сотрудничающие с нами в области медицинского образования.', 'all_view' => 'Смотреть все', 'register' => 'Регистрация', 'login' => 'Войти', 'logout' => 'Выйти', 'password' => 'Пароль', 'quick_links' => 'Быстрые ссылки', 'newsletter' => 'Рассылка', 'newsletter_placeholder' => 'Ваш email', 'address' => 'Баку, Азербайджан', 'contact_subtitle' => 'Свяжитесь с нами', 'contact_body' => 'Отправьте нам вопросы и предложения. Наша команда ответит в ближайшее время.', 'about_subtitle' => 'Профессиональная медицинская академия', 'certificates_title' => 'Дипломы и сертификаты', 'certificates_subtitle' => 'Проверка документа', 'certificates_body' => 'Введите номер документа для онлайн-проверки статуса.',
            ]);
        }

        return $az;
    }
}
