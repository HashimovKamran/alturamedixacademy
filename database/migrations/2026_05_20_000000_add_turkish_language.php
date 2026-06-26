<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $this->upsertLanguage($now);

        if ($this->hasPublicContent('tr')) {
            return;
        }

        foreach ([
            'aa_settings',
            'aa_pages',
            'aa_sliders',
            'aa_home_stats',
            'aa_trainings',
            'aa_features',
            'aa_blocks',
            'aa_partners',
            'aa_ads',
            'aa_gallery',
            'aa_page_builder_blocks',
            'aa_visual_edits',
            'aa_visual_blocks',
        ] as $table) {
            $this->cloneSimpleTable($table, $now);
        }

        $this->cloneMenus($now);
        $this->cloneArticles($now);
        $this->applyTurkishDefaults($now);
    }

    public function down(): void
    {
        // Dil datası istifadəçi tərəfindən redaktə oluna bilər, rollback zamanı silinmir.
    }

    private function upsertLanguage(mixed $now): void
    {
        if (! Schema::hasTable('aa_languages')) {
            return;
        }

        DB::table('aa_languages')->updateOrInsert(
            ['code' => 'tr'],
            [
                'title' => 'Türkçe',
                'native_name' => 'Türkçe',
                'locale' => 'tr_TR',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function hasPublicContent(string $language): bool
    {
        foreach (['aa_settings', 'aa_menus', 'aa_pages', 'aa_article_categories', 'aa_articles'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'lang_code')) {
                if (DB::table($table)->where('lang_code', $language)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function cloneSimpleTable(string $table, mixed $now): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'lang_code')) {
            return;
        }

        if (DB::table($table)->where('lang_code', 'tr')->exists()) {
            return;
        }

        foreach (DB::table($table)->where('lang_code', 'az')->orderBy('id')->get() as $row) {
            DB::table($table)->insert($this->cloneRow($table, $row, $now));
        }
    }

    private function cloneMenus(mixed $now): void
    {
        if (! Schema::hasTable('aa_menus') || ! Schema::hasColumn('aa_menus', 'lang_code')) {
            return;
        }

        if (DB::table('aa_menus')->where('lang_code', 'tr')->exists()) {
            return;
        }

        $map = [];
        $menus = DB::table('aa_menus')
            ->where('lang_code', 'az')
            ->orderByRaw('case when parent_id is null or parent_id = 0 then 0 else 1 end')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($menus as $menu) {
            $data = $this->cloneRow('aa_menus', $menu, $now);
            $data['parent_id'] = ! empty($menu->parent_id) && isset($map[(int) $menu->parent_id])
                ? $map[(int) $menu->parent_id]
                : null;

            $map[(int) $menu->id] = DB::table('aa_menus')->insertGetId($data);
        }
    }

    private function cloneArticles(mixed $now): void
    {
        if (! Schema::hasTable('aa_article_categories') || ! Schema::hasTable('aa_articles')) {
            return;
        }

        if (! DB::table('aa_article_categories')->where('lang_code', 'tr')->exists()) {
            $categoryMap = [];
            foreach (DB::table('aa_article_categories')->where('lang_code', 'az')->orderBy('sort_order')->orderBy('id')->get() as $category) {
                $categoryMap[(int) $category->id] = DB::table('aa_article_categories')->insertGetId($this->cloneRow('aa_article_categories', $category, $now));
            }
        } else {
            $categoryMap = DB::table('aa_article_categories as az')
                ->join('aa_article_categories as tr', 'tr.slug', '=', 'az.slug')
                ->where('az.lang_code', 'az')
                ->where('tr.lang_code', 'tr')
                ->pluck('tr.id', 'az.id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if (DB::table('aa_articles')->where('lang_code', 'tr')->exists()) {
            return;
        }

        foreach (DB::table('aa_articles')->where('lang_code', 'az')->orderBy('id')->get() as $article) {
            $data = $this->cloneRow('aa_articles', $article, $now);
            $data['category_id'] = ! empty($article->category_id) && isset($categoryMap[(int) $article->category_id])
                ? $categoryMap[(int) $article->category_id]
                : null;

            DB::table('aa_articles')->insert($data);
        }
    }

    private function cloneRow(string $table, object $row, mixed $now): array
    {
        $data = [];
        foreach (Schema::getColumnListing($table) as $column) {
            if ($column === 'id') {
                continue;
            }

            $data[$column] = $row->{$column} ?? null;
        }

        $data['lang_code'] = 'tr';

        if (array_key_exists('created_at', $data)) {
            $data['created_at'] = $now;
        }

        if (array_key_exists('updated_at', $data)) {
            $data['updated_at'] = $now;
        }

        return $data;
    }

    private function applyTurkishDefaults(mixed $now): void
    {
        $this->updateSettings($now);
        $this->updateMenus($now);
        $this->updatePages($now);
        $this->updateRowsBySlug('aa_article_categories', [
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

        $this->updateRowsBySlug('aa_articles', $this->articleTranslations(), $now);
        $this->updateRowsBySort('aa_home_stats', [1 => ['title' => 'Eğitim programları'], 2 => ['title' => 'Katılımcılar'], 3 => ['title' => 'Uluslararası ortaklar']], $now);
        $this->updateRowsBySort('aa_trainings', [1 => ['title' => 'ACLS Provider Kursu', 'location' => 'Bakü, Azerbaycan'], 2 => ['title' => 'PHTLS Provider Kursu', 'location' => 'Bakü, Azerbaycan'], 3 => ['title' => 'İleri Hava Yolu Yönetimi', 'location' => 'Bakü, Azerbaycan']], $now);
        $this->updateRowsBySort('aa_features', [1 => ['title' => 'Bilimsel Yazılar', 'description' => 'Akademik araştırma ve yazı koleksiyonu'], 2 => ['title' => 'Kongre Arşivi', 'description' => 'Geçmiş konferans ve kongre materyalleri'], 3 => ['title' => 'Video Eğitimler', 'description' => 'Pratik beceriler için video materyalleri'], 4 => ['title' => 'Eğitim Materyalleri', 'description' => 'İndirilebilir ders kitapları ve eğitim kaynakları'], 5 => ['title' => 'Bibliyografik Veritabanları', 'description' => 'Faydalı bağlantılar ve bilimsel kaynak veritabanları']], $now);
        $this->updateRowsByKey('aa_blocks', 'block_key', ['journal' => ['body' => 'Acil ve kritik tıp alanında bilimsel yazıları okuyun.', 'button_text' => 'Dergiye geç'], 'mobile_app' => ['title' => 'ALTURAMEDIX MOBİL UYGULAMA', 'subtitle' => 'YENİ', 'body' => 'Kurslara ve bildirimlere daha kolay erişim için mobil uygulamamız yakında.'], 'support' => ['title' => 'Sorunuz mu var? Bizimle iletişime geçin.', 'body' => 'Ekibimiz size yardımcı olmaya hazır.', 'button_text' => 'İletişim']], $now);
        $this->updateRowsBySort('aa_gallery', [1 => ['title' => 'Pratik eğitim günü', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'], 2 => ['title' => 'Simülasyon laboratuvarı', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'], 3 => ['title' => 'Akademik konferans', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'], 4 => ['title' => 'Klinik ekip çalışması', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'], 5 => ['title' => 'Acil bakım senaryosu', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.'], 6 => ['title' => 'Sertifika sunumu', 'description' => 'Alturamedix Academy etkinliklerinden bir görüntü.']], $now);
        $this->updateAds($now);
    }

    private function updateSettings(mixed $now): void
    {
        if (! Schema::hasTable('aa_settings')) {
            return;
        }

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
                ['setting_value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    private function updateMenus(mixed $now): void
    {
        $this->updateRowsByKey('aa_menus', 'url', [
            '/' => ['title' => 'Ana sayfa'],
            '/about' => ['title' => 'Hakkımızda'],
            '/articles' => ['title' => 'Akademik Yazılar'],
            '/certificates' => ['title' => 'Diploma ve Sertifikalar'],
            '/trainings' => ['title' => 'Kurslar'],
            '/gallery' => ['title' => 'Galeri'],
            '/contact' => ['title' => 'İletişim'],
        ], $now);
    }

    private function updatePages(mixed $now): void
    {
        $this->updateRowsByKey('aa_pages', 'page_key', [
            'about' => [
                'title' => 'Hakkımızda',
                'subtitle' => 'Alturamedix Academy hakkında',
                'body' => '<p>Alturamedix Academy tıp eğitimi, klinik bilgi paylaşımı ve profesyonel gelişim için oluşturulmuş modern bir akademik platformdur.</p><p>Amacımız acil ve kritik tıp alanındaki kaliteli bilgileri hekimlere, sağlık çalışanlarına ve öğrencilere daha erişilebilir kılmaktır.</p>',
                'content' => '<p>Alturamedix Academy tıp eğitimi, klinik bilgi paylaşımı ve profesyonel gelişim için oluşturulmuş modern bir akademik platformdur.</p><p>Amacımız acil ve kritik tıp alanındaki kaliteli bilgileri hekimlere, sağlık çalışanlarına ve öğrencilere daha erişilebilir kılmaktır.</p>',
            ],
            'contact' => [
                'title' => 'İletişim',
                'subtitle' => 'Bizimle iletişime geçin',
                'body' => '<p>Sorunuz, öneriniz veya iş birliği talebiniz varsa aşağıdaki form aracılığıyla bizimle iletişime geçebilirsiniz.</p>',
                'content' => '<p>Sorunuz, öneriniz veya iş birliği talebiniz varsa aşağıdaki form aracılığıyla bizimle iletişime geçebilirsiniz.</p>',
            ],
            'certificates' => [
                'title' => 'Diploma ve Sertifikalar',
                'subtitle' => 'Belge doğrulama',
                'body' => 'Belge numarasını girerek durumunu çevrimiçi doğrulayabilirsiniz.',
                'content' => 'Belge numarasını girerek durumunu çevrimiçi doğrulayabilirsiniz.',
            ],
        ], $now);
    }

    private function articleTranslations(): array
    {
        return [
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
    }

    private function updateAds(mixed $now): void
    {
        if (! Schema::hasTable('aa_ads')) {
            return;
        }

        DB::table('aa_ads')->where('lang_code', 'tr')->update([
            'title' => 'Reklamınız burada yer alabilir',
            'updated_at' => $now,
        ]);
    }

    private function updateRowsBySlug(string $table, array $translations, mixed $now): void
    {
        $this->updateRowsByKey($table, 'slug', $translations, $now);
    }

    private function updateRowsBySort(string $table, array $translations, mixed $now): void
    {
        $this->updateRowsByKey($table, 'sort_order', $translations, $now);
    }

    private function updateRowsByKey(string $table, string $column, array $translations, mixed $now): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'lang_code') || ! Schema::hasColumn($table, $column)) {
            return;
        }

        foreach ($translations as $key => $values) {
            DB::table($table)
                ->where('lang_code', 'tr')
                ->where($column, $key)
                ->update(array_merge($values, ['updated_at' => $now]));
        }
    }
};
