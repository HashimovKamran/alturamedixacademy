<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $this->settings($now);
        $this->menus($now);
        $this->pages($now);
        $this->sliders($now);
        $this->stats($now);
        $this->categories($now);
        $this->articles($now);
        $this->trainings($now);
        $this->features($now);
        $this->blocks($now);
        $this->adsAndGallery($now);
    }

    public function down(): void
    {
        // Public kontent istifadəçi tərəfindən redaktə oluna bilər, rollback zamanı silinmir.
    }

    private function settings(mixed $now): void
    {
        foreach ([
            'site_description' => 'Klinik bilgi paylaşımı, bilimsel eğitim ve profesyonel gelişim için modern akademik platform.',
            'section_academic' => 'Akademik Yazılar',
            'section_latest' => 'Son Yayınlar',
            'section_trainings' => 'Kurslar ve Konferanslar',
            'section_trainings_all' => '',
            'section_features' => 'Akademik Kaynaklar',
            'section_partners' => 'Ortaklarımız',
            'section_partners_subtitle' => '',
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
        ] as $key => $value) {
            DB::table('aa_settings')->updateOrInsert(
                ['lang_code' => 'tr', 'setting_key' => $key],
                ['setting_value' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    private function menus(mixed $now): void
    {
        $this->updateBy('aa_menus', 'url', [
            '/' => ['title' => 'Ana sayfa'],
            '/about' => ['title' => 'Hakkımızda'],
            '/articles' => ['title' => 'Akademik Yazılar'],
            '/certificates' => ['title' => 'Diploma ve Sertifikalar'],
            '/trainings' => ['title' => 'Kurslar'],
            '/gallery' => ['title' => 'Galeri'],
            '/contact' => ['title' => 'İletişim'],
        ], $now);
    }

    private function pages(mixed $now): void
    {
        $about = '<p>Alturamedix Academy tıp eğitimi, klinik bilgi paylaşımı ve profesyonel gelişim için oluşturulmuş modern bir akademik platformdur.</p><p>Amacımız acil ve kritik tıp alanındaki kaliteli bilgileri hekimlere, sağlık çalışanlarına ve öğrencilere daha erişilebilir kılmaktır.</p>';
        $contact = '<p>Sorunuz, öneriniz veya iş birliği talebiniz varsa aşağıdaki form aracılığıyla bizimle iletişime geçebilirsiniz.</p>';

        $this->updateBy('aa_pages', 'page_key', [
            'about' => ['title' => 'Hakkımızda', 'subtitle' => 'Alturamedix Academy hakkında', 'body' => $about, 'content' => $about],
            'contact' => ['title' => 'İletişim', 'subtitle' => 'Bizimle iletişime geçin', 'body' => $contact, 'content' => $contact],
            'certificates' => ['title' => 'Diploma ve Sertifikalar', 'subtitle' => 'Belge doğrulama', 'body' => 'Belge numarasını girerek durumunu çevrimiçi doğrulayabilirsiniz.', 'content' => 'Belge numarasını girerek durumunu çevrimiçi doğrulayabilirsiniz.'],
        ], $now);
    }

    private function sliders(mixed $now): void
    {
        $this->updateBy('aa_sliders', 'sort_order', [
            1 => ['subtitle' => 'Academy of Emergency & Critical Care', 'description' => 'Klinik bilgi paylaşımı, bilimsel eğitim ve profesyonel gelişim için modern akademik platform.', 'button_1_text' => 'Kursları görüntüle', 'button_2_text' => 'Hesap oluştur'],
            2 => ['title' => 'Simülasyon temelli kurslar', 'subtitle' => 'Pratik becerileri gerçek senaryolarla güçlendirin', 'description' => 'Acil ve kritik tıbbi karar verme için uygulamalı eğitim programları.', 'button_1_text' => 'Akademik Yazılar', 'button_2_text' => 'Diploma ve Sertifikalar'],
            3 => ['title' => 'Akademik gelişim ekosistemi', 'subtitle' => 'Yazılar, sertifikalar ve konferanslar', 'description' => 'Profesyonel gelişimi ölçülebilir ve sürekli hale getiren dijital akademi.', 'button_1_text' => 'İletişim', 'button_2_text' => 'Galeri'],
        ], $now);
    }

    private function stats(mixed $now): void
    {
        $this->updateBy('aa_home_stats', 'sort_order', [
            1 => ['title' => 'Eğitim programları'],
            2 => ['title' => 'Katılımcılar'],
            3 => ['title' => 'Uluslararası ortaklar'],
            4 => ['title' => 'Belge doğrulama'],
        ], $now);
    }

    private function categories(mixed $now): void
    {
        $this->updateBy('aa_article_categories', 'slug', [
            'tecili-yardim' => ['title' => 'Acil Yardım'],
            'kritik-baxim' => ['title' => 'Kritik Bakım'],
            'telim-materiallari' => ['title' => 'Eğitim Materyalleri'],
            'emergency-care' => ['title' => 'Acil Yardım'],
            'critical-care' => ['title' => 'Kritik Bakım'],
            'clinical-skills' => ['title' => 'Klinik Beceriler'],
            'training-materials' => ['title' => 'Eğitim Materyalleri'],
            'reanimasiya' => ['title' => 'Resüsitasyon'],
            'tecili-kardiologiya' => ['title' => 'Acil Kardiyoloji'],
            'travma' => ['title' => 'Travma'],
            'tecili-nevrologiya' => ['title' => 'Acil Nöroloji'],
            'toksikologiya' => ['title' => 'Toksikoloji'],
            'intensiv-terapiya' => ['title' => 'Yoğun Bakım'],
        ], $now);
    }

    private function articles(mixed $now): void
    {
        $body = '<p>Bu materyal klinik karar verme sürecinde pratik, ölçülebilir ve ekip odaklı bir yaklaşımı açıklar.</p><p>Temel adımlar, risk noktaları ve eğitim sırasında kullanılabilecek kısa bir kontrol listesi sunar.</p>';

        $items = [
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
            'klinik-qerarverme' => ['title' => 'Klinik Karar Vermeye Sistematik Yaklaşım', 'excerpt' => 'Acil bakım ekipleri için hızlı ve temellendirilmiş karar süreçleri.'],
            'airway-management' => ['title' => 'Hava Yolu Güvenliğinde İlk 5 Dakika', 'excerpt' => 'Acil durumlarda sistematik değerlendirme ve doğru rol dağılımı.'],
            'sepsis-bundle' => ['title' => 'Sepsis Protokolüne Pratik Yaklaşım', 'excerpt' => 'Erken tanı, sıvı stratejisi ve izlem için kısa klinik rehber.'],
            'team-communication' => ['title' => 'Kritik Ekiplerde Etkili İletişim', 'excerpt' => 'SBAR modeli ve kapalı döngü iletişim ilkeleri.'],
            'simulation-design' => ['title' => 'Simülasyon Senaryosu Nasıl Hazırlanır?', 'excerpt' => 'Eğitim hedefleri, ölçülebilir çıktılar ve debriefing yapısı.'],
            'trauma-checklist' => ['title' => 'Travma Hastaları İçin Hızlı Kontrol Listesi', 'excerpt' => 'Birincil değerlendirme ve öncelikli müdahaleler için pratik liste.'],
            'ventilation-basics' => ['title' => 'Mekanik Ventilasyona Giriş', 'excerpt' => 'Güvenli başlangıç için temel parametreler ve öneriler.'],
            'ecg-fast-read' => ['title' => 'Tehlikeli EKG Ritimlerini Hızlı Tanıma', 'excerpt' => 'Klinik risk taşıyan ritimler için kısa görsel yaklaşım.'],
            'learning-pathway' => ['title' => 'Kişisel Öğrenme Yolu Nasıl Oluşturulur?', 'excerpt' => 'Profesyonel gelişim için aşamalı ve izlenebilir akademik plan.'],
        ];

        foreach ($items as $slug => $values) {
            $items[$slug] = array_merge($values, ['body' => $body, 'content' => $body, 'short_description' => $values['excerpt']]);
        }

        $this->updateBy('aa_articles', 'slug', $items, $now);
    }

    private function trainings(mixed $now): void
    {
        $this->updateBy('aa_trainings', 'sort_order', [
            1 => ['title' => 'ACLS Provider Kursu', 'location' => 'Bakü, Azerbaycan'],
            2 => ['title' => 'PHTLS Provider Kursu', 'location' => 'Bakü, Azerbaycan'],
            3 => ['title' => 'İleri Hava Yolu Yönetimi', 'location' => 'Bakü, Azerbaycan'],
            4 => ['title' => 'EKG hızlı klinik okuma', 'location' => 'Bakü, Azerbaycan'],
        ], $now);
    }

    private function features(mixed $now): void
    {
        $this->updateBy('aa_features', 'sort_order', [
            1 => ['title' => 'Bilimsel Yazılar', 'description' => 'Akademik araştırma ve yazı koleksiyonu'],
            2 => ['title' => 'Kongre Arşivi', 'description' => 'Geçmiş konferans ve kongre materyalleri'],
            3 => ['title' => 'Video Eğitimler', 'description' => 'Pratik beceriler için video materyalleri'],
            4 => ['title' => 'Eğitim Materyalleri', 'description' => 'İndirilebilir ders kitapları ve eğitim kaynakları'],
            5 => ['title' => 'Bibliyografik Veritabanları', 'description' => 'Faydalı bağlantılar ve bilimsel kaynak veritabanları'],
        ], $now);
    }

    private function blocks(mixed $now): void
    {
        $this->updateBy('aa_blocks', 'block_key', [
            'journal' => ['body' => 'Acil ve kritik tıp alanında bilimsel yazıları okuyun.', 'button_text' => 'Dergiye geç'],
            'mobile_app' => ['title' => 'ALTURAMEDIX MOBİL UYGULAMA', 'subtitle' => 'YENİ', 'body' => 'Kurslara ve bildirimlere daha kolay erişim için mobil uygulamamız yakında.'],
            'support' => ['title' => 'Sorunuz mu var? Bizimle iletişime geçin.', 'body' => 'Ekibimiz size yardımcı olmaya hazır.', 'button_text' => 'İletişim'],
        ], $now);
    }

    private function adsAndGallery(mixed $now): void
    {
        if (Schema::hasTable('aa_ads')) {
            DB::table('aa_ads')->where('lang_code', 'tr')->update(['title' => 'Reklamınız burada yer alabilir', 'updated_at' => $now]);
        }

        $this->updateBy('aa_gallery', 'sort_order', [
            1 => ['title' => 'Pratik eğitim günü', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
            2 => ['title' => 'Simülasyon laboratuvarı', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
            3 => ['title' => 'Akademik konferans', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
            4 => ['title' => 'Klinik ekip çalışması', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
            5 => ['title' => 'Acil bakım senaryosu', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
            6 => ['title' => 'Sertifika sunumu', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'],
        ], $now);
    }

    private function updateBy(string $table, string $column, array $rows, mixed $now): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'lang_code') || ! Schema::hasColumn($table, $column)) {
            return;
        }

        foreach ($rows as $key => $values) {
            DB::table($table)
                ->where('lang_code', 'tr')
                ->where($column, $key)
                ->update(array_merge($values, ['updated_at' => $now]));
        }
    }
};
