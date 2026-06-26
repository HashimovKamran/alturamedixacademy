<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Admin\AdminLogService;
use App\Services\Admin\UploadService;
use App\Support\Admin\AdminLanguage;
use App\Support\Cms\SafeUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $language = AdminLanguage::selected($request);

        return view('admin.settings', [
            'languages' => AdminLanguage::activeLanguages(),
            'selectedLanguage' => $language,
            'settings' => Setting::query()->where('lang_code', $language)->pluck('setting_value', 'setting_key')->all(),
            'logoSettings' => $this->logoSettings($language),
            'groups' => $this->groups(),
        ]);
    }

    public function update(Request $request, UploadService $uploads, AdminLogService $logs): RedirectResponse
    {
        $request->validate([
            'logo_image' => ['nullable', 'file', 'mimes:svg,png,jpg,jpeg,webp', 'max:5120'],
            'header_logo_image' => ['nullable', 'file', 'mimes:svg,png,jpg,jpeg,webp', 'max:5120'],
            'hero_logo_image' => ['nullable', 'file', 'mimes:svg,png,jpg,jpeg,webp', 'max:5120'],
        ], [
            'logo_image.mimes' => 'Loqo SVG, PNG, JPG və ya WEBP formatında olmalıdır.',
            'header_logo_image.mimes' => 'Header / footer loqosu SVG, PNG, JPG və ya WEBP formatında olmalıdır.',
            'hero_logo_image.mimes' => 'Hero loqosu SVG, PNG, JPG və ya WEBP formatında olmalıdır.',
            'logo_image.max' => 'Loqo maksimum 5 MB olmalıdır.',
            'header_logo_image.max' => 'Header / footer loqosu maksimum 5 MB olmalıdır.',
            'hero_logo_image.max' => 'Hero loqosu maksimum 5 MB olmalıdır.',
        ]);

        $language = AdminLanguage::selected($request);
        $languageCodes = AdminLanguage::activeLanguages()->pluck('code')->all();
        if ($languageCodes === []) {
            $languageCodes = [$language];
        }

        foreach ($this->groups() as $items) {
            foreach ($items as $key => $label) {
                $value = in_array($key, ['home_map_enabled'], true)
                    ? ($request->boolean($key) ? '1' : '0')
                    : (string) $request->input($key, '');

                if (str_starts_with($key, 'social_')) {
                    $value = SafeUrl::clean($value, '');
                }
                if ($key === 'home_slider_autoplay_ms') {
                    $value = (string) max(2500, min(20000, (int) $value));
                }
                if ($key === 'home_map_height') {
                    $value = (string) max(240, min(700, (int) $value));
                }

                Setting::query()->updateOrCreate(
                    ['lang_code' => $language, 'setting_key' => $key],
                    ['setting_value' => $value]
                );
            }
        }

        $updatedLogos = [];
        foreach ($this->logoKeys() as $imageKey) {
            if (! $request->hasFile($imageKey)) {
                continue;
            }

            $file = $request->file($imageKey);
            if (! $file || ! $file->isValid()) {
                throw ValidationException::withMessages([
                    $imageKey => 'Loqo faylı oxuna bilmədi. Faylı yenidən seçib təkrar yoxlayın.',
                ]);
            }

            $logo = $uploads->store($file, 'settings');
            if (! $logo) {
                throw ValidationException::withMessages([
                    $imageKey => 'Loqo yüklənmədi. SVG, PNG, JPG və ya WEBP formatında, maksimum 5 MB olan etibarlı fayl seçin.',
                ]);
            }

            foreach ($languageCodes as $code) {
                Setting::query()->updateOrCreate(
                    ['lang_code' => $code, 'setting_key' => $imageKey],
                    ['setting_value' => $logo]
                );
            }

            $updatedLogos[] = $imageKey;
        }

        $logs->write(
            $request,
            'settings',
            'update',
            'Sayt ayarları yadda saxlanıldı: '.strtoupper($language).($updatedLogos !== [] ? ' | Loqolar: '.implode(', ', $updatedLogos) : ''),
            'Setting'
        );

        return redirect()->route('admin.settings.index')->with(
            'status',
            $updatedLogos === []
                ? 'Ayarlar yadda saxlanıldı.'
                : 'Loqo uğurla yükləndi və bütün dillər üçün yeniləndi.'
        );
    }

    private function logoKeys(): array
    {
        return ['logo_image', 'header_logo_image', 'hero_logo_image'];
    }

    private function logoSettings(string $preferredLanguage): array
    {
        $values = [];

        foreach ($this->logoKeys() as $key) {
            $values[$key] = (string) Setting::query()
                ->where('setting_key', $key)
                ->whereNotNull('setting_value')
                ->where('setting_value', '<>', '')
                ->orderByRaw('CASE WHEN lang_code = ? THEN 0 ELSE 1 END', [$preferredLanguage])
                ->value('setting_value');
        }

        return $values;
    }

    private function groups(): array
    {
        return [
            'Brend' => [
                'site_name' => 'Sayt adı',
                'site_slogan' => 'Sloqan',
                'site_description' => 'Sayt açıqlaması',
            ],
            'Sosial şəbəkələr' => [
                'social_instagram' => 'Instagram URL',
                'social_linkedin' => 'LinkedIn URL',
                'social_youtube' => 'YouTube URL',
            ],
            'Əsas səhifə' => [
                'section_academic' => 'Akademik yazılar başlığı',
                'section_latest' => 'Son dərc olunanlar başlığı',
                'section_trainings' => 'Təlimlər başlığı',
                'section_features' => 'İmkanlar başlığı',
                'section_partners' => 'Partnyorlar başlığı',
                'section_partners_subtitle' => 'Partnyorlar alt mətni',
                'all_view' => 'Hamısına bax mətni',
                'home_slider_autoplay_ms' => 'Slider intervalı (ms)',
            ],
            'Footer' => [
                'footer_about' => 'Footer haqqında mətni',
                'footer_links' => 'Keçidlər başlığı',
                'footer_contact' => 'Əlaqə başlığı',
                'footer_newsletter' => 'Bülleten başlığı',
                'newsletter_text' => 'Bülleten mətni',
                'newsletter_placeholder' => 'Bülleten placeholder',
            ],
            'Haqqımızda' => [
                'about_who_text' => 'Biz kimik mətni',
                'about_mission_text' => 'Missiya mətni',
                'about_board_text' => 'İdarə heyəti mətni',
            ],
            'Autentifikasiya' => [
                'btn_register' => 'Qeydiyyat düyməsi',
                'btn_login' => 'Giriş düyməsi',
                'auth_logout' => 'Çıxış düyməsi',
                'auth_login_title' => 'Giriş başlığı',
                'auth_login_subtitle' => 'Giriş alt mətni',
                'auth_register_title' => 'Qeydiyyat başlığı',
                'auth_register_subtitle' => 'Qeydiyyat alt mətni',
            ],
            'Əlaqə' => [
                'contact_phone' => 'Telefon',
                'contact_email' => 'Email',
                'contact_address' => 'Ünvan',
            ],
            'Google Map' => [
                'home_map_enabled' => 'Xəritə aktiv olsun',
                'home_map_title' => 'Xəritə başlığı',
                'home_map_subtitle' => 'Xəritə alt mətni',
                'home_map_embed' => 'Google Map iframe',
                'home_map_height' => 'Xəritə hündürlüyü',
            ],
        ];
    }
}
