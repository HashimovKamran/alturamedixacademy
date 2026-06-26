<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalizedPublicContentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (['en', 'ru', 'tr'] as $language) {
            $t = $this->translations($language);

            $this->settings($language, $t['settings'], $now);
            $this->menus($language, $t['menus'], $now);
            $this->pages($language, $t['pages'], $now);
            $this->sliders($language, $t['sliders'], $now);
            $this->homeStats($language, $t['stats'], $now);
            $this->categories($language, $t['categories'], $now);
            $this->articles($language, $t['articles'], $t['article_body'], $now);
            $this->trainings($language, $t['trainings'], $now);
            $this->features($language, $t['features'], $now);
            $this->blocks($language, $t['blocks'], $now);
            $this->partners($language, $t['partners'], $now);
            $this->ads($language, $t['ads'], $now);
            $this->gallery($language, $t['gallery'], $now);
        }
    }

    private function settings(string $language, array $settings, mixed $now): void
    {
        foreach ($settings as $key => $value) {
            DB::table('aa_settings')->updateOrInsert(
                ['lang_code' => $language, 'setting_key' => $key],
                ['setting_value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    private function menus(string $language, array $menus, mixed $now): void
    {
        foreach ($menus as $url => $title) {
            DB::table('aa_menus')->where('lang_code', $language)->where('url', $url)->update([
                'title' => $title,
                'updated_at' => $now,
            ]);
        }
    }

    private function pages(string $language, array $pages, mixed $now): void
    {
        foreach ($pages as $key => $page) {
            DB::table('aa_pages')->where('lang_code', $language)->where('page_key', $key)->update([
                'title' => $page['title'],
                'subtitle' => $page['subtitle'],
                'body' => $page['body'],
                'content' => $page['body'],
                'meta_description' => strip_tags($page['body']),
                'updated_at' => $now,
            ]);
        }
    }

    private function sliders(string $language, array $sliders, mixed $now): void
    {
        foreach ($sliders as $sortOrder => $slider) {
            DB::table('aa_sliders')->where('lang_code', $language)->where('sort_order', $sortOrder)->update($slider + [
                'updated_at' => $now,
            ]);
        }
    }

    private function homeStats(string $language, array $stats, mixed $now): void
    {
        foreach ($stats as $sortOrder => $title) {
            DB::table('aa_home_stats')->where('lang_code', $language)->where('sort_order', $sortOrder)->update([
                'title' => $title,
                'updated_at' => $now,
            ]);
        }
    }

    private function categories(string $language, array $categories, mixed $now): void
    {
        foreach ($categories as $slug => $title) {
            DB::table('aa_article_categories')->where('lang_code', $language)->where('slug', $slug)->update([
                'title' => $title,
                'updated_at' => $now,
            ]);
        }
    }

    private function articles(string $language, array $articles, string $body, mixed $now): void
    {
        foreach ($articles as $slug => $article) {
            DB::table('aa_articles')->where('lang_code', $language)->where('slug', $slug)->update([
                'title' => $article['title'],
                'excerpt' => $article['excerpt'],
                'short_description' => $article['excerpt'],
                'body' => $body,
                'content' => $body,
                'updated_at' => $now,
            ]);
        }
    }

    private function trainings(string $language, array $trainings, mixed $now): void
    {
        foreach ($trainings as $sortOrder => $training) {
            DB::table('aa_trainings')->where('lang_code', $language)->where('sort_order', $sortOrder)->update([
                'title' => $training['title'],
                'location' => $training['location'],
                'updated_at' => $now,
            ]);
        }
    }

    private function features(string $language, array $features, mixed $now): void
    {
        foreach ($features as $sortOrder => $feature) {
            DB::table('aa_features')->where('lang_code', $language)->where('sort_order', $sortOrder)->where('is_active', true)->update([
                'title' => $feature['title'],
                'description' => $feature['description'],
                'updated_at' => $now,
            ]);
        }
    }

    private function blocks(string $language, array $blocks, mixed $now): void
    {
        foreach ($blocks as $key => $block) {
            DB::table('aa_blocks')->where('lang_code', $language)->where('block_key', $key)->update($block + [
                'updated_at' => $now,
            ]);
        }
    }

    private function partners(string $language, array $partners, mixed $now): void
    {
        foreach ($partners as $sortOrder => $title) {
            DB::table('aa_partners')->where('lang_code', $language)->where('sort_order', $sortOrder)->where('is_active', true)->update([
                'title' => $title,
                'updated_at' => $now,
            ]);
        }
    }

    private function ads(string $language, array $ads, mixed $now): void
    {
        foreach ($ads as $key => $title) {
            [$position, $sortOrder] = explode(':', $key, 2);
            DB::table('aa_ads')->where('lang_code', $language)->where('position_key', $position)->where('sort_order', (int) $sortOrder)->where('is_active', true)->update([
                'title' => $title,
                'updated_at' => $now,
            ]);
        }
    }

    private function gallery(string $language, array $gallery, mixed $now): void
    {
        foreach ($gallery as $sortOrder => $item) {
            DB::table('aa_gallery')->where('lang_code', $language)->where('sort_order', $sortOrder)->update([
                'title' => $item['title'],
                'description' => $item['description'],
                'updated_at' => $now,
            ]);
        }
    }

    private function translations(string $language): array
    {
        return match ($language) {
            'ru' => $this->ru(),
            'tr' => $this->tr(),
            default => $this->en(),
        };
    }

    private function en(): array
    {
        return [
            'settings' => [
                'site_description' => 'A modern academic platform for clinical knowledge sharing, scientific education, and professional development.',
                'section_academic' => 'Academic Articles',
                'section_latest' => 'Latest Publications',
                'section_trainings' => 'Courses and Conferences',
                'section_trainings_all' => '',
                'section_features' => 'Academic Resources',
                'section_partners' => 'Our Partners',
                'section_partners_subtitle' => '',
                'all_view' => 'View all',
                'btn_register' => 'Register',
                'btn_login' => 'Sign in',
                'auth_profile_title' => 'Profile',
                'auth_logout' => 'Logout',
                'auth_login_title' => 'Sign in',
                'auth_login_subtitle' => 'Sign in to follow updates.',
                'auth_register_title' => 'Create an account',
                'auth_register_subtitle' => 'Join the platform and stay informed about updates.',
                'auth_email' => 'Email account',
                'auth_password' => 'Password',
                'auth_login_button' => 'Sign in',
                'auth_register_button' => 'Create account',
                'footer_about' => 'A modern academic platform for scientific knowledge, clinical practice, and professional development.',
                'footer_links' => 'Quick links',
                'footer_contact' => 'Contact',
                'footer_newsletter' => 'Newsletter',
                'newsletter_text' => 'Subscribe to receive updates and academic articles.',
                'newsletter_placeholder' => 'Your email address',
                'contact_address' => 'Baku, Azerbaijan',
                'home_map_title' => 'Location map',
                'home_map_subtitle' => 'Find us on the map',
                'ad_label' => 'Ad',
                'ad_default' => 'Your ad could be here',
            ],
            'menus' => [
                '/' => 'Home',
                '/about' => 'About',
                '/articles' => 'Academic Articles',
                '/certificates' => 'Diplomas and Certificates',
                '/trainings' => 'Courses',
                '/gallery' => 'Gallery',
                '/contact' => 'Contact',
            ],
            'pages' => [
                'about' => [
                    'title' => 'About',
                    'subtitle' => 'About Alturamedix Academy',
                    'body' => '<p>Alturamedix Academy is a modern academic platform created for medical education, clinical knowledge sharing, and professional development.</p><p>Our goal is to make high-quality knowledge in emergency and critical care more accessible to physicians, healthcare professionals, and students.</p>',
                ],
                'contact' => [
                    'title' => 'Contact',
                    'subtitle' => 'Get in touch with us',
                    'body' => '<p>If you have a question, proposal, or collaboration request, you can contact us using the form below.</p>',
                ],
                'certificates' => [
                    'title' => 'Diplomas and Certificates',
                    'subtitle' => 'Document verification',
                    'body' => 'Enter a document number to verify its status online.',
                ],
            ],
            'sliders' => [
                1 => ['description' => 'A modern academic platform for clinical knowledge sharing, scientific education, and professional development.', 'button_1_text' => 'View Courses', 'button_2_text' => 'Create an account'],
                2 => ['title' => 'Simulation-based courses', 'subtitle' => 'Strengthen practical skills with real scenarios', 'description' => 'Practical training programs for emergency and critical medical decision-making.', 'button_1_text' => 'Academic Articles', 'button_2_text' => 'Diplomas and Certificates'],
                3 => ['title' => 'Academic development ecosystem', 'subtitle' => 'Articles, certificates, and conferences', 'description' => 'A digital academy that makes professional development measurable and continuous.', 'button_1_text' => 'Contact', 'button_2_text' => 'Gallery'],
            ],
            'stats' => [1 => 'Training programs', 2 => 'Trainees and participants', 3 => 'International partners'],
            'categories' => [
                'tecili-yardim' => 'Emergency Care',
                'kritik-baxim' => 'Critical Care',
                'telim-materiallari' => 'Training Materials',
                'emergency-care' => 'Emergency Care',
                'critical-care' => 'Critical Care',
                'clinical-skills' => 'Clinical Skills',
                'training-materials' => 'Training Materials',
                'reanimasiya' => 'Resuscitation',
                'tecili-kardiologiya' => 'Emergency Cardiology',
                'travma' => 'Trauma',
                'tecili-nevrologiya' => 'Emergency Neurology',
                'toksikologiya' => 'Toxicology',
                'intensiv-terapiya' => 'Intensive Care',
            ],
            'articles' => [
                'klinik-qerarverme' => ['title' => 'A Systematic Approach to Clinical Decision-Making', 'excerpt' => 'Practical approaches for fast and well-grounded decisions in emergency care teams.'],
                'airway-management' => ['title' => 'The First 5 Minutes in Airway Safety', 'excerpt' => 'Systematic assessment and proper role distribution in emergency situations.'],
                'sepsis-bundle' => ['title' => 'A Practical Approach to the Sepsis Protocol', 'excerpt' => 'A short clinical guide to early recognition, fluid strategy, and monitoring.'],
                'team-communication' => ['title' => 'Effective Communication in Critical Teams', 'excerpt' => 'SBAR model and closed-loop communication principles.'],
                'simulation-design' => ['title' => 'How to Prepare a Simulation Scenario', 'excerpt' => 'Training goals, measurable outcomes, and debriefing structure.'],
                'trauma-checklist' => ['title' => 'Rapid Checklist for Trauma Patients', 'excerpt' => 'A practical list for primary survey and priority interventions.'],
                'ventilation-basics' => ['title' => 'Introduction to Mechanical Ventilation', 'excerpt' => 'Core parameters and recommendations for a safe start.'],
                'ecg-fast-read' => ['title' => 'Quick Recognition of Dangerous ECG Rhythms', 'excerpt' => 'A concise visual approach to clinically risky rhythms.'],
                'learning-pathway' => ['title' => 'How to Build a Personal Learning Pathway', 'excerpt' => 'A staged and trackable academic plan for professional development.'],
                'kardiopulmonar-reanimasiya' => ['title' => 'Updated Approaches in Cardiopulmonary Resuscitation', 'excerpt' => 'Initial assessment and high-quality compression principles for resuscitation teams.'],
                'travma-pasiyentine-ilkin-yanasma' => ['title' => 'Initial Approach Principles for Trauma Patients', 'excerpt' => 'Rapid decision-making and priority interventions in trauma patients.'],
                'hava-yolu-idareetmesi' => ['title' => 'Difficult Airway Management', 'excerpt' => 'Practical approaches for safe airway management in emergency settings.'],
                'ekq-interpretasiyasi' => ['title' => 'Critical Points in ECG Interpretation', 'excerpt' => 'Rapid recognition of dangerous rhythms and clinical decision-making.'],
                'zeherlenmelerde-ilk-yardim' => ['title' => 'First Aid in Poisoning Cases', 'excerpt' => 'Initial assessment and safety steps in toxicological cases.'],
                'kritik-bakim-monitorinq' => ['title' => 'Monitoring in Critical Care', 'excerpt' => 'Dynamic monitoring and early warning indicators in intensive care.'],
                'kardiopulmonar-reanimasiyada-yenilenmis-yanasmalar' => ['title' => 'Updated Approaches in Cardiopulmonary Resuscitation', 'excerpt' => 'Initial assessment and high-quality compression principles for resuscitation teams.'],
                'travma-pasiyentinin-ilkin-yanasma-prinsipleri' => ['title' => 'Initial Approach Principles for Trauma Patients', 'excerpt' => 'Rapid decision-making and priority interventions in trauma patients.'],
                'cetin-hava-yolu-idareetmesi' => ['title' => 'Difficult Airway Management', 'excerpt' => 'Practical approaches for safe airway management in emergency settings.'],
                'ekq-interpretasiyasinda-kritik-meqamlar' => ['title' => 'Critical Points in ECG Interpretation', 'excerpt' => 'Rapid recognition of dangerous rhythms and clinical decision-making.'],
            ],
            'article_body' => '<p>This material explains a practical, measurable, and team-oriented approach to clinical decision-making.</p><p>It presents the main steps, risk points, and a short checklist that can be used during training.</p>',
            'trainings' => [
                1 => ['title' => 'ACLS Provider Course', 'location' => 'Baku, Azerbaijan'],
                2 => ['title' => 'PHTLS Provider Course', 'location' => 'Baku, Azerbaijan'],
                3 => ['title' => 'Advanced Airway Management', 'location' => 'Baku, Azerbaijan'],
            ],
            'features' => [
                1 => ['title' => 'Scientific Articles', 'description' => 'A collection of academic research and articles'],
                2 => ['title' => 'Congress Archive', 'description' => 'Past conference and congress materials'],
                3 => ['title' => 'Video Tutorials', 'description' => 'Video materials for practical skills'],
                4 => ['title' => 'Training Materials', 'description' => 'Downloadable textbooks and teaching resources'],
                5 => ['title' => 'Bibliographic Databases', 'description' => 'Useful links and scientific source databases'],
            ],
            'blocks' => [
                'journal' => ['body' => 'Read scientific articles in emergency and critical care.', 'button_text' => 'Go to journal'],
                'mobile_app' => ['title' => 'ALTURAMEDIX MOBILE APP', 'subtitle' => 'NEW', 'body' => 'Our mobile app for easier access to courses and notifications is coming soon.'],
                'support' => ['title' => 'Have a question? Contact us.', 'body' => 'Our team is ready to help you.', 'button_text' => 'Contact us'],
            ],
            'partners' => [1 => 'Senan Aghayev', 2 => 'Baku Medical Plaza', 3 => 'Tornado Security Service', 4 => 'EUSEM', 5 => 'AHA Training Center'],
            'ads' => ['sidebar:1' => 'Your ad could be here', 'sidebar:2' => 'Your ad could be here', 'bottom:1' => 'Your ad could be here'],
            'gallery' => [
                1 => ['title' => 'Practical training day', 'description' => 'A view from Alturamedix Academy events.'],
                2 => ['title' => 'Simulation laboratory', 'description' => 'A view from Alturamedix Academy events.'],
                3 => ['title' => 'Academic conference', 'description' => 'A view from Alturamedix Academy events.'],
                4 => ['title' => 'Clinical teamwork', 'description' => 'A view from Alturamedix Academy events.'],
                5 => ['title' => 'Emergency care scenario', 'description' => 'A view from Alturamedix Academy events.'],
                6 => ['title' => 'Certificate presentation', 'description' => 'A view from Alturamedix Academy events.'],
            ],
        ];
    }

    private function tr(): array
    {
        return array_replace_recursive($this->en(), [
            'settings' => [
                'site_description' => 'Klinik bilgi paylaşımı, bilimsel eğitim ve profesyonel gelişim için modern akademik platform.',
                'section_academic' => 'Akademik Yazılar',
                'section_latest' => 'Son Yayınlar',
                'section_trainings' => 'Kurslar ve Konferanslar',
                'section_features' => 'Akademik Kaynaklar',
                'section_partners' => 'Ortaklarımız',
                'all_view' => 'Tümünü görüntüle',
                'btn_register' => 'Kayıt ol',
                'btn_login' => 'Giriş yap',
                'auth_profile_title' => 'Profil',
                'auth_logout' => 'Çıkış',
                'auth_login_title' => 'Giriş yap',
                'auth_login_subtitle' => 'Güncellemeleri takip etmek için giriş yapın.',
                'auth_register_title' => 'Hesap oluştur',
                'auth_register_subtitle' => 'Platforma katılın ve güncellemelerden haberdar olun.',
                'auth_email' => 'Email hesabı',
                'auth_password' => 'Şifre',
                'auth_login_button' => 'Giriş yap',
                'auth_register_button' => 'Hesap oluştur',
                'footer_about' => 'Bilimsel bilgi, klinik uygulama ve profesyonel gelişim için modern akademik platform.',
                'footer_links' => 'Hızlı bağlantılar',
                'footer_contact' => 'İletişim',
                'footer_newsletter' => 'Bülten',
                'newsletter_text' => 'Güncellemeler ve akademik yazılar hakkında bilgi almak için abone olun.',
                'newsletter_placeholder' => 'Email adresiniz',
                'contact_address' => 'Bakü, Azerbaycan',
                'home_map_title' => 'Adres haritası',
                'home_map_subtitle' => 'Bizi haritada bulun',
                'ad_label' => 'Reklam',
                'ad_default' => 'Reklamınız burada yer alabilir',
            ],
            'menus' => [
                '/' => 'Ana sayfa',
                '/about' => 'Hakkımızda',
                '/articles' => 'Akademik Yazılar',
                '/certificates' => 'Diploma ve Sertifikalar',
                '/trainings' => 'Kurslar',
                '/gallery' => 'Galeri',
                '/contact' => 'İletişim',
            ],
            'pages' => [
                'about' => [
                    'title' => 'Hakkımızda',
                    'subtitle' => 'Alturamedix Academy hakkında',
                    'body' => '<p>Alturamedix Academy tıp eğitimi, klinik bilgi paylaşımı ve profesyonel gelişim için oluşturulmuş modern bir akademik platformdur.</p><p>Amacımız acil ve kritik tıp alanındaki kaliteli bilgileri hekimlere, sağlık çalışanlarına ve öğrencilere daha erişilebilir kılmaktır.</p>',
                ],
                'contact' => [
                    'title' => 'İletişim',
                    'subtitle' => 'Bizimle iletişime geçin',
                    'body' => '<p>Sorunuz, öneriniz veya iş birliği talebiniz varsa aşağıdaki form aracılığıyla bizimle iletişime geçebilirsiniz.</p>',
                ],
                'certificates' => [
                    'title' => 'Diploma ve Sertifikalar',
                    'subtitle' => 'Belge doğrulama',
                    'body' => 'Belge numarasını girerek durumunu çevrimiçi doğrulayabilirsiniz.',
                ],
            ],
            'sliders' => [
                1 => ['description' => 'Klinik bilgi paylaşımı, bilimsel eğitim ve profesyonel gelişim için modern akademik platform.', 'button_1_text' => 'Kursları görüntüle', 'button_2_text' => 'Hesap oluştur'],
                2 => ['title' => 'Simülasyon temelli kurslar', 'subtitle' => 'Pratik becerileri gerçek senaryolarla güçlendirin', 'description' => 'Acil ve kritik tıbbi karar verme için uygulamalı eğitim programları.', 'button_1_text' => 'Akademik Yazılar', 'button_2_text' => 'Diploma ve Sertifikalar'],
                3 => ['title' => 'Akademik gelişim ekosistemi', 'subtitle' => 'Yazılar, sertifikalar ve konferanslar', 'description' => 'Profesyonel gelişimi ölçülebilir ve sürekli hale getiren dijital akademi.', 'button_1_text' => 'İletişim', 'button_2_text' => 'Galeri'],
            ],
            'stats' => [1 => 'Eğitim programları', 2 => 'Katılımcılar', 3 => 'Uluslararası ortaklar'],
            'categories' => [
                'tecili-yardim' => 'Acil Yardım',
                'kritik-baxim' => 'Kritik Bakım',
                'telim-materiallari' => 'Eğitim Materyalleri',
                'emergency-care' => 'Acil Yardım',
                'critical-care' => 'Kritik Bakım',
                'clinical-skills' => 'Klinik Beceriler',
                'training-materials' => 'Eğitim Materyalleri',
                'reanimasiya' => 'Resüsitasyon',
                'tecili-kardiologiya' => 'Acil Kardiyoloji',
                'travma' => 'Travma',
                'tecili-nevrologiya' => 'Acil Nöroloji',
                'toksikologiya' => 'Toksikoloji',
                'intensiv-terapiya' => 'Yoğun Bakım',
            ],
            'articles' => [
                'klinik-qerarverme' => ['title' => 'Klinik Karar Vermeye Sistematik Yaklaşım', 'excerpt' => 'Acil bakım ekipleri için hızlı ve temellendirilmiş karar süreçleri.'],
                'airway-management' => ['title' => 'Hava Yolu Güvenliğinde İlk 5 Dakika', 'excerpt' => 'Acil durumlarda sistematik değerlendirme ve doğru rol dağılımı.'],
                'sepsis-bundle' => ['title' => 'Sepsis Protokolüne Pratik Yaklaşım', 'excerpt' => 'Erken tanı, sıvı stratejisi ve izlem için kısa klinik rehber.'],
                'team-communication' => ['title' => 'Kritik Ekiplerde Etkili İletişim', 'excerpt' => 'SBAR modeli ve kapalı döngü iletişim ilkeleri.'],
                'simulation-design' => ['title' => 'Simülasyon Senaryosu Nasıl Hazırlanır?', 'excerpt' => 'Eğitim hedefleri, ölçülebilir çıktılar ve debriefing yapısı.'],
                'trauma-checklist' => ['title' => 'Travma Hastaları İçin Hızlı Kontrol Listesi', 'excerpt' => 'Birincil değerlendirme ve öncelikli müdahaleler için pratik liste.'],
                'ventilation-basics' => ['title' => 'Mekanik Ventilasyona Giriş', 'excerpt' => 'Güvenli başlangıç için temel parametreler ve öneriler.'],
                'ecg-fast-read' => ['title' => 'Tehlikeli EKG Ritimlerini Hızlı Tanıma', 'excerpt' => 'Klinik risk taşıyan ritimler için kısa görsel yaklaşım.'],
                'learning-pathway' => ['title' => 'Kişisel Öğrenme Yolu Nasıl Oluşturulur?', 'excerpt' => 'Profesyonel gelişim için aşamalı ve izlenebilir akademik plan.'],
                'kardiopulmonar-reanimasiya' => ['title' => 'Kardiyopulmoner Resüsitasyonda Güncel Yaklaşımlar', 'excerpt' => 'Resüsitasyon ekipleri için ilk değerlendirme ve kaliteli kompresyon ilkeleri.'],
                'travma-pasiyentine-ilkin-yanasma' => ['title' => 'Travma Hastasına İlk Yaklaşım İlkeleri', 'excerpt' => 'Travma hastalarında hızlı karar verme ve öncelikli müdahaleler.'],
                'hava-yolu-idareetmesi' => ['title' => 'Zor Hava Yolu Yönetimi', 'excerpt' => 'Acil koşullarda güvenli hava yolu yönetimi için pratik yaklaşımlar.'],
                'ekq-interpretasiyasi' => ['title' => 'EKG Yorumlamasında Kritik Noktalar', 'excerpt' => 'Tehlikeli ritimlerin hızlı tanınması ve klinik karar verme.'],
                'zeherlenmelerde-ilk-yardim' => ['title' => 'Zehirlenmelerde İlk Yardım', 'excerpt' => 'Toksikolojik olgularda ilk değerlendirme ve güvenlik adımları.'],
                'kritik-bakim-monitorinq' => ['title' => 'Kritik Bakımda İzlem', 'excerpt' => 'Yoğun bakımda dinamik izlem ve erken uyarı göstergeleri.'],
                'kardiopulmonar-reanimasiyada-yenilenmis-yanasmalar' => ['title' => 'Kardiyopulmoner Resüsitasyonda Güncel Yaklaşımlar', 'excerpt' => 'Resüsitasyon ekipleri için ilk değerlendirme ve kaliteli kompresyon ilkeleri.'],
                'travma-pasiyentinin-ilkin-yanasma-prinsipleri' => ['title' => 'Travma Hastasına İlk Yaklaşım İlkeleri', 'excerpt' => 'Travma hastalarında hızlı karar verme ve öncelikli müdahaleler.'],
                'cetin-hava-yolu-idareetmesi' => ['title' => 'Zor Hava Yolu Yönetimi', 'excerpt' => 'Acil koşullarda güvenli hava yolu yönetimi için pratik yaklaşımlar.'],
                'ekq-interpretasiyasinda-kritik-meqamlar' => ['title' => 'EKG Yorumlamasında Kritik Noktalar', 'excerpt' => 'Tehlikeli ritimlerin hızlı tanınması ve klinik karar verme.'],
            ],
            'article_body' => '<p>Bu materyal klinik karar verme sürecinde pratik, ölçülebilir ve ekip odaklı bir yaklaşımı açıklar.</p><p>Temel adımlar, risk noktaları ve eğitim sırasında kullanılabilecek kısa bir kontrol listesi sunar.</p>',
            'trainings' => [
                1 => ['title' => 'ACLS Provider Kursu', 'location' => 'Bakü, Azerbaycan'],
                2 => ['title' => 'PHTLS Provider Kursu', 'location' => 'Bakü, Azerbaycan'],
                3 => ['title' => 'İleri Hava Yolu Yönetimi', 'location' => 'Bakü, Azerbaycan'],
            ],
            'features' => [
                1 => ['title' => 'Bilimsel Yazılar', 'description' => 'Akademik araştırma ve yazı koleksiyonu'],
                2 => ['title' => 'Kongre Arşivi', 'description' => 'Geçmiş konferans ve kongre materyalleri'],
                3 => ['title' => 'Video Eğitimler', 'description' => 'Pratik beceriler için video materyalleri'],
                4 => ['title' => 'Eğitim Materyalleri', 'description' => 'İndirilebilir ders kitapları ve eğitim kaynakları'],
                5 => ['title' => 'Bibliyografik Veritabanları', 'description' => 'Faydalı bağlantılar ve bilimsel kaynak veritabanları'],
            ],
            'blocks' => [
                'journal' => ['body' => 'Acil ve kritik tıp alanında bilimsel yazıları okuyun.', 'button_text' => 'Dergiye geç'],
                'mobile_app' => ['title' => 'ALTURAMEDIX MOBİL UYGULAMA', 'subtitle' => 'YENİ', 'body' => 'Kurslara ve bildirimlere daha kolay erişim için mobil uygulamamız yakında.'],
                'support' => ['title' => 'Sorunuz mu var? Bizimle iletişime geçin.', 'body' => 'Ekibimiz size yardımcı olmaya hazır.', 'button_text' => 'İletişim'],
            ],
            'ads' => ['sidebar:1' => 'Reklamınız burada yer alabilir', 'sidebar:2' => 'Reklamınız burada yer alabilir', 'bottom:1' => 'Reklamınız burada yer alabilir'],
            'gallery' => [
                1 => ['title' => 'Pratik eğitim günü', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
                2 => ['title' => 'Simülasyon laboratuvarı', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
                3 => ['title' => 'Akademik konferans', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
                4 => ['title' => 'Klinik ekip çalışması', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
                5 => ['title' => 'Acil bakım senaryosu', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
                6 => ['title' => 'Sertifika sunumu', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
            ],
        ]);
    }

    private function ru(): array
    {
        return [
            'settings' => [
                'site_description' => 'Современная академическая платформа для обмена клиническими знаниями, научного обучения и профессионального развития.',
                'section_academic' => 'Академические статьи',
                'section_latest' => 'Последние публикации',
                'section_trainings' => 'Курсы и конференции',
                'section_trainings_all' => '',
                'section_features' => 'Академические ресурсы',
                'section_partners' => 'Наши партнеры',
                'section_partners_subtitle' => '',
                'all_view' => 'Смотреть все',
                'btn_register' => 'Регистрация',
                'btn_login' => 'Войти',
                'auth_profile_title' => 'Профиль',
                'auth_logout' => 'Выйти',
                'auth_login_title' => 'Войти',
                'auth_login_subtitle' => 'Войдите, чтобы следить за обновлениями.',
                'auth_register_title' => 'Создать аккаунт',
                'auth_register_subtitle' => 'Присоединяйтесь к платформе и следите за обновлениями.',
                'auth_email' => 'Email',
                'auth_password' => 'Пароль',
                'auth_login_button' => 'Войти',
                'auth_register_button' => 'Создать аккаунт',
                'footer_about' => 'Современная академическая платформа для научных знаний, клинической практики и профессионального развития.',
                'footer_links' => 'Быстрые ссылки',
                'footer_contact' => 'Контакты',
                'footer_newsletter' => 'Рассылка',
                'newsletter_text' => 'Подпишитесь, чтобы получать обновления и академические статьи.',
                'newsletter_placeholder' => 'Ваш email',
                'contact_address' => 'Баку, Азербайджан',
                'home_map_title' => 'Карта адреса',
                'home_map_subtitle' => 'Найдите нас на карте',
                'ad_label' => 'Реклама',
                'ad_default' => 'Здесь может быть ваша реклама',
            ],
            'menus' => [
                '/' => 'Главная',
                '/about' => 'О нас',
                '/articles' => 'Академические статьи',
                '/certificates' => 'Дипломы и сертификаты',
                '/trainings' => 'Курсы',
                '/gallery' => 'Галерея',
                '/contact' => 'Контакты',
            ],
            'pages' => [
                'about' => [
                    'title' => 'О нас',
                    'subtitle' => 'Об Alturamedix Academy',
                    'body' => '<p>Alturamedix Academy — современная академическая платформа, созданная для медицинского образования, обмена клиническими знаниями и профессионального развития.</p><p>Наша цель — сделать качественные знания в области неотложной и критической медицины более доступными для врачей, медицинских специалистов и студентов.</p>',
                ],
                'contact' => [
                    'title' => 'Контакты',
                    'subtitle' => 'Свяжитесь с нами',
                    'body' => '<p>Если у вас есть вопрос, предложение или запрос о сотрудничестве, вы можете связаться с нами через форму ниже.</p>',
                ],
                'certificates' => [
                    'title' => 'Дипломы и сертификаты',
                    'subtitle' => 'Проверка документа',
                    'body' => 'Введите номер документа, чтобы проверить его статус онлайн.',
                ],
            ],
            'sliders' => [
                1 => ['description' => 'Современная академическая платформа для обмена клиническими знаниями, научного обучения и профессионального развития.', 'button_1_text' => 'Посмотреть курсы', 'button_2_text' => 'Создать аккаунт'],
                2 => ['title' => 'Курсы на основе симуляции', 'subtitle' => 'Укрепляйте практические навыки на реальных сценариях', 'description' => 'Практические учебные программы для принятия решений в неотложной и критической медицине.', 'button_1_text' => 'Академические статьи', 'button_2_text' => 'Дипломы и сертификаты'],
                3 => ['title' => 'Экосистема академического развития', 'subtitle' => 'Статьи, сертификаты и конференции', 'description' => 'Цифровая академия, делающая профессиональное развитие измеримым и непрерывным.', 'button_1_text' => 'Контакты', 'button_2_text' => 'Галерея'],
            ],
            'stats' => [1 => 'Учебные программы', 2 => 'Слушатели и участники', 3 => 'Международные партнеры'],
            'categories' => [
                'tecili-yardim' => 'Неотложная помощь',
                'kritik-baxim' => 'Критическая помощь',
                'telim-materiallari' => 'Учебные материалы',
                'emergency-care' => 'Неотложная помощь',
                'critical-care' => 'Критическая помощь',
                'clinical-skills' => 'Клинические навыки',
                'training-materials' => 'Учебные материалы',
                'reanimasiya' => 'Реанимация',
                'tecili-kardiologiya' => 'Неотложная кардиология',
                'travma' => 'Травма',
                'tecili-nevrologiya' => 'Неотложная неврология',
                'toksikologiya' => 'Токсикология',
                'intensiv-terapiya' => 'Интенсивная терапия',
            ],
            'articles' => [
                'klinik-qerarverme' => ['title' => 'Системный подход к клиническим решениям', 'excerpt' => 'Практические подходы для быстрых и обоснованных решений в командах неотложной помощи.'],
                'airway-management' => ['title' => 'Первые 5 минут безопасности дыхательных путей', 'excerpt' => 'Системная оценка и правильное распределение ролей в экстренной ситуации.'],
                'sepsis-bundle' => ['title' => 'Практический подход к протоколу сепсиса', 'excerpt' => 'Краткое клиническое руководство по раннему распознаванию, инфузионной стратегии и мониторингу.'],
                'team-communication' => ['title' => 'Эффективная коммуникация в критической команде', 'excerpt' => 'Модель SBAR и принципы замкнутой коммуникации.'],
                'simulation-design' => ['title' => 'Как подготовить симуляционный сценарий', 'excerpt' => 'Цели обучения, измеримые результаты и структура дебрифинга.'],
                'trauma-checklist' => ['title' => 'Быстрый чеклист для пациента с травмой', 'excerpt' => 'Практический список для первичного осмотра и приоритетных вмешательств.'],
                'ventilation-basics' => ['title' => 'Введение в механическую вентиляцию', 'excerpt' => 'Основные параметры и рекомендации для безопасного старта.'],
                'ecg-fast-read' => ['title' => 'Быстрое распознавание опасных ритмов на ЭКГ', 'excerpt' => 'Краткий визуальный подход к клинически рискованным ритмам.'],
                'learning-pathway' => ['title' => 'Как построить индивидуальную траекторию обучения', 'excerpt' => 'Поэтапный и отслеживаемый академический план профессионального развития.'],
                'kardiopulmonar-reanimasiya' => ['title' => 'Обновленные подходы в сердечно-легочной реанимации', 'excerpt' => 'Первичная оценка и принципы качественных компрессий для реанимационной команды.'],
                'travma-pasiyentine-ilkin-yanasma' => ['title' => 'Принципы первичного подхода к пациенту с травмой', 'excerpt' => 'Быстрое принятие решений и приоритетные вмешательства при травме.'],
                'hava-yolu-idareetmesi' => ['title' => 'Управление сложными дыхательными путями', 'excerpt' => 'Практические подходы к безопасному обеспечению дыхательных путей в экстренной ситуации.'],
                'ekq-interpretasiyasi' => ['title' => 'Критические моменты интерпретации ЭКГ', 'excerpt' => 'Быстрое распознавание опасных ритмов и клиническое принятие решений.'],
                'zeherlenmelerde-ilk-yardim' => ['title' => 'Первая помощь при отравлениях', 'excerpt' => 'Первичная оценка и меры безопасности при токсикологических случаях.'],
                'kritik-bakim-monitorinq' => ['title' => 'Мониторинг в критической помощи', 'excerpt' => 'Динамическое наблюдение и ранние предупреждающие показатели в интенсивной терапии.'],
                'kardiopulmonar-reanimasiyada-yenilenmis-yanasmalar' => ['title' => 'Обновленные подходы в сердечно-легочной реанимации', 'excerpt' => 'Первичная оценка и принципы качественных компрессий для реанимационной команды.'],
                'travma-pasiyentinin-ilkin-yanasma-prinsipleri' => ['title' => 'Принципы первичного подхода к пациенту с травмой', 'excerpt' => 'Быстрое принятие решений и приоритетные вмешательства при травме.'],
                'cetin-hava-yolu-idareetmesi' => ['title' => 'Управление сложными дыхательными путями', 'excerpt' => 'Практические подходы к безопасному обеспечению дыхательных путей в экстренной ситуации.'],
                'ekq-interpretasiyasinda-kritik-meqamlar' => ['title' => 'Критические моменты интерпретации ЭКГ', 'excerpt' => 'Быстрое распознавание опасных ритмов и клиническое принятие решений.'],
            ],
            'article_body' => '<p>Этот материал объясняет практический, измеримый и командно-ориентированный подход к клиническому принятию решений.</p><p>В нем представлены основные шаги, точки риска и короткий чеклист, который можно использовать во время обучения.</p>',
            'trainings' => [
                1 => ['title' => 'Курс ACLS Provider', 'location' => 'Баку, Азербайджан'],
                2 => ['title' => 'Курс PHTLS Provider', 'location' => 'Баку, Азербайджан'],
                3 => ['title' => 'Advanced Airway Management', 'location' => 'Баку, Азербайджан'],
            ],
            'features' => [
                1 => ['title' => 'Научные статьи', 'description' => 'Коллекция академических исследований и статей'],
                2 => ['title' => 'Архив конгрессов', 'description' => 'Материалы прошедших конференций и конгрессов'],
                3 => ['title' => 'Видеоуроки', 'description' => 'Видеоматериалы для практических навыков'],
                4 => ['title' => 'Учебные материалы', 'description' => 'Загружаемые учебники и учебные ресурсы'],
                5 => ['title' => 'Библиографические базы', 'description' => 'Полезные ссылки и научные базы источников'],
            ],
            'blocks' => [
                'journal' => ['body' => 'Читайте научные статьи в области неотложной и критической медицины.', 'button_text' => 'Перейти в журнал'],
                'mobile_app' => ['title' => 'МОБИЛЬНОЕ ПРИЛОЖЕНИЕ ALTURAMEDIX', 'subtitle' => 'НОВОЕ', 'body' => 'Наше мобильное приложение для удобного доступа к курсам и уведомлениям скоро будет доступно.'],
                'support' => ['title' => 'Есть вопрос? Свяжитесь с нами.', 'body' => 'Наша команда готова помочь вам.', 'button_text' => 'Связаться'],
            ],
            'partners' => [1 => 'Сенан Агаев', 2 => 'Baku Medical Plaza', 3 => 'Охранная служба Tornado', 4 => 'EUSEM', 5 => 'AHA Training Center'],
            'ads' => ['sidebar:1' => 'Здесь может быть ваша реклама', 'sidebar:2' => 'Здесь может быть ваша реклама', 'bottom:1' => 'Здесь может быть ваша реклама'],
            'gallery' => [
                1 => ['title' => 'День практического обучения', 'description' => 'Кадр с мероприятий Alturamedix Academy.'],
                2 => ['title' => 'Симуляционная лаборатория', 'description' => 'Кадр с мероприятий Alturamedix Academy.'],
                3 => ['title' => 'Академическая конференция', 'description' => 'Кадр с мероприятий Alturamedix Academy.'],
                4 => ['title' => 'Работа клинической команды', 'description' => 'Кадр с мероприятий Alturamedix Academy.'],
                5 => ['title' => 'Сценарий неотложной помощи', 'description' => 'Кадр с мероприятий Alturamedix Academy.'],
                6 => ['title' => 'Вручение сертификатов', 'description' => 'Кадр с мероприятий Alturamedix Academy.'],
            ],
        ];
    }
}
